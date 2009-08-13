<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);
if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

$var = $list['variable'];
$val = $list['value'];

$s = new Pserver(null, $server, $server);
$s->get();
$pi = $s->getObject('phpini');
$pi->initPhpIni();
if (!isset($pi->phpini_flag_b->$var)) {
	print("No variable by that name\n");
	exit;
}
$pi->phpini_flag_b->$var = $val;
$pi->setUpdateSubaction('full_update');
$pi->was();

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');
		if ($web->syncserver !== $server) { continue; }
		$php = $web->getObject('phpini');
		$php->initPhpIni();
		$php->phpini_flag_b->$var = $val;
		$php->setUpdateSubaction('full_update');
		$php->was();
	}
}

