<?php 
include_once "htmllib/lib/include.php";


//array('add-under-class', 'add-under-name', 'subaction');


commandline_main();

function commandline_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;
	initProgram('admin');
	$must = array('action');
	$p = parse_opt($argv);
	$pk = array_keys($p);
	foreach($must as $m) {
		if (!array_search_bool($m, $pk)) {
			Print("Need action, class and name\n");
			exit;
		}
	}

	$func = "__cmd_desc_{$p['action']}";

	try {
		$list = $func($p);
		if ($list) {
			if (isset($p['output-type'])) {
				if ($p['output-type'] === 'json') {
					$out = json_encode($list);
					print($out);
				} else if ($p['output-type'] === 'serialize') {
					$out = serialize($list);
					print($out);
				}
			} else {
				foreach($list as $l) {
					print("$l\n");
				}
			}
		} else {
			print("{$p['action']} succesfully executed\n");
		}
		exit(0);
	} catch (exception $e) {
		print($e->__full_message);
		print("\n");
		exit(8);
	}
}




