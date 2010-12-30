<?php 

include_once "htmllib/phplib/lxlib.php";

grep_main() ;


function grep_main()
{

	recurse_dir(".", "find_expr");

}

function find_expr($file)
{
	global $gbl, $sgbl, $argc, $argv;

	if (is_dir($file)) {
		return;
	}
	$dl = file($file);


	$count = 0;
 	foreach($dl as $l) {
		$count++;
		if (preg_match('/' . $argv[1] . '/', $l)) {
			print("$file:$count:$l");
		}
	}
}

			





