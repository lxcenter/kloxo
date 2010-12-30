<?php 

class Module extends Lxdb {

static $__desc = array("S", "",  "module");
static $__desc_nname	 = array("", "",  "module_name", URL_SHOW); 
static $__desc_info	 = array("", "",  "module_information");

static $__acdesc_update_update = array("", "",  "details");



static function initThisListRule($parent, $class)
{
	return "__v_table";
}

function createShowUpdateform()
{
	$uform['update'] = null;
	return $uform;
}

function updateform($subaction, $param)
{
	$vlist['nname'] = array('M', null);
	$vlist['info'] = array('M', null);
	return $vlist;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist[] = 'a=update&sa=collectmodinfo';
	return $alist;
}

static function getModuleList()
{

	return null;
	//$gbl->__module_list;

}


}
