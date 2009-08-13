<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('client');
$list = $login->getList('client');

$par = parse_opt($argv);

$newip = null;

if (isset($par['oldip'])) {
	$oldip = $par['oldip'];
}

if (isset($par['newip'])) {
	$newip = $par['newip'];
}


foreach($list as $c) {
	$dlist = $c->getList('domain');
	foreach($dlist as $l) {
		$dns = $l->getObject('dns');
		$dns->setUpdateSubaction('full_update');
		if ($newip && $oldip) {
			foreach($dns->dns_record_a as $drec) {
				if ($drec->ttype !== 'a') {
					continue;
				}
				print("changing oldip $oldip to $newip\n");
				if ($drec->param === $oldip) {
					$drec->param = $newip;
				}
			}
		}
		$dns->was();
	}
}

