<?php 
include_once "htmllib/lib/include.php";
include_once "lib/updatelib.php";
include_once "htmllib/lib/updatelib.php";

function updatecleanup_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	log_cleanup("Executing UpdateCleanup. This can take some time. Please be patient.");

	$program = $sgbl->__var_program_name;
	$opt = parse_opt($argv);

	if ($opt['type'] === 'master') {
		initProgram('admin');
		$flg = "__path_program_start_vps_flag";
		if (!lxfile_exists($flg)) {
			log_cleanup("Set skin to feather.n");
			set_login_skin_to_feather();
			lxfile_touch("__path_program_start_vps_flag");
		}
	} else {
		$login = new Client(null, null, 'update');
	}


// Fix #388 - phpMyAdmin config.inc.php permission
// TODO: Fix it permanently after third-party updates.
	log_cleanup("Running Fix #388");
    $correct_perm = "0644";
    $check_perm = substr(decoct( fileperms("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php") ), 2);

    if ($check_perm != $correct_perm) {
        lxfile_unix_chmod("/usr/local/lxlabs/$program/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
    }

// Fix #446 - Stats page not password protected by default. Complements r427.
// TODO: Remove this fix in Kloxo 6.1.6 and up.
	log_cleanup("Running Fix #446");
	mysql_query("UPDATE `kloxo`.`web` SET `stats_password` = SUBSTRING(MD5(RAND()) FROM 1 FOR 8) WHERE `web`.`stats_password` = ''");
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
//

	if (lxfile_exists(".svn")) {
		log_cleanup("BREAK - Development version Found");
		exit;
	}

	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		if (!is_secondary_master()) {
			log_cleanup("Update database");
			updateDatabaseProperly();

			fixDataBaseIssues();
			doUpdates();

			log_cleanup("DriverLoading");
			lxshell_return("__path_php_path", "../bin/common/driverload.php");
		}
		log_cleanup("Update the Slaves");
		update_all_slave();
		cp_dbfile();
	} else {
		$sgbl->slave = true;
	}

	if (!is_secondary_master()) {
		updatecleanup();
	}
		log_cleanup("Finished!\n\n");
}

function cp_dbfile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;

	log_cleanup("Installing database wrapper");

	lxfile_cp("../sbin/{$progname}db", "/usr/bin/{$progname}db");
	lxfile_generic_chmod("/usr/bin/{$progname}db", "0755");
}

exit_if_another_instance_running();
debug_for_backend();
updatecleanup_main();
