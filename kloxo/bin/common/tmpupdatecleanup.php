<?php 
include_once "htmllib/lib/include.php";
// include_once "lib/updatelib.php";
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

//	print("\n\n***Executing UpdateCleanup. This can take some time. Please be patient.***\n\n");
	log_cleanup("*** Executing Update cleanup - BEGIN ***");
//
// Check for lxlabs yum repo file and if exists
// Change to lxcenter repo file
//
	if (lxfile_exists("/etc/yum.repos.d/lxlabs.repo")) {
		log_cleanup("- Delete old repo's");
		lxfile_mv("/etc/yum.repos.d/lxlabs.repo","/etc/yum.repos.d/lxlabs.repo.lxsave");
		system("rm -f /etc/yum.repos.d/lxlabs.repo");
		log_cleanup("- Removed lxlabs.repo");
		log_cleanup("- Installing lxcenter.repo");
		system("wget -O /etc/yum.repos.d/lxcenter.repo http://download.lxcenter.org/lxcenter.repo");
		log_cleanup("- Installing yum-protectbase plugin");
		system("yum install -y -q yum-protectbase");
	//	print("Done.\n");
	}
//

//
// Fix vulnerability within webmail
// If the flag isn't found, run the fix
//
// Disabled in Kloxo 6.1.4
//	if (!lxfile_exists("/usr/local/lxlabs/kloxo/file/webmailReset")) {
//		system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
//		system("/bin/rm /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
//	}

// Remove Flagfile in Kloxo 6.1.4, thios can be removed in 6.1.4+
	if (lxfile_exists("/usr/local/lxlabs/kloxo/file/webmailReset")) {
		system("/bin/rm /usr/local/lxlabs/kloxo/file/webmailReset");
	}

// Fix #388 - phpMyAdmin config.inc.php permission

	$correct_perm = "0644";
	$check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

	if ($check_perm != $correct_perm) {
		lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
	}

//

	if (lxfile_exists(".svn")) {
		log_cleanup("- SVN Found... Exiting");
		exit;
	}

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		if (!is_secondary_master()) {
			updateDatabaseProperly();
		//	fixExtraDB();
		//	doUpdateExtraStuff();
			// call new function (for 6.2.x) since 6.1.7
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

	lxfile_touch("__path_program_start_vps_flag");

	log_cleanup("*** Executing Update cleanup - END ***");
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

