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
//
// Check for lxlabs yum repo file and if exists
// Change to lxcenter repo file
//
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

// Fix #388 - phpMyAdmin config.inc.php permission

	$correct_perm = "0644";
	$check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

	if ($check_perm != $correct_perm) {
		lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
	}

//

	if (lxfile_exists('.git')) {
		log_cleanup('- Development found... Exiting');
		exit;
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

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

