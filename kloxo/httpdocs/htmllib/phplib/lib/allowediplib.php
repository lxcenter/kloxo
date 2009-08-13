<?php 

class allowedip extends lxdb {

static $__desc = array("", "",  "allowed_ip");
static $__desc_nname = array("", "",  "allowed_ip_range");
static $__desc_ipaddress = array("n", "",  "allowed_ip_range");
static $__desc_current_ip_f = array("", "",  "your_current_ip");
static $__rewrite_nname_const =    Array("ipaddress", "parent_clname");

function isSync() 
{ 
	if_demo_throw_exception('ip');
	return false ; 
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=allowedip";
	$alist[] = "a=addform&c=allowedip";
	$alist[] = "a=list&c=blockedip";
	$alist[] = "a=addform&c=blockedip";
	return $alist;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['current_ip_f'] = array('M', $_SERVER['REMOTE_ADDR']);
	$vlist['ipaddress'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListNlist($parent, $view)
{
	$nlist['ipaddress'] = null;
	return $nlist;
}

}
