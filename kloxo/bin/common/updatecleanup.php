<?php 

exit_if_not_system_user();

system("/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/");
system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php {$argv[1]}");
exit;

/*
include_once "htmllib/lib/include.php";
include_once "lib/updatelib.php";
include_once "htmllib/lib/updatelib.php";

updatecleanup_main();

function updatecleanup_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	$program = $sgbl->__var_program_name;
	$login = new Client(null, null, 'upgrade');

	log_log("update", "Execing Updatecleanup");
	$opt = parse_opt($argv);


	if ($opt['type'] === 'master') {
		$sgbl->slave = false;
		updateDatabaseProperly();
		doUpdateExtraStuff();
		lxshell_return("__path_php_path", "../bin/common/driverload.php");
		update_slave();
	} else {
		$sgbl->slave = true;
	}

	updatecleanup();
}

*/
