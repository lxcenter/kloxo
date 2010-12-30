<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) {
	$server = $list['server'];
} else {
	$server = 'localhost';
}



$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach((array)$dlist as $l) {
		$mmail = $l->getObject('mmail');
		if ($mmail->syncserver !== $server) { continue; }

		$mmail->setUpdateSubaction('full_update');
		$mmail->was();

		$mlist = $mmail->getList('mailaccount');
		foreach($mlist as $ml) {
			$spam = $ml->getObject('spam');
			$spam->setUpdateSubaction('full_update');
			$spam->was();
			$ml->setUpdateSubaction('full_update');
			$ml->was();
		}
	}
}

