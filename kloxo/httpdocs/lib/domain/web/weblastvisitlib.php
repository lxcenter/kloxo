<?php 

class WebLastVisit extends Lxaclass {

static $__desc = array("", "",  "latest_visitor");
static $__desc_nname = array("", "",  "latest_visitor");
static $__desc_time = array("", "",  "time");
static $__desc_realtime = array("", "",  "realtime");
static $__desc_referer = array("", "",  "referer");
static $__desc_file = array("", "",  "url");
static $__desc_remote_host = array("", "",  "remote_host");


static function initThisList($parent, $class)
{
	$res = rl_exec_get(null, $parent->syncserver,  array("WebLastVisit", "getTrafficInfo"), array($parent->nname));
	return $res;
}

static function perPage() { return 500; }
static function defaultSortDir() { return 'desc' ; }
static function defaultSort() { return 'realtime' ; }
static function createListNlist($parent, $view)
{
	$nlist['realtime'] = '4%';
	$nlist['time'] = '4%';
	$nlist['remote_host'] = '10%';
	$nlist['file'] = '100%';
	$nlist['referer'] = '100%';
	return $nlist;
}

function isSelect() { return false ; }

function display($var)
{
	if ($var === 'file' || $var === 'referer') {
		$vr = substr($this->$var, 0, 60);
		$vv = $this->$var;
		$vr = str_replace(":", " ", $vr);
		$vv = str_replace(":", " ", $vv);
		return "_lxspan:$vr:$vv:";
	}

	return parent::display($var);

}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = "a=show";
	return $alist;
}

static function getTrafficInfo($name)
{
	$oldtime = time() -  40 * 3600;
	$newtime = time();
	$file = "__path_httpd_root/$name/stats/$name-custom_log";
	$file = expand_real_root($file);

	$fp = @lfopen($file, "r");
	$total = 0;

	dprint("\n$file: " . @ date('Y-m-d-H-i-s', $oldtime) . " $newtime " . @ date('Y-m-d-H-i-s', $newtime) . "\n");

	if(!$fp){
		dprint("File Does Not Exist:returning Zero"); 
		return 0;
	}

	$fsize = lfilesize($file);

	if($fsize <=10) {
		dprint("File Size is Less Than Zero and Returning Zero:\n");
		return "";
	}

	dprint("File Size is :$fsize\n\n\n");

	if ($fsize > 20 * 1024) {
		fseek($fp, -19 * 1024, SEEK_END);
		$line = fgets($fp);
	}

	$i = 3;

	$total = 0;
	$count = 0;
	while(!feof($fp)) {
		$count++;
		$line = fgets($fp);
		$res[] = webtraffic::apacheLogFullString($line);
	}

	$c = 0;
	foreach($res as $k => $r) {
		$c++;
		if (($count - 50) > $c) {
			unset($res[$k]);
		}
	}
	$ncount = 0;
	foreach($res as $r) {
		if (!$r['Time']) {
			continue;
		}
		$file = strfrom($r['Request'], " ");
		$file = strtil($file, "HTTP");
		$time = trim($r['Time'], "[]");
		$time = strtil($time, " ");
		$o['realtime'] = $r['realtime'];
		$o['time'] = $time;
		$o['nname'] = $ncount;
		$o['file'] = $file;
		$o['referer'] = $r['Referer'];
		$o['remote_host'] = $r['Remote-Host'];
		$out[] = $o;
		$ncount++;
	}

		
	return $out;
}
}
