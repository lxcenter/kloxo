<?php 

include_once "htmllib/lib/include.php";

child_main();

function child_main()
{
	global $argv;
	//sleep(100);
	ob_start();
	$rem = unserialize(lfile_get_contents($argv[1]));
	unlink($argv[1]);

	if (!$rem) { exit; }

	if (isset($rem->sleep)) {
		sleep($rem->sleep);
	}

	if ($rem->__type == 'object') {
		$func = $rem->func;
		$ret = $rem->__exec_object->$func();
	} else {
		$ret = call_user_func_array($rem->func, $rem->arglist);
	}


	$var = base64_encode(serialize($ret));
	ob_end_clean();
	print($var);
	exit;
}


