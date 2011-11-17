<?php 
include_once "htmllib/lib/include.php"; 


initprogram('admin');

$list = parse_opt($argv);

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

//lxfile_mv("/etc/pure-ftpd/pureftpd.passwd", "/etc/pure-ftpd/pureftpd.passwd.oldsaved");
//lunlink("/etc/pure-ftpd/pureftpd.pdb");
//lunlink("/etc/pure-ftpd/pureftpd.passwd.tmp");

$login->loadAllObjects('client');
$list = $login->getList('client');

log_cleanup("Fixing FTP User");

foreach($list as $c) {
//	if (($c->syncserver !== $server) || ($server !== 'all')) { continue; }

	$flist = $c->getList('ftpuser');

	foreach($flist as $fl) {
		$fl->dbaction = 'syncadd';
		$fl->was();

		log_cleanup("- '{$fl->nname}' ('{$c->nname}') at '{$fl->syncserver}'");
	}
}

