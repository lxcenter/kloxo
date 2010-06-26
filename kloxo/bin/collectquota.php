<?php 
include_once "htmllib/lib/displayinclude.php";

exit_if_another_instance_running();
initProgram('admin');
debug_for_backend();

$sgbl->__var_collectquota_run = true;

$cmd = parse_opt($argv);

if (!isset($cmd['just-db'])) {
	$sgbl->__var_just_db = false;
	try {
		storeinGblvariables();
	} catch (Exception $e) {
		print($e->getMessage());
		print("\n");
	}
} else {
	$sgbl->__var_just_db = true;
}

$login = null;
initProgram('admin');
collectquota_main();


function collectquota_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	//ob_end_flush();

	try {
		print_time('collect');
		$login->collectQuota();
		$login->metadbaction = 'writeonly';
		$login->was();
		print_time('collect', 'Time Taken To Collect Quota');
	} catch (Exception $e) {
		print("Caught Execption\n");
		print($e->getMessage());
		print("\n");
		print("\n");
		print("\n");
	}
}

function storeinGblvariables()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$login->loadAllObjects('client');

	$clist = $login->getList('client');
	if (!$clist) {
		return;
	}
	
	$mysqldblist = array();
	$clientlist = array();
	$weblist = array();
	$mailaccountlist = array();
	
	foreach($clist as $c) {
		$domlist = $c->getList('domaina');
		$clientlist[$c->websyncserver][] = $c;
		
		$dblist = $c->getList('mysqldb');
		foreach((array) $dblist as $db) {
			$mysqldblist[$db->syncserver][] = $db;
		}
		
		foreach((array) $domlist as $domain) {
			if (!$domain->isDomainVirtual()) {
				continue;
			}

			$web = $domain->getObject('web');
			$mmail= $domain->getObject('mmail');
			
			//$dns = $domain->getObject('dns');
			$weblist[$web->syncserver][] = $web;
			$mclist = $mmail->getList('mailaccount');
			foreach($mclist as $mac) {
				$mailaccountlist[$mmail->syncserver][] = $mac;
			}
			$trafficlist[$domain->nname] = $domain;
		}
	}

	$disk_usage = getTotalUsage('web', $weblist);
	$maildisk_usage = getTotalUsage('mailaccount', $mailaccountlist);
	$mysqldb_usage = getTotalUsage('mysqldb', $mysqldblist);
	$clientdisk_usage = getTotalUsage('client', $clientlist);

	$sgbl->__var_disk_usage = $disk_usage;
	$sgbl->__var_maildisk_usage = $maildisk_usage;
	$sgbl->__var_mysqldb_usage = $mysqldb_usage;
	$sgbl->__var_clientdisk_usage = $clientdisk_usage;



	$firstofmonth  = @ mktime(00, 01, 00, @ date("n"), 1, @ date("Y"));
	 //$today  = mktime( 00 , 01 , 00 , date("n") , date("j") , date("Y")); 
	$today = time();

	foreach((array) $trafficlist as $domain) {
		$domt  = $domain->getList("domaintraffic");
		$list = get_namelist_from_objectlist($domt);
		$total[$domain->getClName()] = trafficGetIndividualObjectTotal($domt, $firstofmonth, $today, $domain->nname);		

		list($month, $year) = get_last_month_and_year();
		$last_traffic = DomaintrafficHistory::getMonthTotal($domt, $month, $year, domaintraffichistory::getExtraVar()); 
		if (!isset($sgbl->__var_traffic_last_usage)) {
			$sgbl->__var_traffic_last_usage = null;
		}
		$sgbl->__var_traffic_last_usage[$domain->getClName()] = $last_traffic['traffic_usage'];
	}

	$sgbl->__var_traffic_usage = $total;
	//dprintr($sgbl->__var_maildisk_usage);
	//dprintr($sgbl->__var_mysqldb_usage);
	//dprintr($sgbl->__var_disk_usage);
	//dprintr($sgbl->__var_clientdisk_usage);
	dprintr($sgbl->__var_traffic_usage);
}


function getTotalUsage($class, $list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$var = null;
	foreach($list as $k => $d) {

		$needlist = null;
		foreach($d as $dp) {
			$nlist = null;
			$nlist = $dp->getQuotaNeedVar();
			$needlist[$dp->getClName()] = $nlist;
		}

		//$userlist = get_namelist_from_objectlist($d, 'nname', 'username');
		$driver = $gbl->getSyncClass(null, $k, $class);
		try {
			$tvar  = rl_exec_get(null, $k, array($class, 'findTotalUsage'), array($driver, $needlist)); 
			if ($class === 'client') {
				dprintr("$k: \n");
				dprintr($tvar);
			}
		} catch (Exception $e) {
			print("Could not get Remote Disk Usage $k\n");
		}
		$var = lx_array_merge(array($var, $tvar));
	}
	return $var;
}


