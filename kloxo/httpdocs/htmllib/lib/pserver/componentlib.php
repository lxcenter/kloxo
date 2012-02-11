<?php 


class Component extends Lxdb {
//Core

//Data
static $__desc=  Array("", "",  "Component Info");
static $__desc_nname =  Array("S","",  "component_name");
static $__desc_componentname =  Array("S","",  "component_name");
static $__desc_version =  Array("S", "",  "component_version");
static $__desc_full_version =  Array("tS", "",  "detailed_component_info");
static $__desc_status =  Array("eS", "",  "s:status");
static $__desc_status_v_on =  Array("eS", "",  "is_installed");
static $__desc_status_v_off =  Array("eS", "",  "is_not_installed");
static $__rewrite_nname_const =    Array("componentname", "syncserver");

static $__acdesc_list = array("", "",  "component_info");




//Objects

//Lists

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');
	$alist[] = "a=list&c=domain";
	return $alist;
}


function isSelect()
{
	return false;
}

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=list&c=$class";
	if ($login->isLte('pserver')) {
		//$alist[] = "a=addform&c=$class";
	}
	return $alist;
}

static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->syncserver;
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['componentname'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}
static function createListNlist($parent, $view)
{
	$nlist['status'] = '5%';
	$nlist['componentname'] = '100%';
	$nlist['version'] = '10%';
	return $nlist;
}

function updateform($subaction, $param)
{
	$vlist['full_version'] = null;
	return $vlist;
}
function createShowUpdateform()
{
	$uform['update'] = null;
	return $uform;
}

static function initThisObject($parent, $class, $name = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'component');
	$ar = rl_exec_get($parent->__masterserver, $parent->__readserver, array("component__$driverapp", "getDetailedInfo"), $parent->nname);
	$res['full_version'] = $ar;
	$obj = new Component($parent->__masterserver, $parent->__readserver, $name);
	return $obj;
}


static function canGetSingle()
{
	return false;
}

static function initThisListRule($parent, $class)
{
	return null;
}
static function initThisList($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$list = self::initThisArray($parent, $class, array('componentname'));

	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'component');
	$res = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("component__$driverapp", "getListVersion"), array($parent->syncserver, $list));
	return $res;

}


static function initThisArray($parent, $class, $fieldlist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$db = new Sqlite($parent->__masterserver, "component");
	$res = $db->getRowsWhere("syncserver = '$parent->syncserver'");


	if ($res) foreach($res as &$__r) {
		$__r['nname'] = $__r['componentname'] . "___" . $parent->syncserver;
	}
	return $res;

}


}
