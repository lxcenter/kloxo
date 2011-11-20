<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing Web server config");

foreach($list as $c) {
	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		if ($server !== 'all') {
			if ($web->syncserver !== $server) { continue; }
		}

		$web->setUpdateSubaction('full_update');
		$web->was();

		log_cleanup("- '{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'");
	}
}

