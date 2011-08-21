<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

$login->loadAllObjects('client');
$list = $login->getList('client');

print("\n");

foreach($list as $c) {
	$clname = $c->getPathFromName('nname');
	$cdir = "/home/{$clname}";
	$dlist = $c->getList('domaina');

	passthru("chown {$clname}:apache {$cdir}/");
	print("chown {$clname}:apache FOR {$cdir}/\n" );
	passthru("chmod 770 {$cdir}/");
	print("chmod 770 FOR {$cdir}/\n");

	foreach((array) $dlist as $l) {
		$web = $l->nname;

		if (($select === "all") || ($select === 'chown')) {
			passthru("chown -R {$clname}:{$clname} {$cdir}/{$web}/");
			print("chown {$clname}:{$clname} FOR {$cdir}/{$web}/ AND INSIDE\n");
		}
		if (($select === "all") || ($select === 'chmod')) {
			passthru("find {$cdir}/{$web}/ -type f -name \"*.php*\" -exec chmod 644 {} \;");
			print("chmod 644 FOR *.php* INSIDE {$cdir}/{$web}/\n");
			// passthru("find {$cdir}/{$web}/ -type f -exec chmod 644 {} \;");
			// echo "find {$cdir}/{$web}/ -type f -exec chmod 644 {} \;\n";
			passthru("find {$cdir}/{$web}/ -type d -exec chmod 755 {} \;");
			print("chmod 775 FOR {$cdir}/{$web}/ AND INSIDE\n");
		}
		print("\n");
	}
}


