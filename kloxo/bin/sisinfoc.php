<?php 
include_once "htmllib/lib/include.php"; 
include_once "htmllib/lib/lxguardincludelib.php";
$global_dontlogshell = true;
exit_if_secondary_master();
exit_if_another_instance_running();
debug_for_backend();



watchdog__sync::watchRun();


if ($sgbl->is_this_master()) {
	$gbl->is_master = true;
	initProgram('admin');
	run_mail_to_ticket();
}


monitor_load();
collect_traffic();
lxguard_main();
add_to_log("/var/log/kloxo/smtp.log");
add_to_log("/var/log/kloxo/courier");



function collect_traffic()
{
	$flfile = "__path_program_etc/last_sisinfoc";
	$ret = lfile_get_unserialize($flfile);
	$interval = 20 * 60;
	//$interval = 2;
	if ((time() - $ret['time']) < $interval) {
		//return;
	}
	$oldtime = $ret['time'];
	//if (!$oldtime) { $oldtime = time() - 20 * 60 ; }
	if (!$oldtime) { $oldtime = time() - 5 * 60 ; }
	$newtime = time();
	$ret['time'] = time();
	lfile_put_serialize($flfile, $ret);

	//mailtraffic:generateGraph($oldtime, $newtime);
	//webtraffic::generateGraph($oldtime, $newtime);
}

