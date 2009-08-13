<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');
$list = $login->getlist('client');

foreach($list as $l) {
	if (!$l->isAdmin()) {
		process_client($l);
	}
	process_domain($l);
}

function process_client($l)
{
	$clist = $l->getList('client');
	foreach($clist as $c) {
		process_client($c);
		process_domain($c);
	}
}

function process_domain($l)
{
	$dlist = $l->getList('domain');
	foreach($dlist as $d) {
		$d->generateCMList();
		$d->setUpdateSubaction();
		$d->write();
	}
}

