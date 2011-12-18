<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$client = (isset($list['client'])) ? $list['client'] : null;

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing Web server config");

$prevsyncserver = '';
$currsyncserver = '';

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

		$currsyncserver = $web->syncserver;

		if ($prevsyncserver !== $currsyncserver) {
			$web->setUpdateSubaction('static_config_update');

			log_cleanup("- inside static (defaults/webmail) directory at '{$currsyncserver}'");

			$prevsyncserver = $currsyncserver;
		}

		$web->setUpdateSubaction('full_update');
		$web->was();

		log_cleanup("- '{$web->nname}' ('{$c->nname}') at '{$web->syncserver}'");
	}
}

