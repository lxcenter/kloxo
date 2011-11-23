<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

log_cleanup("Fixing DNS server config");

if (isset($list['new_dnstemplate'])) {
	$dnst = new Dnstemplate(null, null, $list['new_dnstemplate']);
	$dnst->get();
	if ($dnst->dbaction === 'add') {
		log_cleanup("- DNS template doesn't exist");
		exit;
	}
}

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	
	foreach($dlist as $l) {
		$dns = $l->getObject('dns');

		if ($server !== 'all') {
			if ($dns->syncserver !== $server) { continue; }
		}

		if ($dnst) {
			$dns->dns_record_a = null;
			$dns->copyObject($dnst);
		}

		$dns->setUpdateSubaction('full_update');
		$dns->was();

		log_cleanup("- '{$dns->nname}'('{$c->nname}') at '{$dns->syncserver}'");
	}
}

