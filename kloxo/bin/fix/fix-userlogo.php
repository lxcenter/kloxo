<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php";

initProgram('admin');

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

$list = parse_opt($argv);

$select = strtolower($list['select']);

setFixUserlogo($select);

/* ****** BEGIN - setFixUserlogo ***** */

function setFixUserlogo($select)
{
	global $gbl, $sgbl, $login, $ghtml;

	log_cleanup("Fix userlogo");

	passthru("cp -rf /usr/local/lxlabs/kloxo/file/user-logo.png /home/kloxo/httpd/user-logo.png");
	log_cleanup("- Source FROM /usr/local/lxlabs/kloxo/file/user-logo.png");
	log_cleanup("- Target TO /home/kloxo/httpd/user-logo.png");

	if ($select === 'defaults') {
		setFixUserlogoDefaultPages();
	}
	else if ($select === 'domains') {
		setFixUserlogoDomainPages();
	}
	else if ($select === 'all') {
		setFixUserlogoDefaultPages();
		setFixUserlogoDomainPages();
	}
	else {
		log_cleanup("- Wrong select...");
	}
}

function setFixUserlogoDefaultPages()
{
	passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/cp/images/logo.png");
	log_cleanup("- Target TO /home/kloxo/httpd/cp/images/logo.png");
	passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/default/images/logo.png");
	log_cleanup("- Target TO /home/kloxo/httpd/default/images/logo.png");
	passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/disable/images/logo.png");
	log_cleanup("- Target TO /home/kloxo/httpd/disable/images/logo.png");
	passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/webmail/images/logo.png");
	log_cleanup("- Target TO /home/kloxo/httpd/webmail/images/logo.png");
}

function setFixUserlogoDomainPages()
{
	global $gbl, $sgbl, $login, $ghtml;

	$login->loadAllObjects('client');
	$list = $login->getList('client');

	foreach($list as $c) {
		$clname = $c->getPathFromName('nname');
		$cdir = "/home/{$clname}";
		$dlist = $c->getList('domaina');

		foreach((array) $dlist as $l) {
			$web = $l->nname;
			passthru("cp -rf /home/kloxo/httpd/user-logo.png {$cdir}/{$web}/images/logo.png");
			log_cleanup("- Target TO {$cdir}/{$web}/images/logo.png");
		}
	}
}

/* ****** END - setFixUserlogo ***** */

