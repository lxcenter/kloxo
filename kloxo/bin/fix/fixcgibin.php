<?php 
include_once "htmllib/lib/include.php"; 

initProgram('admin');


$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	$cdir = "__path_customer_root/{$c->getPathFromName('nname')}";
	foreach($dlist as $l) {
		lxfile_mv_rec("$cdir/cgi-bin/$l->nname", "$cdir/$l->nname/cgi-bin");
		$web = $l->getObject('web');
		$web->setUpdateSubaction('full_update');
		$web->was();
	}
}

