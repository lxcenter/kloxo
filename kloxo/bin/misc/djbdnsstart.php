<?php 

include_once "htmllib/lib/include.php"; 

if (!posix_getpwnam('tinydns')) {
	system("useradd tinydns");
}
if (!posix_getpwnam('dnslog')) {
	system("useradd dnslog");
}

if (!posix_getpwnam('dnscache')) {
	system("useradd dnscache");
}
if (!posix_getpwnam('axfrdns')) {
	system("useradd axfrdns");
}

if (!lxfile_exists("/var/tinydns")) {
	system("tinydns-conf tinydns dnslog /var/tinydns 127.0.0.1");
}

if (!lxfile_exists("/var/axfrdns")) {
	system("axfrdns-conf axfrdns dnslog /var/axfrdns /var/tinydns 0.0.0.0");
}

//  Project issue #949 - Hardcode 0.0.0.0
//  $list = os_get_allips();
//
//  $out = implode("/", $list);
//
$out = "0.0.0.0\n";
//
//  Kloxo development version has this file refactored
//  LxCenter DT05022014
//

lfile_put_contents("/var/tinydns/env/IP", "$out");

if (!lxfile_exists("/var/dnscache")) {
	system("dnscache-conf dnscache dnslog /var/dnscache 127.0.0.1"); 
}

lfile_put_contents("/var/axfrdns/tcp", ":allow");
system("cd /var/axfrdns;  /usr/local/bin/tcprules tcp.cdb tcp.tmp < tcp");

lfile_put_contents("/var/dnscache/env/IP", "127.0.0.1");
