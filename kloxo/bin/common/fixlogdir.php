<?php 

include_once "htmllib/lib/include.php"; 

fixlogdir_main();

function fixlogdir_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;

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
	lxfile_generic_chmod("../log", "0700");
	os_restart_program();


}
