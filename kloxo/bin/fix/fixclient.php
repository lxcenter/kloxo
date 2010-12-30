<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('client');

$list = $login->getList('client');

foreach($list as $l) {
	lxfile_unix_chown("__path_customer_root/{$l->getPathFromName('nname')}", "$l->username:apache");
	lxfile_unix_chmod("__path_customer_root/{$l->getPathFromName('nname')}", "0750");
}

