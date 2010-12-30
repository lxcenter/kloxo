<?php


/// Important: Dirprotect is a bit tricky. It on its own lacks any synctoSystem. Instead it updates the parent(web) object, and the adding to the virtualhost file is done in that file. This is done in both add(), and 'deleteSpecific'  functions.

class Diruser_a extends Lxaclass
{

static $__desc = array("", "",  "dirprotect_user");
static $__desc_nname = array("", "",  "user_name", 'a=updateform&sa=update');
static $__desc_param = array("", "",  "password");
static $__acdesc_update_update =  array("", "",  "change_password");

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = null;
	$vlist['param'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = "add";
	return $ret;

}

function updateform($subaction, $param)
{

	$vlist['nname'] = array('M', $this->nname);
	$vlist['param'] = null;
	return $vlist;
}

}


class Dirprotect extends Lxdb
{

//Core
static $__desc = array("", "",  "protected_directory");

//Data
static $__desc_nname = array("", "",  "virtual_directory");
static $__desc_path = array("", "",  "virtual_directory", URL_SHOW);
static $__desc_subweb = array("", "",  "subdomain");
static $__desc_diruser_a = array("", "",  "list_of_users");
static $__desc_parent_name = array("", "",  "domain");
static $__desc_authname = array("n", "",  "auth_name", URL_SHOW);
static $__desc_status  = array("e", "",  "s:status", URL_TOGGLE_STATUS);
static $__desc_status_v_on  = array("", "",  "enabled"); 
static $__desc_status_v_off  = array("", "",  "disabled"); 

static $__acdesc_update_add =  array("", "",  "add_protection");

function createExtraVariables()
{
	$parent = $this->getParentO();
	$this->__var_iisid = $parent->iisid;
	$this->__var_username = $parent->username;
	// If dbaction is delete then there is no parent...
	if ($this->dbaction === 'delete') {
		return;
	}
	$this->customer_name = $this->getRealClientParentO()->getPathFromName();
}


function getFileName()
{
	return str_replace("/", "_", "{$this->path}_{$this->subweb}");
}

function getId()
{
	return strfrom($this->nname, "___");
}

static function add($parent, $class, $param)
{

	$param['parent_clname'] = $parent->getClName();
	$param['syncserver'] = $parent->syncserver;
	$param['path'] = coreFfile::getRealpath($param['path']);
	$param['nname'] = $param['parent_clname'] . "___" . $param['path'];
	// Update the parent. The syncing of dirprotect is handled by the web object.
	$web = $parent;
	$web->setUpdateSubaction('add_delete_dirprotect');
	return $param;
}

function deleteSpecific()
{
	$web = $this->getParentO();
	$web->setUpdateSubaction('add_delete_dirprotect');
}

function updateAdd($param)
{
	$new = new Dirprotect($this->__readserver, $this->nname);
	$new->parent_clname = $this->getParentO()->getClName();
	$new->status = 'on';
	$new->diruser_a = null;
	$new->dbaction = 'add';
	$this->getParentO()->addToList('dirprotect', $new);
	return null;

}

static function createListNlist($parent, $view)
{
	$nlist['status'] = '4%';
	//$nlist['nname'] = '50%';
	$nlist['authname'] = '30%';
	$nlist['path'] = '100%';
	return $nlist;

}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = "a=show";
	$alist['property'][] = "a=addform&c=diruser_a";
}

function createShowAlist(&$alist, $subaction = null)
{
	return $alist;

}

static function createListAddForm($parent, $class)
{
	return true;
}

static function createListAlist($parent, $class)
{
	$alist[] = 'a=list&c=dirprotect';
	//$alist[] = '__int|a=show&l[class]=ffile&l[nname]=/|o=dirprotect&a=updateform&sa=add|';
	//$alist[] = 'a=addform&c=dirprotect';
	return $alist;

}
function createShowClist($subaction)
{
	if ($this->status != 'nonexistant') {
		$clist['diruser_a'] = null;
		return $clist;
	}
	return null;

}

function display($var)
{

	if ($var === "realpath") {
		return $this->nname;
	}
	return $this->$var;
}


static function addform($parent, $class, $typetd = null)
{

	$vlist['authname'] = null;
	//$vlist['subweb'] = array('s', $subweblist);
	$vlist['path'] = array('L', '/');
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}


}
