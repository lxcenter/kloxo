<?php 

class ftpsession extends Lxclass {


static $__desc = array("", "",  "ftp_session");

static $__desc_pid = array("", "", "pid");
static $__desc_nname = array("", "", "pid");
static $__desc_account = array("", "", "account");
static $__desc_time = array("", "", "time");
static $__desc_file = array("", "", "file");
static $__desc_host = array("", "", "host");
static $__desc_state = array("", "", "state");


function get() {}
function write() {}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}
static function createListNlist($parent, $view)
{

	$nlist['pid'] = '10%';
	$nlist['state'] = '10%';
	$nlist['account'] = '100%';
	//$nlist['time'] = '10%';
	//$nlist['file'] = '10%';
	//$nlist['host'] = '10%';
	return $nlist;
}

static function initThisListRule($parent, $class) { return null; }
static function initThisList($parent, $class)
{

	if ($parent->is__table('client')) {
		if ($parent->username) {
			$username = $parent->username;
			$res = rl_exec_in_driver($parent, $class, "getFtpList", array($username));
		} else {
			return null;
		}
	} else {
		$res = rl_exec_in_driver($parent, $class, "getFtpList", array());
	}
	foreach($res as &$__r) {
		$__r['parent_clname'] = $parent->getClName();
	}
	return $res;
}


}
