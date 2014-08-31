<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');


$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach((array) $dlist as $l) {
		$l->generateDomainKey(false, true);
	}
}

