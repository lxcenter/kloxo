<?php 
include_once "htmllib/lib/include.php";

//fixes issue #515
// lxfile_generic_chmod("/home/admin", "0770");
//fixes issue #709
// lxfile_generic_chmod("/home/admin", "0750");

if (lxfile_exists("/etc/httpd/conf.d/php.conf")) {
	lunlink("/etc/httpd/conf.d/suphp.conf");
}
lxfile_cp("../file/suphp.conf", "/etc/httpd/conf.d/suphp.nonconf");
lxfile_cp("../file/etc_suphp.conf", "/etc/suphp.conf");


$driverapp = slave_get_driver('web');

if ($driverapp === 'lighttpd') {
	print("driver is lighty\n");
	exit;
}

print("Installing suPHP\n");

system("yum -y install mod_suphp");

if (lxfile_exists("/etc/httpd/conf.d/php.conf")) {
	lunlink("/etc/httpd/conf.d/suphp.conf");
}

print("Fixing Configuration...\n");

lxfile_cp("../file/suphp.conf", "/etc/httpd/conf.d/suphp.nonconf");
lxfile_cp("../file/etc_suphp.conf", "/etc/suphp.conf");
