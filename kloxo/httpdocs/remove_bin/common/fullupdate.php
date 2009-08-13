<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

if (!$list['class']) {
	print("$argv[0] --class=<classname> \n");
	exit;
}

$login->loadAllObjects('domain');
$list = $login->getList($class);

foreach($list as $l) {
	$l->setUpdateSubaction('full_update');
	$l->was();
}
print("\n\n");

