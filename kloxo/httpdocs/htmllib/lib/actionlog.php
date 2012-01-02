<?php 

class actionlog extends Lxdb {

static $__desc = array("", "", "action_log");
static $__desc_ddate = array("", "", "date");
static $__desc_class = array("", "", "class");
static $__desc_ipaddress = array("", "", "ipaddress");
static $__desc_objectname = array("", "", "objectname");
static $__desc_action = array("", "", "action");
static $__desc_subaction = array("", "", "subaction");
static $__desc_login = array("", "", "loginid");
static $__desc_auxiliary_id = array("", "", "auxiliary");


static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function defaultSort() { return 'ddate'; }
static function defaultSortdir() { return 'desc'; }
function isSelect() { return false; }

static function createListBlist($parent, $class)
{
	return null;
}

static function createListSlist($parent)
{
	$slist['ipaddress'] = null;
	$slist['login'] = null;
	$slist['auxiliary_id'] = null;
	$slist['class'] = null;
	$slist['objectname'] = null;
	$slist['action'] = null;
	$slist['subaction'] = null;
	return $slist;
}

static function createListNlist($parent, $view)
{
	$nlist['ddate'] = '4%';
	$nlist['ipaddress'] = '4%';
	$nlist['login'] = '4%';
	$nlist['auxiliary_id'] = '4%';
	$nlist['class'] = '4%';
	$nlist['objectname'] = '4%';
	$nlist['action'] = '10%';
	$nlist['subaction'] = '100%';
	return $nlist;
}

static function initThisListRule($parent, $class)
{
	if ($parent->isAdmin()) {
		return "__v_table";
	}

	return array("loginclname", "=", "'{$parent->getClName()}'");
}


}
