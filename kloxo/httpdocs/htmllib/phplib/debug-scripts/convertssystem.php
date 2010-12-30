<?php 

unset($argv[0]);
foreach($argv as $v) {
	$name = preg_replace("/(.*)ssystem(.*)/i", "\$1pserver\$2", $v);
	$name = preg_replace("/(.*)ssystem(.*)/i", "\$1pserver\$2", $name);
	print("mv $v $name \n");
	system("mv $v $name");
}
