<?php 
include_once "htmllib/lib/displayinclude.php";

print_time("gettraffic");
gettraffic_main();
$val = print_time("gettraffic", "Get Traffic ");
log_log("get_traffic", $val);


function gettraffic_main()
{
	global $argc, $argv;
	$list = parse_opt($argv);
	if (isset($list['delete-table']) && $list['delete-table'] === 'yes') {
		print("clearing Traffic Table\n");
		clearTrafficTable();
		filltraffictable();
	} else {
		filltraffictable();
	}
}

//testFunc();

function clearTrafficTable()
{
	$sql = new Sqlite(null, "domaintraffic");
	$sql->rawquery("delete from domaintraffic;");
}

function filltraffictable() 
{
	global $gbl, $login, $ghtml; 
	initProgram('admin');
	$login->loadAllObjects('client');
	$clist = $login->getList('client');
	$t="";
 // Fake domain to store the time the last stats finding was done.

	$laccessdom = new Domain(null, null, '__last_access_domain_');
	try {
		$laccess = $laccessdom->getFromList('domaintraffic', '__last_access_domain_');
	} catch (exception $e) {
		dprint("not getting\n");
		$laccess = null;
	}


	if (!$laccess) {
        $laccess = new Domaintraffic(null, null, '__last_access_domain_');
        $oldtime = 0;
		$laccess->parent_clname = 'domain-__last_access_domain_';
        $laccess->dbaction = 'add';
    } else {
        $oldtime = $laccess->timestamp;
    }

	if ($oldtime && ((time() - $oldtime) > 12 * 3600 * 24) ) {
		$oldtime = time() - 12 * 3600 * 24;
		$laccess->timestamp = $oldtime;
		$laccess->setUpdateSubaction();
		$laccess->write();
	}

	foreach($clist as $c) {
		$domlist = $c->getList('domain');
		foreach((array)$domlist as $domain) {
			if (!$domain->isDomainVirtual()) {
				continue;
			}
			$web = $domain->getObject('web');
			$mmail= $domain->getObject('mmail');
			$globaldomlist[$domain->nname] = $domain;
			$weblist[$web->syncserver][] = $domain;
			$mmaillist[$mmail->syncserver][] = $mmail;
		}
	}




	$flag = 0;
	if($oldtime == 0) {
		// 8 days back
		$oldtime  =  @ mktime(00, 01, 00, date("n"), date("j") - 10, date("Y"));
		// Start of Jan
		//$oldtime  =  mktime( 00 , 01, 00 , 1 ,1, date("Y"));
		$flag = 1;
	}
	
	// $newtime =   mktime( 00 , 01, 00 , date("n")  , date("j")  ,date("Y"));

    $newtime = time();

	$old = $oldtime;
	$new = $newtime;

	if(($newtime - $oldtime) >= (19 * 60 * 60)) {
		if($flag == 1) {  
			$old   =  @ mktime(00, 01, 00, @ date("n"), @ date("j"), @ date("Y"));
			$timearray[0] = $newtime . "-" . $old; 
			$newtime = $old;
		}
		$j=0;

		for($i = $newtime; $i >= $oldtime ; $i-= (24 *  60 * 60)) {
			if($j > 0) { 
				$timearray[]  = $new . "-" . $i ;
			}
			$new = $i;
			$j++;
		}

		if($flag != 1) {
			$timearray[] =  "$new-$oldtime";
		}
		$timearray = array_reverse($timearray);

		foreach($timearray as $t1) {
			$t = explode("-", $t1);
			$newtime = $t[0];
			$oldtime = $t[1];
			if ($newtime - $oldtime < 4 * 60 * 60) { continue; }
			$o = @ strftime("%c", $oldtime);
			$n = @ strftime("%c", $newtime);
			print("\n\n$o  to ... $n\n\n"); 
			findtraffic($weblist, $mmaillist, $globaldomlist, $oldtime, $newtime);
			$laccess->timestamp = $newtime;
			$laccess->setUpdateSubaction();
			$laccess->write();
		}

		// This is the time at which this was run last time.
		$laccess->timestamp = time();
		$laccess->setUpdateSubaction();
		$laccess->write();
	} else {
		dprint("Less than a day:");
		dprint("\n\n\n\n");
	}

} 
 
