<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

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
	$pi = $s->getObject('phpini');
	$pi->setUpdateSubaction('full_update');
	$pi->was();

	log_cleanup("- in '/etc' at '{$pi->syncserver}'");
}

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		if ($server !== 'all') {
			if ($web->syncserver !== $server) { continue; }
		}

		$php = $web->getObject('phpini');
		$php->initPhpIni();
		$php->setUpdateSubaction('full_update');
		$php->was();

		log_cleanup("- in '/home/httpd/{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'");
	}
}

