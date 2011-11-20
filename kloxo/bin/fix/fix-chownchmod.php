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

	// --- still problem when switch to suphp use chmod 750 for userdir and then back to 770
	// so, for compromise set /home/kloxo/httpd to 770 because less importance than /home/<client>
	$httpddirchmod = '770';
	$userdirchmod = '750';
	$phpfilechmod = '644';
	$domdirchmod = '755';

	// --- change because escape form suphp error

	// --- for /home/kloxo/httpd dirs (defaults pages)

	log_cleanup("Fix file permission problems for defaults pages (chown/chmod files)");

	system("chown -R lxlabs:lxlabs /home/kloxo/httpd/");
	log_cleanup("- chown lxlabs:lxlabs FOR INSIDE /home/kloxo/httpd/");

	system("chown lxlabs:apache /home/kloxo/httpd/");
	log_cleanup("- chown lxlabs:apache FOR /home/kloxo/httpd/");

	system("find /home/kloxo/httpd/ -type f -name \"*.php*\" -exec chmod {$phpfilechmod} \{\} \\;");
	log_cleanup("- chmod {$phpfilechmod} FOR *.php* INSIDE /home/kloxo/httpd/");
				
	system("find /home/kloxo/httpd/ -type d -exec chmod {$domdirchmod} \{\} \\;");
	log_cleanup("- chmod {$domdirchmod} FOR /home/kloxo/httpd/ AND INSIDE");

	system("chmod {$httpddirchmod} /home/kloxo/httpd/");
	log_cleanup("- chmod {$httpddirchmod} FOR /home/kloxo/httpd/");

	// --- for domain dirs
	log_cleanup("Fix file permission problems for domains (chown/chmod files)");

	foreach($list as $c) {
		$clname = $c->getPathFromName('nname');
		$cdir = "/home/{$clname}";
		$dlist = $c->getList('domaina');
		$ks = "kloxoscript";
	
		system("chown {$clname}:apache {$cdir}/");
		log_cleanup("- chown {$clname}:apache FOR {$cdir}/");
		
		system("chmod {$userdirchmod} {$cdir}/");
		log_cleanup("- chmod {$userdirchmod} FOR {$cdir}/");

		system("chown {$clname}:apache {$cdir}/{$ks}/");
		log_cleanup("- chown {$clname}:apache FOR {$cdir}/{$ks}/");

		system("chown -R {$clname}:{$clname} {$cdir}/{$ks}/");
		log_cleanup("- chown {$clname}:{$clname} FOR {$cdir}/{$ks}/ AND INSIDE");

		system("find {$cdir}/{$ks}/ -type f -name \"*.php*\" -exec chmod {$phpfilechmod} \{\} \\;");
		log_cleanup("- chmod {$phpfilechmod} FOR *.php* INSIDE {$cdir}/{$ks}/");
				
		system("find {$cdir}/{$ks}/ -type d -exec chmod {$domdirchmod} \{\} \\;");
		log_cleanup("- chmod {$domdirchmod} FOR {$cdir}/{$ks}/ AND INSIDE");

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


