<?php 

class mailtraffic extends lxclass {

function get() {}
function write() {}


static function generateGraph($oldtime, $newtime)
{
	$convertedfile = self::convertfile($oldtime, $newtime);
	if (!$convertedfile) {
		return;
	}
	$list = lscandir("__path_mail_root/domains");
	foreach($list as $l) {
		if (csb($l, "lists.")) {
			continue;
		}
		$total = self::getmail_usage($convertedfile, $l, $oldtime, $newtime);
		execRrdSingle("mailtraffic", "ABSOLUTE", $l, $total * 1024 * 1024);
	}

	lunlink($convertedfile);
}


static function convertfile($oldtime, $newtime)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$file =  '/var/log/kloxo/maillog';
	if (!lxfile_real($file)) { return null; }
	$fp = fopen($file, "r");

	$fsize = filesize($file);
	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("mailtraffic", "getTimeFromOriginalQmailString"));

	if ($ret < 0) {
		dprint("Could not find position\n");
		return;
	}

	$totstring = null;
	$count = 0;
	while(!feof($fp)) {
		$count++;
		if ($count > 1000000) {
			break;
		}
		$string = fgets($fp);
		$totstring .= $string;
		if (self::getTimeFromOriginalQmailString($string) > $newtime) {
			break;
		}
	}

	fclose($fp);
	$convertfile = tempnam("/tmp/", "mail_log");
	$convertfile_source = tempnam("/tmp/", "mail_log_source");
	lfile_put_contents($convertfile_source, $totstring);
	system("perl {$sgbl->__path_kloxo_httpd_root}/awstats/tools/maillogconvert.pl standard < $convertfile_source > $convertfile");
	lunlink($convertfile_source);
	return $convertfile;
}


static function findTotalmailQuota($driver, $list, $oldtime, $newtime)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if(!isset($oldtime)) { return null; }

	$file =  '/var/log/kloxo/maillog';
	$processedir = "/var/log/kloxo";

	$convertedfile = self::convertfile($oldtime, $newtime);

	if (!$convertedfile) {
		self::rotateLog($processedir, $file);
		return;
	}

	foreach($list as $d) {
		$tlist[$d]  = self::getmail_usage($convertedfile, $d, $oldtime, $newtime);
	}
	lunlink($convertedfile);

	self::rotateLog($processedir, $file);
	self::rotateLog("/var/log/kloxo/", "/var/log/kloxo/courier");
	self::rotateLog("/var/log/kloxo/", "/var/log/kloxo/smtp.log");
	return $tlist;
}

static function rotateLog($processedir, $file)
{
	$stat = stat($file);
	if ($stat['size'] >= 10 * 1024 * 1024) {
		lxfile_mv($file, getNotexistingFile($processedir, basename($file)));
		createRestartFile("syslog");
	}
	$list = lscandir_without_dot($processedir);
	foreach($list as $k) {
		$file = "$processedir/$k";
		$stat = stat($file);
		if ($stat['mtime'] < time() - 10 * 24 * 3600) {
			dprint("deleting old log $file\n");
			lxfile_rm($file);
		}
	}

}


static function getmail_usage($file, $domainname, $oldtime ,$newtime )
{ 
	global $gbl, $sgbl, $login, $ghtml; 
	$total =  self::getEachmailfileqouta($file, $domainname , $oldtime , $newtime);

	return $total;
}

static function qmailLogConvertString($line)
{
	list($month , $date, $from, $to, $domain, $minus1, $prot, $minu2, $status, $size) = explode(" ", $line);
	return $size;
}


static function getTimeFromOriginalQmailString($line)
{
	
	///2006-03-10 07:00:01

	$line = trimSpaces($line);
	$year = @ date('y');
	list($month, $day, $time) = explode(" ", $line);
	$month = get_num_for_month($month);
	list($hour , $min , $sec ) = explode(':' , $time);
	//$s  =  mktime($hour , $min , $sec , monthToInt($month), str_pad($day , 2, 0, STR_PAD_LEFT) , $year);
	$s  = @  mktime($hour, $min, $sec, $month, $day, $year);
	//dprint(" $date $time $hour, $min $sec $month, $day , $year, Time: $s\n");
	// Return date and size. The size param is not important. Our aim is to find the right position.
	return $s;
}

static function getTimeFromString($line)
{
	
	///2006-03-10 07:00:01
	list($date, $time, ) = explode(" ", $line);
	list($year, $month, $day) = explode("-", $date);
	list($hour , $min , $sec ) = explode(':' , $time);
	//$s  =  mktime($hour , $min , $sec , monthToInt($month), str_pad($day , 2, 0, STR_PAD_LEFT) , $year);
	$s  =  mktime($hour, $min, $sec, $month, $day, $year);
	//dprint(" $date $time $hour, $min $sec $month, $day , $year, Time: $s\n");
	// Return date and size. The size param is not important. Our aim is to find the right position.
	return $s;
}


static function  getEachmailfileqouta($file, $domainname, $oldtime, $newtime) 
{
	dprint("Opening File name is :$file\n");

	//error_reporting(0);

	$fp = @ fopen($file, "r");

	if (!$fp) { return 0; }

	while(!feof($fp)) {
		$string = fgets($fp);
		if (csa($string, $domainname)) {
			$total += self::qmailLogConvertString($string);
		}
	}

	fclose($fp);
	$total = $total / (1024 * 1024);
	$total = round($total, 1);
	dprint("Returning Total From OUT SIDE This File: for $domainname $total \n");
	return $total;
}
}
