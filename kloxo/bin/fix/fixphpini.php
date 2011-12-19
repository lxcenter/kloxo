<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;

/*
$s = new Pserver(null, $server, $server);

$s->get();
$pi = $s->getObject('phpini');
$pi->setUpdateSubaction('full_update');
$pi->was();
*/

$login->loadAllObjects('client');
$list = $login->getList('client');

$plist = $login->getList('pserver');

log_cleanup("Fixing php.ini");

foreach($plist as $s) {
	if ($client) {
	//	if ($client !== $c->nname) { continue; }
		$ca = explode(",", $client);
		if (!in_array($c->nname, $ca)) { continue; }
		$server = 'all';
	}

	if ($server !== 'all') {
	//	if ($c->syncserver !== $server) { continue; }
		$sa = explode(",", $server);
		if (!in_array($c->syncserver, $sa)) { continue; }
	}

	$pi = $s->getObject('phpini');
	$pi->setUpdateSubaction('full_update');
	$pi->was();

	log_cleanup("- in '/etc' at '{$s->nname}'");
}

foreach($list as $c) {
	if ($client) {
		$server = 'all';
		if ($client !== $c->nname) { continue; }
	}

	if ($server !== 'all') {
		if ($c->syncserver !== $server) { continue; }
	}

	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		$php = $web->getObject('phpini');
		$php->initPhpIni();
		$php->setUpdateSubaction('full_update');
		$php->was();

		log_cleanup("- in '/home/httpd/{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'");
	}
}

