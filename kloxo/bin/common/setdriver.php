<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');


$list = parse_opt($argv);


checkIfVariablesSet($list, array('server', 'class'));

$server = $list['server'];
$class = $list['class'];

if (!isset($list['driver'])) {
	$driverapp = $gbl->getSyncClass(null, $server, $class);
	print("Driver for $class is $driverapp\n");
	exit;
}

$pgm = $list['driver'];

changeDriverFunc($server, $class, $pgm);

