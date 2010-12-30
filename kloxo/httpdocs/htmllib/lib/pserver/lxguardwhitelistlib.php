<?php 

class lxguardwhitelist extends lxdb {

static $__desc = array("", "",  "white_list");
static $__desc_ipaddress = array("", "",  "ipaddress");
static $__desc_cur_ip = array("", "",  "your_current_ip");
static $__rewrite_nname_const = array("ipaddress", "syncserver");
static $__acdesc_list = array("", "",  "white_list");


function createExtraVariables()
{
	$parent = $this->getParentO();
	$sq = new Sqlite(null, "lxguardwhitelist");
	$res = $sq->getRowsWhere("syncserver = '$parent->syncserver'", array('nname', 'ipaddress'));
	$this->__var_whitelist = $res;
}

static function createListAddForm($parent, $class) { return true ; }

static function add($parent, $class, $param)
{
	$param['ipaddress'] = trim($param['ipaddress']);
	$param['syncserver'] = $parent->nname;
	return $param;
}

static function createListAlist($pserver, $class)
{
	$alist[] = 'a=show';
	$alist[] = 'a=list&c=lxguardhitdisplay';
	$alist[] = 'a=list&c=lxguardwhitelist';
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['ipaddress'] = '100%';
	return $nlist;
}


static function addform($parent, $class, $typetd = null)
{
	$vlist['cur_ip'] = array('M', $_SERVER['REMOTE_ADDR']);
	$vlist['ipaddress'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}

}