function get_mailaccountlist($m)
{
	 $list = array();
	  foreach($m as $v) {
		$mac = $v->getList('mailaccount');
		if ($mac) {
			$list = array_merge($list, get_namelist_from_objectlist($mac));
		}
	}
  return $list;
}


function findtraffic($weblist, $mmaillist, $globaldomlist, $oldtime, $newtime)
{

	global $gbl, $login, $ghtml; 

	$web_usage = null;

	$gen = $login->getObject('general')->generalmisc_b;
	$webstatsprog = $gen->webstatisticsprogram;
	if (!$webstatsprog) { $webstatsprog = "awstats"; }

	foreach($weblist as $k => $dlist) {
		$list = null;
		foreach($dlist as $d) {
			$rt = new Remote();
			$rt->iisid = $d->getObject('web')->iisid;
			$rt->nname = $d->nname;
			$rt->customer_name = $d->getRealClientParentO()->getPathFromName('nname');
			$rt->priv = new Priv(null, null, $d->nname);
			$rt->priv->awstats_flag = $d->priv->awstats_flag;
			$rt->stats_username = $d->getObject('web')->stats_username;
			$rt->stats_password = $d->getObject('web')->stats_password;
			$rt->remove_processed_stats = $d->getObject('web')->remove_processed_stats;
			$list[$d->nname] = $rt;
		}
		$driverapp = $gbl->getSyncClass(null, $k, 'web');
		$web_usaget = rl_exec_get(null, $k, array("webtraffic", 'findTotaltrafficwebUsage'), array($driverapp, $webstatsprog, $list, $oldtime, $newtime)); 
		$web_usage = lx_array_merge(array($web_usage , $web_usaget));
	}

	foreach($mmaillist as $k => $m) {
		$mlist = get_namelist_from_objectlist($m);
		$driverapp = $gbl->getSyncClass(null, $k, 'mmail');
		$mailusaget  = rl_exec_get(null, $k, array("mailtraffic", 'findTotalmailQuota'), array($driverapp, $mlist, $oldtime, $newtime)); 
		$mailusage = lx_array_merge(array($mailusaget, $mailusage));
	}

	try {
		foreach($weblist as $k => $w) {
			$wlist = get_namelist_from_objectlist($w);
			$driverapp = $gbl->getSyncClass(null, $k, 'ftpuser');
			$ftpusaget  = rl_exec_get(null, $k, array("ftpusertraffic__$driverapp", 'findTotalQuota'), array($wlist, $oldtime, $newtime)); 
			$ftpusage = lx_array_merge(array($ftpusaget, $ftpusage));
		}
	} catch (exception $e) {
		print($e->getMessage(). "\n");
	}

	//dprintr($web_usage);
	//dprintr($mailusage);
	//dprintr($ftpusage);
	$res="";
	foreach($globaldomlist as $d) {
		$res['nname'] = "$d->nname:$oldtime:$newtime";
		$domt = new Domaintraffic(null, null, $res['nname']);
		$res['ddate'] = time();
		$res['timestamp'] =    strftime("%c", $newtime);
		$res['oldtimestamp'] = strftime("%c", $oldtime);
		$res['comment'] = null;
		$res['parent_list'] = null;
		$res['parent_clname'] = $d->getClName();
		$res['webtraffic_usage']  =  $web_usage[$d->nname];
		$res['mailtraffic_usage'] = $mailusage[$d->nname];
		$res['ftptraffic_usage'] = $ftpusage[$d->nname];
		$res['traffic_usage']  = $res['ftptraffic_usage'] + $res['webtraffic_usage'] + $res['mailtraffic_usage'];
//		print_r($res);
		$domt->create($res);
		$domt->was();
	}
} 


function testFunc()
{
	$inittime = time();
	$hour = 0;
	$min = 0;
	$second = 0;
	$month = 'Jun';
	$date = 21;
	$year = 2005;
	$s1 = mktime($hour, $min, $second, monthToInt($month), $date, $year);
	$date = 22;
	$s2 = mktime($hour, $min, $second, monthToInt($month), $date, $year);
	$res = getEachwebfilequota("/home/root/paid-my-traffic.com-custom_log", $s1, $s2);
	$total = time() - $inittime;
	dprint("Total Time $total seconds \n");
}

