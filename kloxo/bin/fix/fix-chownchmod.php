<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$clname = $c->getPathFromName('nname');
	$cdir = "/home/{$clname}";
	$dlist = $c->getList('domaina');

	passthru("chown {$clname}:apache {$cdir}/");
	echo "chown {$clname}:apache {$cdir}/"."\n";
	passthru("chmod 770 {$cdir}/");
	echo "chmod 770 {$cdir}/"."\n";

	foreach((array) $dlist as $l) {
		$web = $l->nname;

		if (($select === "all") || ($select === 'chown')) {
			passthru("chown -R {$clname}:{$clname} {$cdir}/{$web}/");
			echo "chown -R {$clname}:{$clname} {$cdir}/{$web}/"."\n";
		}
		if (($select === "all") || ($select === 'chmod')) {
			passthru("find {$cdir}/{$web}/ -type f -name \"*.php\" -exec chmod 644 {} \;");
			echo "find {$cdir}/{$web}/ -type f -name \"*.php\" -exec chmod 644 {} \;"."\n";
			// passthru("find {$cdir}/{$web}/ -type f -exec chmod 644 {} \;");
			// echo "find {$cdir}/{$web}/ -type f -exec chmod 644 {} \;"."\n";
			passthru("find {$cdir}/{$web}/ -type d -exec chmod 755 {} \;");
			echo "find {$cdir}/{$web}/ -type d -exec chmod 755 {} \;"."\n";
		}
	}
}


