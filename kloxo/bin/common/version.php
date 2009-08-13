<?php 

include "htmllib/lib/include.php";

$list = parse_opt($argv);
$version = $sgbl->__ver_major_minor_release;
if(isset($list['vertype'])) {
	$var = "__ver_" . $list['vertype'];
	$version = $sgbl->$var;
}
if (isset($list['cvsstyle'])) {
	$version = str_replace(".", "_", $version);
}

print($version);
print("\n");
flush();
