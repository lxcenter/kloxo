<?php 

$v = `uname -r`;

//$v = "2.6.10-53.1.4.el5xen";

$vv = explode("-", $v);

$v = $vv[0];

$vv = explode(".", $v);

if ($vv[2] < 12) {
	exit(10);
} 
exit(0);



