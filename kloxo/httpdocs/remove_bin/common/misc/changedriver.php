<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$plist = parse_opt($argv);

$class = $argv[1];
if (!isset($argv[2])) {
	$driverapp = $gbl->getSyncClass(null, 'localhost', $class);
	print("Driver for $class is $driverapp\n");
	exit;
}

$pgm = $argv[2];



$server = $login->getFromList('pserver', 'localhost');

$os = $server->ostype;
include "../file/driver/$os.inc";


$dr = $server->getObject('driver');

if (!array_search_bool($pgm, $driver[$class])) {
	$str = implode(" ", $driver[$class]);
	print("The driver name isn't correct: Available drivers for $class: $str\n");
	exit;
}


$v = "pg_$class";
$dr->driver_b->$v = $pgm;

$dr->setUpdateSubaction();

$dr->write();

print("Successfully changed Driver for $class to $pgm\n");






