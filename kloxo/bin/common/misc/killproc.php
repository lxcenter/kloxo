<?php 
include_once "htmllib/lib/include.php"; 

$list = getRealPidlist($argv[1]);

foreach((array) $list as $l) {
	lxshell_return("kill", $l);
}
