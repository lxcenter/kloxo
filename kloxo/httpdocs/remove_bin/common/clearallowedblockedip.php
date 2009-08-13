<?php 
include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

if (!$list['class'] || !$list['name']) {
	print("Usage $argv[0] --class= --name= \n");
	exit;
}

$class = $list['class'];
$name = $list['name'];

$object = new $class(null, null, $name);

$list = $object->getList('allowedip');

foreach($list as $l) {
	$l->delete();
	$l->write();
}

$list = $object->getList('blockedip');
foreach($list as $l) {
	$l->delete();
	$l->write();
}

print("AllowedIp Sucessfully cleared for $class:$name\n");

