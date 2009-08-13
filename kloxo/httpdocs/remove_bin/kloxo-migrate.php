<?php 

@ mkdir("../pid");
@ mkdir("../log");
include_once "htmllib/lib/include.php"; 


os_fix_lxlabs_permission();


cp_rec_if_not_exists("/etc/httpd/conf/lxadmin/", "/etc/httpd/conf/kloxo");
cp_if_not_exists("/etc/httpd/conf/lxadmin/lxadmin.conf", "/etc/httpd/conf/kloxo/kloxo.conf");
cp_if_not_exists("/var/bogofilter/lxadmin.wordlist.db", "/var/bogofilter/kloxo.wordlist.db");
change_lxadmin_to_kloxo_directory("/etc/httpd/conf/kloxo/");
change_lxadmin_to_kloxo("/etc/httpd/conf/httpd.conf");
change_lxadmin_to_kloxo("/etc/php.ini");
cp_rec_if_not_exists("/usr/lib/lxadminphp/", "/usr/lib/kloxophp");
cp_rec_if_not_exists("/var/tinydns/root/lxadmin", "/var/tinydns/root/kloxo");
lxfile_mkdir("/var/log/kloxo");
change_lxadmin_to_kloxo("/etc/syslog.conf");
change_lxadmin_to_kloxo("/etc/init.d/courier-imap");
change_lxadmin_to_kloxo("/usr/bin/lxredirecter.sh");
change_lxadmin_to_kloxo("/etc/xinetd.d/pureftp");
change_lxadmin_to_kloxo_directory("/etc/awstats/");
lxfile_mkdir("/home/kloxo");
mv_rec_if_not_exists("/home/lxadmin/httpd/", "/home/kloxo/httpd/");
cp_rec_if_not_exists("/home/lxadmin/lxguard/", "/home/kloxo/lxguard/");
mv_rec_if_not_exists("/home/lxadmin/client", "/home/kloxo/client");
mv_rec_if_not_exists("/home/lxadmin/domain", "/home/kloxo/domain");
cp_rec_if_not_exists("/home/lxadmin/selfbackup", "/home/kloxo/selfbackup");

lxfile_rm_rec("/usr/local/lxlabs/kloxo/httpdocs/img/custom/");
lxfile_rm_rec("/usr/local/lxlabs/kloxo/httpdocs/img/logo/");
cp_rec_if_not_exists("/usr/local/lxlabs/lxadmin/httpdocs/img/custom", "/usr/local/lxlabs/kloxo/httpdocs/img/custom");
cp_rec_if_not_exists("/usr/local/lxlabs/lxadmin/httpdocs/img/logo", "/usr/local/lxlabs/kloxo/httpdocs/img/logo");

change_http_dir();
change_lighty();
change_dns();
passthru("lphp.exe ../bin/common/tmpupdatecleanup.php");



function change_http_dir()
{
	$list = lscandir_without_dot_or_underscore("/home/httpd/");

	foreach($list as $l) {
		cp_rec_if_not_exists("/home/httpd/$l/lxadminscript", "/home/httpd/$l/kloxoscript");
		change_lxadmin_to_kloxo("/home/httpd/$l/kloxoscript/phpinfo.php");
		cp_rec_if_not_exists("/home/httpd/$l/conf/lxadmin.$l", "/home/httpd/$l/conf/kloxo.$l");
		change_lxadmin_to_kloxo("/home/httpd/$l/conf/kloxo.$l");
		change_lxadmin_to_kloxo("/home/httpd/$l/php.ini");
	}
}

function change_lighty()
{
	cp_rec_if_not_exists("/etc/lighttpd/conf/lxadmin/", "/etc/lighttpd/conf/kloxo");
	cp_if_not_exists("/etc/lighttpd/conf/kloxo/lxadmin.conf", "/etc/lighttpd/conf/kloxo/kloxo.conf");
	change_lxadmin_to_kloxo("/etc/lighttpd/lighttpd.conf");
	change_lxadmin_to_kloxo_directory("/etc/lighttpd/conf/kloxo");
}

function change_dns()
{
	cp_if_not_exists("/var/named/chroot/etc/lxadmin.named.conf", "/var/named/chroot/etc/kloxo.named.conf");
	change_lxadmin_to_kloxo("/var/named/chroot/etc/named.conf");
}


function change_lxadmin_to_kloxo_directory($dir)
{
	if (!lxfile_exists($dir)) { return; }
	$list = lscandir_without_dot_or_underscore($dir);
	foreach($list as $l) {
		change_lxadmin_to_kloxo("$dir/$l");
	}
}


function change_lxadmin_to_kloxo($filename)
{
	$string = lfile_get_contents($filename);
	if (!$string) { return ; }
	$string = str_replace("Lxadmin", "Kloxo", $string);
	$string = str_replace("lxadmin", "kloxo", $string);
	file_put_contents($filename, $string);

}

