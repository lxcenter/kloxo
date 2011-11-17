<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

$s = new Pserver(null, $server, $server);

$s->get();
$pi = $s->getObject('phpini');
$pi->setUpdateSubaction('full_update');
$pi->was();

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fix php.ini config");

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

	//	if ($web->syncserver !== $server) { continue; }

		$php = $web->getObject('phpini');
		$php->initPhpIni();
		$php->setUpdateSubaction('full_update');
		$php->was();

		log_cleanup("- '" . $web->nname . "' domain in '". $web->syncserver . "' server");

	}
}

