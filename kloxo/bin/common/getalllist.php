<?php 

include_once "htmllib/lib/include.php";
initProgram('admin');

$gbl->loaddriverappInfo('localhost');
$opt = parse_opt($argv);
if (!$opt['class']) {
	exit;
}
$class = $opt['class'];
$login->loadAllObjects($class);

$list = $login->getList($class);

foreach($list as $l) {
	$l->createSyncClass();
}

$pserverlist = $login->getList('pserver');

foreach($pserverlist as $ps) {
	$ps->createSyncClass();
}

$lg = clone ($login);
lxclass::clearChildrenAndParent($lg);

$outlist[$class] = $list;
$outlist['pserver'] = $pserverlist;
$outlist['login'] = $lg;
$outlist['gbl'] = $gbl;
print(base64_encode(serialize($outlist)));
exit;
