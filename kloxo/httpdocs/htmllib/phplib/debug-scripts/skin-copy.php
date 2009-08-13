<?php 
include_once "htmllib/lib/include.php";


$list = scandir($argv[1]);

foreach($list as $f) {

	if (!csb($f, "Copy")) {
		continue;
	}
	preg_match( "/Copy \((.*)\).*/i", $f, $match);
	if (!isset($match[1])) {
		system("rm -rf $sgbl->__path_program_httdocs/img/skin/color001");
		system("cp -a '{$argv[1]}/$f' $sgbl->__path_program_httdocs/img/skin/color001");
		continue;
	}
	$num = createZeroString(3 - strlen($match[1])) . $match[1];
	system("rm -rf $sgbl->__path_program_httdocs/img/skin/color$num");
	system("cp -a '{$argv[1]}/$f' $sgbl->__path_program_httdocs/img/skin/color$num");
}

