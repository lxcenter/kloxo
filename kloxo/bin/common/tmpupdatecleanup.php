<?php 
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/updatelib.php";

exit_if_another_instance_running();
debug_for_backend();
updatecleanup_main();

function updatecleanup_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	$program = $sgbl->__var_program_name;
	$opt = parse_opt($argv);

	if ($opt['type'] === 'master') {
		initProgram('admin');
		$flg = "__path_program_start_vps_flag";
		if (!lxfile_exists($flg)) {
			set_login_skin_to_feather();
		}
	} else {
		$login = new Client(null, null, 'update');
	}

	log_cleanup("*** Executing Update (cleanup) - BEGIN ***");

    // Do things that is needed before the cleanup starts.
    print("########################################\n");
    print("##        Executing PreCleanup        ##\n");
    print("########################################\n");
    doBeforeUpdate();
    print("########################################\n");
    print("##        Finished PreCleanup         ##\n");
    print("########################################\n");

	if (lxfile_exists('.git')) {
		log_cleanup('- Development found!');
	}

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		if (!is_secondary_master()) {
			updateDatabaseProperly();
			fixDataBaseIssues();
			doUpdates();
			lxshell_return("__path_php_path", "../bin/common/driverload.php");
		}
		update_all_slave();
		cp_dbfile();
	} else {
		$sgbl->slave = true;
	}

	if (!is_secondary_master()) {
		updatecleanup();
	}

	if ($opt['type'] === 'master') {
		lxfile_touch("__path_program_start_vps_flag");
	}

	// issue #716 -- [beta] Unresolved dependency on Apache version
	
	// --- remove httpd-itk rpm (from webtatic.repo or others) because may conflict with
	// httpd 2.2.21 that include mpm itk beside mpm worker and event
	
	exec("rpm -q httpd-itk | grep -i 'not installed'", $out, $ret);

	// --- not work with !$ret
	if ($ret !== 0) {
		log_cleanup("Remove httpd-itk rpm package");
		log_cleanup("- Remove httpd-itk");
		exec("rpm -e httpd-itk --nodeps");
		exec("rpm -q httpd | grep -i 'not installed'", $out2, $ret2);
		if ($ret2 === 0) {
			log_cleanup("- Reinstall httpd");
			exec("yum reinstall httpd -y");
		}
	}

	// MR -- mysql not start after kloxo slave install
	log_cleanup("Preparing MySQL service");

	log_cleanup("- MySQL activated");
	exec("chkconfig mysqld on");
	
	log_cleanup("- MySQL restarted");
	exec("service mysqld restart");

	// MR -- importance for update from 6.1.6 or previous where change apache/lighttpd structure 
	// or others for next version

	$slist = array(
		"httpd*", "lighttpd*", "bind*", "djbdns*", "pure-ftpd*", "php*",
		"vpopmail", "courier-imap-toaster", "courier-authlib-toaster", 
		"qmail", "safecat", "spamassassin", "bogofilter", "ezmlm-toaster", 
		"autorespond-toaster", "clamav-toaster");
	setUpdateServices($slist);
	
	// MR -- use this trick for qmail non-daemontools based
	log_cleanup("Preparing some services again");
	
	log_cleanup("- courier-imap enabled and restart queue");
	exec("chkconfig courier-imap on");
	createRestartFile("courier-imap");
	
	log_cleanup("- qmail enabled and restart queue");
	exec("chkconfig qmail on");
	createRestartFile("qmail");

	$fixapps = array("dns", "web", "php", "mail", "ftpuser", "vpop");
	setUpdateConfigWithVersionCheck($fixapps, $opt['type']);

	// --- for anticipate change xinetd listing
	exec("service xinetd restart");
}

function doBeforeUpdate()
{
    global $gbl, $sgbl, $login, $ghtml;

    $program = $sgbl->__var_program_name;

    // Check for lxlabs yum repo file and if exists
    // Change to lxcenter repo file
    if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
        log_cleanup("- Deleting old lxlabs yum repo");
        lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
        exec("rm -f /etc/yum.repos.d/lxlabs.repo");
        log_cleanup("- Removed lxlabs.repo");
        log_cleanup("- Installing lxcenter.repo");
        exec("wget -O /etc/yum.repos.d/lxcenter.repo http://download.lxcenter.org/lxcenter.repo");
        log_cleanup("- Installing yum-protectbase plugin");
        exec("yum install -y -q yum-protectbase");
    }

    // Project issue #1079
    // Install yum-plugin-replace (New since Kloxo 6.1.14)
    $ret =  install_if_package_not_exist("yum-plugin-replace");
    if ($ret)
    {
        print("Installed RPM package yum-plugin-replace\n");
    }

    // Project issue #1079
    // Replace lxphp package (New since Kloxo 6.1.14)
    $ret =  replace_rpm_package("lxphp", "kloxo-core-php");
    if ($ret)
    {
        print("Replaced RPM package lxphp with kloxo-core-php\n");
    }

    // Fix #388 - phpMyAdmin config.inc.php permission
    $correct_perm = "0644";
    $check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

    if ($check_perm != $correct_perm) {
        lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
    }

    // Project issue #1081
    // Remove lxrestart
    if (lxfile_exists("/usr/sbin/lxrestart")) {
        log_cleanup("- Deleting lxrestart from /usr/sbin/ (not in use anymore)");
        lxfile_rm('/usr/sbin/lxrestart');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/cexe/lxrestart')) {
        log_cleanup("- Deleting lxrestart from cexe (not in use anymore)");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/cexe/lxrestart');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/lxrestart.c')) {
        log_cleanup("- Deleting lxrestart.c from src (not in use anymore)");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/lxrestart.c');
    }
    // Clean Source dir
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/lxrestart')) {
        log_cleanup("- Clean the sources dir - remove lxrestart");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/lxrestart');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/closeallinput')) {
        log_cleanup("- Clean the sources dir - remove closeallinput");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/closeallinput');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/lxexec')) {
        log_cleanup("- Clean the sources dir - remove lxexec");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/lxexec');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/lxphpsu')) {
        log_cleanup("- Clean the sources dir - remove lxphpsu");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/lxphpsu');
    }
    if (lxfile_exists('/usr/local/lxlabs/'. $program . '/src/lxsuexec')) {
        log_cleanup("- Clean the sources dir - remove lxsuexec");
        lxfile_rm('/usr/local/lxlabs/' . $program . '/src/lxsuexec');
    }

    // DT18022014 - Cleanup the mess.
    if (lxfile_exists('/usr/local/lxlabs/' . $program . '/httpdocs/live/common.php')) {
        log_cleanup("- Remove live dir (not in use)");
        lxfile_rm_rec('/usr/local/lxlabs/' . $program . '/httpdocs/live');
        lxfile_rm('/usr/local/lxlabs/' . $program . '/etc/phplive.db');
    }
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

