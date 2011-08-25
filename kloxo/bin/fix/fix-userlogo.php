<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

// disable because want run on master and slave
// initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

$login->loadAllObjects('client');
$list = $login->getList('client');

print("\n");

print("Start copy...\n");

passthru("cp -rf /usr/local/lxlabs/kloxo/file/user-logo.png /home/kloxo/httpd/user-logo.png");
print("- copy /usr/local/lxlabs/kloxo/file/user-logo.png /home/kloxo/httpd/user-logo.png\n");

passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/cp/images/logo.png");
passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/default/images/logo.png");
passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/disable/images/logo.png");
passthru("cp -rf /home/kloxo/httpd/user-logo.png /home/kloxo/httpd/webmail/images/logo.png");
print("- copy /home/kloxo/httpd/user-logo.png to cp|default|disable|webmail page\n");


foreach($list as $c) {
	$clname = $c->getPathFromName('nname');
	$cdir = "/home/{$clname}";
	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->nname;

		passthru("cp -rf /home/kloxo/httpd/user-logo.png {$cdir}/{$web}/images/logo.png");
		print("- copy /home/kloxo/httpd/user-logo.png to domains\n");
		print("\n");
	}
}

print("... the end\n");
