<?php 
include_once "htmllib/lib/include.php"; 


$driverapp = slave_get_driver('web');

if ($driverapp === 'lighttpd') {
	print("Driver is lighty\n");
	exit;
}


system("yum -y install frontpage");

system("cp -r /usr/local/frontpage/version5.0/apache2/.libs/mod_frontpage.so /etc/httpd/modules/");
system("chmod 755 /var/log/httpd/");

addLineIfNotExistInside("/etc/httpd/conf/httpd.conf", "LoadModule frontpage_module modules/mod_frontpage.so", "");




