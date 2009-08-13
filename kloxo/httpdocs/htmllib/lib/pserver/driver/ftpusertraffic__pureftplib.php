<?php 

class ftpusertraffic__pureftp extends lxDriverClass {


static function findTotalQuota($list, $oldtime, $newtime)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if(!isset($oldtime)) {
		return null;
	}
	$processfile =  '/var/log/kloxo/pureftpd.log';
	$processedir = "/var/log/kloxo";


	foreach($list as $d) {
		$tlist[$d]  = self::getftp_usage($processfile, $d, $oldtime, $newtime);
	}

	$stat = stat($processfile);
	if ($stat['size'] >= 25 * 1024 * 1024 ) {
		lxfile_mv($processfile, getNotexistingFile($processedir, basename($processfile)));
	}
	return $tlist;
}


static function getftp_usage($file, $domainname , $oldtime ,$newtime )
{ 
	global $gbl, $sgbl, $login, $ghtml; 
	$total =  self::getEachfileqouta($file, $domainname , $oldtime , $newtime);

	return $total;
}


static function LogConvertString($string) 
{
	$line = preg_replace('/\s+/', " ", $string);
	list($ip, $h, $f, $tdate, $timestamp, $modethid, $url,  $code, $size) = explode(" ", $line);
	$edate = str_replace("[", "", $tdate);
	list($date , $month, $year1) = explode("/", $edate, 3);
	list($year, $hour, $min, $second) = explode(":", $year1, 4);
	$s = mktime($hour, $min, $second, monthToInt($month), $date, $year);
	//dprint("Date" . date("d-M-Y-H-i-s", $s). " " .  $tdate. "\n");
	return $size;
}


static function getTimeFromString($string) 
{
	$line = preg_replace('/\s+/', " ", $string);
	list($ip, $h, $f, $tdate, $timestamp, $modethid, $url, $code, $size) = explode(" ", $line);
	$edate = str_replace("[", "", $tdate);
	list($date , $month, $year1) = explode("/", $edate, 3);
	list($year, $hour, $min, $second) = explode(":", $year1, 4);
	$s = mktime($hour, $min, $second, monthToInt($month), $date, $year);
	//dprint("Date" . date("d-M-Y-H-i-s", $s). " " .  $tdate. "\n");
	return $s;
}



static function  getEachfileqouta($file , $domainname , $oldtime , $newtime) 
{
	$fp = @fopen($file, "r");

	print("Opening File name is :$file\n");

	error_reporting(0);

	if(!$fp){
		return 0;
	}
	$fsize = filesize($file);


	print("Here U are in Mail log file Size is:$fsize\n");

	if($fsize <= 5){
		return 0;
	}
	$total = 0;

	$ret = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("ftpusertraffic__pureftp", "getTimeFromString"));

	if ($ret < 0) {
		dprint("Could not find position\n");
		return;
	}


	$total = 0;
	while(!feof($fp)) {
		$string = fgets($fp);
		if (csa($string, $domainname)) {
			$total += self::LogConvertString($string);
		}
		if (self::getTimeFromString($string) > $newtime) {
			break;
		}
	}

	$total = $total / (1024 * 1024);
	$total = round($total, 1);
	fclose($fp);
	dprint("Returning Total From OUT SIDE This File: for $domainname $total \n");
	return $total;
}


}

