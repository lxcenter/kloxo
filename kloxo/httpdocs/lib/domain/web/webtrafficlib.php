<?php 
include_once "lib/domain/web/driver/logfile.php";

class webtraffic extends lxclass {

function get() {}
function write() {}



static function generateGraph($oldtime, $newtime)
{
    global $global_dontlogshell;
    $oldv = $global_dontlogshell;
    $global_dontlogshell = true;
    $list = lscandir_without_dot("__path_httpd_root");
    foreach($list as $l) {
		if (!lxfile_exists("__path_httpd_root/$l/stats")) {
			continue;
		}
		$total = webtraffic::getEachwebfilequota("__path_httpd_root/$l/stats/$l-custom_log", $oldtime, $newtime);
		execRrdSingle("webtraffic", "ABSOLUTE", $l, $total * 1024 * 1024);
    }

    $global_dontlogshell = $oldv;
}


static function run_awstats($statsprog, $list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_dontlogshell;

	log_log("run_stats", "In awstats");
	$global_dontlogshell = true;
	foreach($list as $p) {
		log_log("run_stats", "In awstats for $p->nname $statsprog");
		if ($p->priv->isOn('awstats_flag')) {
			lxfile_mkdir("__path_httpd_root/$p->nname/webstats/");

			$name = $p->nname;
			web::createstatsConf($p->nname, $p->stats_username, $p->stats_password);

			if (is_disabled($statsprog)) {
				continue;
			}
			log_log("run_stats", "Execing $statsprog");
			//system("rm /home/httpd/$p->nname/webstats/*");
			if ($statsprog === 'webalizer') {
				print("webalizer: $p->nname\n");
				lxshell_return("nice", "-n", "15", "webalizer", "-n", $p->nname, "-t", $p->nname, "-c", "__path_real_etc_root/webalizer/webalizer.{$p->nname}.conf");

			} else {
				print("awstats: $p->nname\n");
				putenv("GATEWAY_INTERFACE=");
				//system("nice -n 15 perl /home/kloxo/httpd/awstats/wwwroot/cgi-bin/awstats.pl -update -config=$name > /tmp/test 2>&1");
				lxshell_return("nice", "-n", "15", "perl", "__path_kloxo_httpd_root/awstats/wwwroot/cgi-bin/awstats.pl", "-update", "-config=$name");
				//lxshell_return("__path_kloxo_httpd_root/awstats/tools/awstats_buildstaticpages.pl", "-awstatsprog=$sgbl->__path_kloxo_httpd_root/awstats/wwwroot/cgi-bin/awstats.pl", "-dir=$sgbl->__path_httpd_root/$name/webstats/", "-config=$name");
				//lxfile_cp("__path_httpd_root/$name/webstats/awstats.$name.html", "__path_httpd_root/$name/webstats/index.html");
			}
		}
	}

	/// Needed to get the domain list from the files in the /etc/awstats directory.
 /*	$list = lscandir_without_dot("__path_real_etc_root/awstats");
	foreach($list as $l) {
		$p = preg_replace("/awstats\.(.*)\.conf/", "$1", $l);
		dprint($p);
		dprint("\n");
*/
 
}

static function getweb_usage($name, $customer_name, $oldtime, $newtime, $d)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$web_home = "$sgbl->__path_httpd_root";

	$log_path = "$web_home/$name/stats"; 
	$processedir = "$sgbl->__path_customer_root/$customer_name/__processed_stats/";

	lxfile_mkdir($processedir);


	$dir1 = "$log_path/";

	$files = lscandir_without_dot($dir1);

	$total = 0;
	foreach($files as $file) {
		if(!strstr($file, "gz")) {
			$total  += self::getEachwebfilequota("$dir1/$file", $oldtime, $newtime);
			$stat = stat("$dir1/$file");
			if ($stat['size'] >= 50 * 1024 * 1024) {
				if (isOn($d->remove_processed_stats)) {
					lxfile_rm("$dir1/$file");
				} else {
					lxfile_mv("$dir1/$file", getNotexistingFile($processedir, $file));
				}
			}
		}
	}

	return $total;
}

static function apacheLogConvertString($string) 
{
	$p = new ApacheLogRegex();
	$res = $p->parse($string);
	$time = $p->logtime_to_timestamp($res['Time']);
	$size = $res['Bytes-Sent'];
	return array($time, $size);
}

static function apacheLogFullString($string)
{
	$p = new ApacheLogRegex();
	$res = $p->parse($string);
	$res['realtime'] = $p->logtime_to_timestamp($res['Time']);
	return $res;
}

static function getTimeFromString($string) 
{
	$p = new ApacheLogRegex();
	$res = $p->parse($string);
	dprintr($res['Time']);
	$value = $p->logtime_to_timestamp($res['Time']);
	dprint("Value: $value\n");
	return $value;
}





static function  getEachwebfilequota($file, $oldtime, $newtime)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$fp = @lfopen($file, "r");
	$total = 0;

	print("\n$file: " . @ date('Y-m-d-H', $oldtime) . " " . @ date('Y-m-d-H', $newtime) . "\n");
	if(!$fp){
		print("File Does Not Exist:returning Zero"); 
		return 0;
	}

	$fsize = lfilesize($file);

	if($fsize <=10) {
		print("File Size is Less Than Zero and Returning Zero:\n");
		return 0;
	}

	print("File Size is :$fsize\n\n\n");


	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("webtraffic", "getTimeFromString"));
	if ($ret < 0) {
		return;
	}

	print("Current Position " . (ftell($fp)/$fsize)*100 . "\n");

	$total = 0;
	$count = 0;
	$break = 1000;
	while(!feof($fp)) {
		$count++;
		$line = fgets($fp);
		list($time, $size) = self::apacheLogConvertString($line);
		if($time > $newtime) {
			break;
		}
		$total += $size;
		if ($count > 100000) {
			$break = 10000;
		}
		if (!($count % $break)) {
			print("Count $count $newtime $time $total $size\n");
		}
	}

	print("$count lines actually processed in $file\n");
	$total = $total / (1024 * 1024);
	$total = round($total, 1);
	fclose($fp);
	print("Returning Total From OUT SIDE This File: $total \n");
	return $total;
}



static function findTotaltrafficwebUsage($driverapp, $statsprog, $list, $oldtime, $newtime) 
{
	// run awstats only if it is today.
	global $gbl, $sgbl, $login, $ghtml; 
	if ($sgbl->isDebug()) {
		if ((time() - $newtime) < 24 * 3600 * 2) {
			self::run_awstats($statsprog, $list);
		}
	} else {
		self::run_awstats($statsprog, $list);
	}
	
	if(!isset($oldtime)) {
		return null;
	}

	foreach($list as $d) {
		$tlist[$d->nname]  = self::getweb_usage($d->nname, $d->customer_name, $oldtime, $newtime, $d);
	}

    foreach($tlist as  $key=>$t){
		if(!isset($t)) {
			$t =0; 
		}
		$temp[$key]  = $t;
	}
	dprintr($temp);
	createRestartFile($driverapp);
	return $temp;
}
 


}


