<?php 


class rawlxguardhit extends lxdb {
static $__table = "lxguardhit";
static $__desc = array("", "",  "raw_connection");
static $__desc_access = array("", "",  "success/failure");
static $__desc_ddate = array("", "",  "time");
static $__desc_service = array("", "",  "service");
static $__desc_user = array("", "",  "user");
static $__desc_ipaddress = array("", "",  "ipaddress");


function isSelect() { return false; }
static function createListBlist($parent, $class)
{
	return null;
}

static function createListAlist($parent, $class)
{
	return lxguardhitdisplay::createListAlist($parent, $class);
}

static function createListNlist($parent, $view)
{

	$nlist['user'] = '10%';
	$nlist['service'] = '10%';
	$nlist['ipaddress'] = '4%';
	$nlist['ddate'] = '4%';
	$nlist['access'] = '100%';
	return $nlist;
}

static function createListSlist($parent)
{
	$slist['user'] = null;
	$slist['service'] = null;
	$slist['ipaddress'] = null;
	$slist['access'] = null;
	return $slist;
}

static function initThisListRule($parent, $class)
{
	return array("syncserver", '=', "'{$parent->syncserver}'");
}

}



class lxguardhit extends lxdb {
static $__desc = array("", "",  "connection");
static $__desc_access = array("", "",  "success/failure");
static $__desc_ddate = array("", "",  "time");
static $__desc_service = array("", "",  "service");
static $__desc_user = array("", "",  "user");


static function createListBlist($parent, $class)
{
	return null;
}


static function createListNlist($parent, $view)
{

	$nlist['service'] = '100%';
	$nlist['user'] = '100%';
	$nlist['ddate'] = '4%';
	$nlist['access'] = '4%';
	return $nlist;
}

static function initThisListRule($parent, $class)
{
	return array("ipaddress", '=', "'{$parent->ipaddress}'");
}

}


class lxguardhitdisplay extends lxclass {
static $__desc = array("", "",  "connection");
static $__desc_ipaddress = array("", "",  "ipaddress", "a=show");
static $__desc_currentip_flag = array("e", "",  "Cur:current_ip_or_not");
static $__desc_currentip_flag_v_dull = array("", "",  "");
static $__desc_currentip_flag_v_on = array("", "", "this_is_your_current_ip");
static $__desc_failcount = array("", "",  "fail");
static $__desc_successcount = array("", "",  "success");
static $__desc_blocked = array("", "",  ".");


function write() { }
function get() { }

function createShowClist($subaction)
{
	$clist['lxguardhit'] = null;
	return $clist;
}

static function createListBlist($parent, $class)
{
	$blist[] = array("a=update&sa=whitelist", 0);
	$blist[] = array("a=update&sa=remove", 0);
	return $blist;
}


static function createListAlist($parent, $class)
{
	$alist[] = "a=show";
	$alist[] = "a=list&c=lxguardhitdisplay";
	$alist[] = "a=list&c=rawlxguardhit";
	$alist[] = "a=list&c=lxguardwhitelist";
	return $alist;
}

function display($var)
{
	if ($var === 'blocked') {
		$wht = $this->getParentO()->getList('lxguardwhitelist');
		$wht = get_namelist_from_objectlist($wht, "ipaddress");
		if (array_search_bool($this->ipaddress, $wht)) {
			return "whitelisted";
		}

		$ds = $this->getParentO()->disablehit;
		if (!$ds) { $ds = 20; }
		if ($this->failcount >= $ds ) {
			return "blocked";
		}
		return null;
	}
	return $this->$var;
}

static function defaultSort() { return 'failcount' ; }
static function defaultSortDir() { return "desc"; }


static function getDataFromServer($syncserver)
{
	$list = rl_exec_get(null, $syncserver, "lxguard_main", array(true));
	foreach($list as $k => $v) {
		foreach($v as $kk => $vv) {
			$l['nname'] = "{$k}___$kk";
			$l['ddate'] = $kk;
			$l['ipaddress'] = $k;
			$l['access'] = $vv['access'];
			$l['user'] = $vv['user'];
			$l['service'] = $vv['service'];
			$l['syncserver'] = $syncserver;
			$obj = new lxguardhit(null, null, $l['nname']);
			$obj->get();
			if ($obj->dbaction === 'add') {
				$obj->create($l);
				$obj->write();
			}
		}
	}
}

static function initThisList($parent, $class)
{
	self::getDataFromServer($parent->syncserver);
	$ret = self::createHitList($parent->syncserver);
	return $ret;
}


static function createHitList($server)
{
	$sq = new Sqlite(null, "lxguardhit");
	$res = $sq->getRowsWhere("syncserver = '$server'");
	foreach($res as $r) {
		$total[$r['ipaddress']][$r['ddate']] = $r;
	}
	foreach($total as $k => $t) {
		$failcount = 0;
		$successcount = 0;
		foreach($t as $kk => $vv) {
			if ($vv['access'] === 'fail') {
				$failcount++;
			} else {
				$successcount++;
			}
		}
		$res['nname'] = $k;
		$res['currentip_flag'] = 'dull';

		if ($k === $_SERVER['REMOTE_ADDR']) {
			$res['currentip_flag'] = 'on';
		}
		$res['ipaddress'] = $k;
		$res['failcount'] = $failcount;
		$res['successcount'] = $successcount;
		$ret[] = $res;
	}
	return $ret;
}

static function createListNlist($parent, $view)
{
	$nlist['blocked'] = '5%';
	$nlist['currentip_flag'] = '5%';
	$nlist['failcount'] = '10%';
	$nlist['successcount'] = '10%';
	$nlist['ipaddress'] = '100%';
	return $nlist;
}

}


