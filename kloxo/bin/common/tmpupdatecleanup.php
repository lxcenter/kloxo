<?php 
include_once "htmllib/lib/include.php";
include_once "lib/updatelib.php";
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

	print("Executing UpdateCleanup. This can take some time. Please be patient.\n");
	log_log("update", "Executing Updatecleanup");
//
// Check for lxlabs yum repo file and if exists
// Change to lxcenter repo file
//
	if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
		print("Delete old repo's\n");
		lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
		system("rm -f /etc/yum.repos.d/lxlabs.repo");
		print("Removed lxlabs.repo\n");
		print("Installing lxcenter.repo\n");
		system("wget -O /etc/yum.repos.d/lxcenter.repo http://download.lxcenter.org/lxcenter.repo");
        print("Installing yum-protectbase plugin\n");
        system("yum install -y -q yum-protectbase");
		print("Done.\n");
	}
//

//
// Fix vulnerability within webmail
// If the flag isn't found, run the fix
//

if (!lxfile_exists("/usr/local/lxlabs/kloxo/file/webmailReset")) {
	system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
	system("/bin/rm /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
}
//


// Fix #388 - phpMyAdmin config.inc.php permission

    $correct_perm = "0644";
    $check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

    if ($check_perm != $correct_perm) {
        lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
    }

//

	if (lxfile_exists(".svn")) {
		print("SVN Found... Exiting\n\n");
		exit;
	}

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		if (!is_secondary_master()) {
			updateDatabaseProperly();
			fixExtraDB();
			doUpdateExtraStuff();
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

	lxfile_touch("__path_program_start_vps_flag");
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

