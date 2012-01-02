<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');


$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	if (!$c->getPrimaryDb()) {
		$db = $c->createDefaultDatabase();
		$db->was();
	}
}

