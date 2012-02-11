<?php 

class clientmail extends lxclass {

static $__desc = array("n", "",  "mails_per_client");
static $__desc_clientname = array("n", "",  "clientname");
static $__desc_mailnum = array("n", "",  "number_of_mails");
static $__acdesc_list = array("n", "",  "mails_per_client");

function get() {}
function write() {}
function doSyncToSystem() {}


<<<<<<< HEAD
static function createListNlist($parent)
=======
static function createListNlist($parent, $view)
>>>>>>> upstream/dev
{
	$nlist['clientname'] = '100%';
	$nlist['mailnum'] = '10%';
	return $nlist;
}

function isSelect() { return false ; }
static function createListBlist($parent, $class) { return null; }

static function initThisListRule($parent, $class) { return null; }
static function initThisList($parent, $class) 
{
	$res = rl_exec_get(null, $parent->syncserver,  array("clientmail", "readtotallog"), array());
	return $res;
}

static function readtotallog()
{
	$list = self::readsmtpLog();
	$nlist = self::readMaillog();
	$total = lx_merge_good($list, $nlist);
	foreach($total as $k => $v) {
		$re['clientname'] = $k;
		$re['nname'] = $k;
		$re['mailnum'] = $v;
		$res[$k] = $re;
	}
	return $res;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function readsmtpLog()
{

	$date = time() - 24 * 3600 * 2;

	$logfile = "/var/log/kloxo/smtp.log";
	$fp = fopen($logfile, "r");
	$fsize = lxfile_size($logfile);

	$pos = lxlabsFindRightPosition($fp, $fsize, $date, time());
	if ($pos === -1) {
		return;
	}
	$s = fgets($fp);
	dprint("The correct pos here: $s\n");

	while(!feof($fp)) {
		$s = fgets($fp);
		$s = trim($s);
		if (!csa($s, "client allowed to relay")) {
			continue;
		}
		$v = strfrom($s, "rcpt: from <");
		$v = strtilfirst($v, ">");
		$s = explode(":", $v);
		$id = $s[1];
		if (!isset($total[$id])) {
			$total[$id] = 1;
		} else {
			$total[$id]++;
		}
	}
	return $total;

}

static function readMaillog()
{
	$date = time() - 24 * 3600 * 2;

	$logfile = "/var/log/kloxo/maillog";
	$fp = fopen($logfile, "r");
	$fsize = lxfile_size($logfile);
	FindRightPosition($fp, $fsize, $date, time(), array("mailtraffic", "getTimeFromOriginalQmailString"));
	while(!feof($fp)) {
		$s = fgets($fp);
		$s = trim($s);
		if (!csa($s, "lx-sending")) {
			continue;
		}
		$v = strfrom($s, "mail for");
		$id = $v;
		if (!isset($total[$id])) {
			$total[$id] = 1;
		} else {
			$total[$id]++;
		}
	}
	return $total;
}

}
