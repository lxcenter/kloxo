<?php 
include_once "htmllib/lib/displayinclude.php";

exit_if_secondary_master();
exit_if_another_instance_running();
scavenge_main();

function scavenge_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	passthru("$sgbl->__path_php_path ../bin/gettraffic.php");
	passthru("$sgbl->__path_php_path ../bin/collectquota.php");
	passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");
	passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");
	passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");
	//passthru("$sgbl->__path_php_path htmllib/lbin/getlicense.php");

	initProgramlib('admin');

	
	checkClusterDiskQuota();

	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');

	if ($driverapp === 'lighttpd') {
		system("service lighttpd restart");
	}
	passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");

	passthru("$sgbl->__path_php_path ../bin/installapp-update.phps");

	$rs = get_all_pserver();
	foreach($rs as $r) {
		watchdog::addDefaultWatchdog($r);
	}
	lxguard::collect_lxguard();
	fix_all_mysql_root_password();

	auto_update();


}
