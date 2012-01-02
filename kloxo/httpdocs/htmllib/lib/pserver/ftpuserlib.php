<?php 


class ftpuser extends Lxclient {

static $__table =  'ftpuser';

static $__desc = array("", "",  "ftp_user");
static $__desc_nname  	 = array("n", "",  "ftp_user_name", URL_SHOW);
static $__desc_directory  	 = array("", "",  "virtual_directory", URL_SHOW);
static $__desc_status = array("e", "",  "s:status", URL_TOGGLE_STATUS);
static $__desc_status_v_on = array("", "",  "on");
static $__desc_status_v_off = array("", "",  "off");
static $__desc_ftp_disk_usage = array("", "",  "disk_quota_(MB) (blank_for_unlimited)");

static $__acdesc_update_edit = array('', '', 'edit', 'edit');


function createExtraVariables()
{


	$this->__var_username = $this->getParentO()->username;


	if ($this->getParentO()->isClass('client')) {
		$this->__var_full_directory = "__path_customer_root/{$this->getParentO()->getPathFromName()}/{$this->directory}";
	} else {
		$this->customer_name = $this->getParentO()->customer_name;
		$this->__var_full_directory = "{$this->getParentO()->getFullDocRoot()}/{$this->directory}";
	}

}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist['__v_dialog_add'] = "a=addform&c=$class";
	return $alist;
}



static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!isset($param['complete_name_f'])) {
		$param['complete_name_f'] = $parent->nname;
	}
	$param['nname'] = trim($param['nname']);
	$char = "@";
	if ($param['complete_name_f'] !== '--direct--') {
		$param['nname'] = "{$param['nname']}{$char}{$param['complete_name_f']}";
	}
	$param['nname'] = substr($param['nname'], 0, 30);
	$param['cpstatus'] = 'on';
	$web = $parent;
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);

	return $param;
}

static function createListNlist($parent, $view)
{
	$nlist['status'] = '3%';
	$nlist['nname'] = '100%';
	$nlist['directory'] = '10%';
	return $nlist;
}

static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isAdmin()) {
		$vlist['__m_message_pre'] = "ftpuser_admin";
	}
	$char = "@"; 
	$dlist = get_namelist_from_objectlist($parent->getList('domain'));
	if ($login->isAdmin()) {
		$dlist = lx_array_merge(array(array("--direct--"), $dlist));
	}
	$vv = array('var' => 'complete_name_f', 'val' => array('s', $dlist));
	$vlist['nname'] = array('m', array('posttext' => "$char", 'postvar' => $vv));
	$vlist['password'] = "";
	$vlist['directory'] = array('L', array('pretext' => "/home/{$parent->nname}/"));
	$vlist['ftp_disk_usage'] = null;

	$ret['variable'] = $vlist;
	$ret['action'] = "add";
	return $ret;

}


function createShowUpdateform()
{
	$uflist['password'] = null;
	if (!$this->isMainFtpUser()) {
		$uflist['edit'] = null;
	}
	return $uflist;
}

function isMainFtpUser()
{
	return ($this->getParentO()->isClass('domain') && $this->getParentO()->ftpusername === $this->nname);
}

function isAction($var)
{
	return !$this->isMainFtpUser();
}

function isSelect()
{
	return !$this->isMainFtpUser();
}

function updateform($subaction, $param)
{
	if ($subaction === 'edit') {
		$vlist['directory'] = null;
		$vlist['ftp_disk_usage'] = null;
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

class all_ftpuser extends ftpuser {

static $__desc = array("", "",  "all_ftpuser");
static $__desc_parent_name_f =  array("n", "",  "owner");
static $__desc_parent_clname =  array("n", "",  "owner");

function isSelect() { return false ; }
static function createListAlist($parent, $class)
{
	return all_mailaccount::createListAlist($parent, $class);
}

static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

static function createListUpdateForm($object, $class)
{
	return null;
}

static function createListSlist($parent)
{
	$nlist['nname'] = null;
	$nlist['parent_clname'] = null;
	return $nlist;
}

}


