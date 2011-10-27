<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

setFixChownChmod($select);

/* ****** BEGIN - setFixChownChmod ***** */

function setFixChownChmod($select)
{
	global $gbl, $sgbl, $login, $ghtml;

	$login->loadAllObjects('client');
	$list = $login->getList('client');

	log_cleanup("Fix chown and chmod for domains");
	
	$userdirchmod = '750';
	$phpfilechmod = '644';
	$domdirchmod = '755';

	foreach($list as $c) {
		$clname = $c->getPathFromName('nname');
		$cdir = "/home/{$clname}";
		$dlist = $c->getList('domaina');
	
		system("chown {$clname}:apache {$cdir}/");
		log_cleanup("- chown {$clname}:apache FOR {$cdir}/");
		
		system("chmod {$userdirchmod} {$cdir}/");
		log_cleanup("- chmod {$userdirchmod} FOR {$cdir}/");

		foreach((array) $dlist as $l) {
			$web = $l->nname;

			if (($select === "all") || ($select === 'chown')) {
				system("chown -R {$clname}:{$clname} {$cdir}/{$web}/");
				log_cleanup("- chown {$clname}:{$clname} FOR {$cdir}/{$web}/ AND INSIDE");
			}
			
			if (($select === "all") || ($select === 'chmod')) {
				system("find {$cdir}/{$web}/ -type f -name \"*.php*\" -exec chmod {$phpfilechmod} \{\} \\;");
				log_cleanup("- chmod {$phpfilechmod} FOR *.php* INSIDE {$cdir}/{$web}/");
				
				system("find {$cdir}/{$web}/ -type d -exec chmod {$domdirchmod} \{\} \\;");
				log_cleanup("- chmod {$domdirchmod} FOR {$cdir}/{$web}/ AND INSIDE");
			}
		}
	}
}

/* ****** END - setFixChownChmod ***** */


