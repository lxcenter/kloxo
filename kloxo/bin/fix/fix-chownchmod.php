<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$type = strtolower($list['type']);

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$clname = $c->getPathFromName('nname');
	$cdir = "/home/{$clname}";
	$dlist = $c->getList('domaina');

	shell_exec("chown {$clname}:apache {$cdir}/");
	shell_exec("chmod 770 {$cdir}/");

	foreach((array) $dlist as $l) {
		$web = $l->nname;
		if (($type === "all") || ($type === 'chown')) {
			shell_exec("chown -R {$clname}:{$clname} {$cdir}/{$web}/");
		}
		if (($type === "all") || ($type === 'chmod')) {
			shell_exec("find {$cdir}/{$web}/ -type f -name \"*.php\" -exec chmod 644 {} \;");
			// shell_exec("find {$cdir}/{$web}/ -type f -exec chmod 644 {} \;");
			shell_exec("find {$cdir}/{$web}/ -type d -exec chmod 755 {} \;");
		}
	}
}


