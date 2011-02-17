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
		// workaround for the following php bug:
		//   http://bugs.php.net/bug.php?id=47948
		//   http://bugs.php.net/bug.php?id=51329
		if (is_array($rem->func) && count($rem->func) > 0) {
			$class = $rem->func[0];
			class_exists($class);
		}
		// ---
		call_user_func_array($rem->func, $rem->arglist);
	}
}


