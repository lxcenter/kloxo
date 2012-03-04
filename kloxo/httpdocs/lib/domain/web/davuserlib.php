<?php 

class davuser extends Lxclient {


static $__desc = array("", "",  "dav_user");
static $__desc_nname  	 = array("n", "",  "dav_user_name", URL_SHOW);
static $__desc_username  	 = array("n", "",  "dav_user_name", URL_SHOW);
static $__desc_directory  	 = array("n", "",  "virtual_directory", URL_SHOW);
static $__desc_status = array("e", "",  "s:status", URL_TOGGLE_STATUS);
static $__desc_status_v_on = array("", "",  "on");
static $__desc_status_v_off = array("", "",  "off");
static $__rewrite_nname_const =    Array("username", "parent_clname");

static $__acdesc_update_edit = array('', '', 'edit', 'edit');
static $__acdesc_list = array('', '', 'web_disk', 'edit');


function createExtraVariables()
{
	$this->__var_system_username = $this->getParentO()->username;

	$sq = new Sqlite(null, 'davuser');
	$list = $sq->getRowsWhere("parent_clname = '$this->parent_clname'", array("username", "realpass"));
	$this->__var_davuser = $list;
	$sq = new Sqlite(null, 'web');
	$list = $sq->getRowsWhere("syncserver = '{$this->getParentO()->syncserver}'", array('nname'));
	$this->__var_domlist = get_namelist_from_arraylist($list);
}



static function add($parent, $class, $param)
{
	$web = $parent;
	$web->setUpdateSubaction('create_config');
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);
	$param['syncserver'] = $web->syncserver;
	$param['directory'] = trim($param['directory'], "/ ");
	$param['directory'] = "/{$param['directory']}";

	return $param;
}

static function createListNlist($parent, $view)
{
	$nlist['status'] = '3%';
	$nlist['username'] = '100%';
	$nlist['directory'] = '10%';
	return $nlist;
}

function display($var)
{
	return parent::display($var);
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['username'] = null;
	$vlist['password'] = "";
	$vlist['directory'] = array('L', '/www');


	$ret['variable'] = $vlist;
	$ret['action'] = "add";
	return $ret;

}


function getId()
{
	return strtilfirst($this->nname, "___");
}
function createShowUpdateform()
{
	$uflist['password'] = null;
	//$uflist['edit'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{
	if ($subaction === 'edit') {
		$vlist['directory'] = null;
		return $vlist;
	}
	return parent::updateform($subaction, $param);
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	//$alist['property'][] = "o=sp_specialplay&a=updateform&sa=skin";
	//$alist['property'][] = "a=updateform&sa=password";
}

function createShowAlist(&$alist, $subaction = null)
{
	return null;
	$alist['__title_main'] = $this->getTitleWithSync();
	//$this->getCPToggleUrl($alist);
	$alist[] = "a=show&l[class]=ffile&l[nname]=/";
	return $alist;

}
}
