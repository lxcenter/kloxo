<?php 

include_once "htmllib/lib/include.php"; 

fixlogdir_main();

function fixlogdir_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$logl = lscandir_without_dot("../log");
	lxfile_mkdir("../processed_log");
	@ lunlink("../log/access_log");
	@ lunlink("/usr/local/lxlabs/ext/php/error.log");
	$dir = getNotexistingFile("../processed_log", "proccessed");
	system("mv ../log ../processed_log/$dir");
	mkdir("../log");

	$list = lscandir_without_dot("../processed_log");
	foreach($list as $l) {
		remove_directory_if_older_than_a_day("../processed_log/$l", 6);
	}
	foreach($logl as $l) {
		lxfile_touch("../log/$l");
	}
	lxfile_generic_chown_rec("../log", "lxlabs:lxlabs");
	//
	// Related to Issue #15
	//
	lxfile_generic_chmod_rec("../log", "0640");
	lxfile_generic_chmod_rec("../processed_log", "0640");
	lxfile_generic_chmod("../log", "0700");
	lxfile_generic_chmod("../processed_log", "0700");
	lxfile_generic_chmod("../log/lighttpd_error.log", "0644");
	lxfile_generic_chmod("../log/access_log", "0644");
	lxfile_generic_chown("../log/lighttpd_error.log", "lxlabs:root");
	lxfile_generic_chown("../log/access_log", "lxlabs:root");
	//
	os_restart_program();


}
