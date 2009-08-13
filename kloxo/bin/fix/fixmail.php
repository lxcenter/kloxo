<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');



$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach($dlist as $l) {
		$mmail = $l->getObject('mmail');
		$mclist = $mmail->getList('mailaccount');
		foreach($mclist as  $mc) {
			$spam = $mc->getObject('spam');
			$mc->setUpdateSubaction('full_update');
			$spam->setUpdateSubaction('full_update');
			$mc->was();
			$spam->was();
		}
	}
}


