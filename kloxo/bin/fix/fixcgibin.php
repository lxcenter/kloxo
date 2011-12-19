<?php 
include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing cgi-bin path");

foreach($list as $c) {
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

	$dlist = $c->getList('domaina');
	$cdir = "__path_customer_root/{$c->getPathFromName('nname')}";

	foreach($dlist as $l) {
		$web = $l->getObject('web');

		lxfile_mv_rec("$cdir/cgi-bin/$l->nname", "$cdir/$l->nname/cgi-bin");

		$web->setUpdateSubaction('full_update');
		$web->was();
	}
}

