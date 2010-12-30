<?php 
include_once "htmllib/lib/include.php"; 

if (lxfile_exists("__path_slave_db")) {
	$type = 'slave';
} else {
	$type = 'master';
}

system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php --type=$type");

