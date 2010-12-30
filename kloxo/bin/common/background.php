<?php 

include_once "htmllib/lib/include.php";

background_main();

function background_main()
{
	global $argv;
	//sleep(100);
	$rem = unserialize(lfile_get_contents($argv[1]));
	unlink($argv[1]);

	if (!$rem) { exit; }

	if (isset($rem->sleep)) {
		sleep($rem->sleep);
	}

	if ($rem->__type == 'object') {
		$func = $rem->func;
		$rem->__exec_object->$func();
	} else {
		call_user_func_array($rem->func, $rem->arglist);
	}
}


