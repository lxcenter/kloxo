<?php 
include_once "htmllib/lib/include.php"; 
exit_if_secondary_master();

lxshell_return("__path_php_path", "../bin/misc/newInstallFixIpaddress.php");

if (lxfile_exists("/proc/xen")) {
	create_dev();
}

$flg = "__path_program_start_vps_flag";

if (lxfile_exists($flg)) {
	exit;
}


dprint("Execing fix ipa\n");
lxshell_return("__path_php_path", "../bin/update.php");


