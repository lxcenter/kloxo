<?php 


class Uuser extends Lxclient {

//Core
static $__desc = array("", "",  "system_user");

// Data
static $__desc_nname =  array("n", "",  "system_user", URL_SHOW);
static $__desc_parent_name =  array("n", "",  "domain_name");
static $__desc_uuser_dummy =  array("n", "",  "primary_ftp_user");
static $__desc_ttype =  array("", "",  "type");
static $__desc_ttype_v_uuser =  array("", "",  "user_name");
static $__desc_shell = array("", "",  "login_access");
static $__desc_chroot = array("e", "",  "chr");
static $__desc_status = array("e", "",  "s", URL_TOGGLE_STATUS);
static $__desc_status_v_on = array("", "",  "on");
static $__desc_status_v_off = array("", "",  "off");
static $__desc_chroot_v_on = array("", "",  "chroot_jailed");
static $__desc_chroot_v_off = array("", "",  "full_access");
static $__desc_uid = array("", "",  "uid");
static $__desc_disk_usage = array("q", "",  "disk_usage");
static $__desc_gid = array("", "",  "gid");


static $__acdesc_update = array("", "",  "ftp_user");
static $__acdesc_update_shell_access = array("", "",  "login_access");
static $__desc_dirprotect_l = array('d', '', '', '');

//Objects

//Lists

static function createListNlist($parent, $view)
{
	$nlist['cpstatus'] = '4%';
	$nlist['status'] = '4%';
	$nlist['nname'] = '100%';
	$nlist['shell'] = '4%';
	$nlist['parent_name'] = '4%';
	return $nlist;

}


function createShowRlist($subaction)
{
	return null;
	$vlist['priv'] = null;
	return $vlist;
}
static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->syncserver;
	return $param;
}




static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->syncserver, 'uuser');
	$res = rl_exec_get($parent->__masterserver, $parent->syncserver,  array("uuser__$driverapp", "getShellList"), null);
	$vlist['nname'] = array('m', null);
	$vlist['shell'] = array('s', $res);
	$vlist['password'] = array('m', null);
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}

function getSpecialParentClass()
{
	return 'web';
}


function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	switch($subaction) {
		case "shell_access":
			{
				$driverapp = $gbl->getSyncClass($this->__masterserver, $this->syncserver, 'uuser');
				$res = rl_exec_get($this->__masterserver, $this->syncserver,  array("uuser__$driverapp", "getShellList"), null);

				$vlist['nname'] = array('M', null);
				$vlist['shell'] = array('s', $res);
				return $vlist;
			}
	}
	return parent::updateform($subaction, $param);
}



/*
function getQuotadisk_usage()
{
	global $gbl, $sgbl, $login, $ghtml; 
	return $sgbl->__var_disk_usage[$this->nname];

}
*/



function getFfileFromVirtualList($name)
{
	$name = coreFfile::getRealpath($name);
	$name = '/' . $name;
	$parent_name = $this->getParentName();
	$ffile= new Ffile($this->__masterserver, $this->syncserver, "__path_httpd_root/$parent_name/$parent_name", $name, $this->nname);
	$ffile->__parent_o = $this;
	$ffile->get();
	return $ffile;
}

function getMenuList()
{
	return null;
}

function createShowUpdateform()
{
	$uflist['password'] = null;
	return $uflist;
}

function createShowAlist(&$alist, $subaction = null)
{
	if (!$this->isLogin()) {
		$alist['property'][] = "a=updateForm&sa=shell_access";
	}
	$this->driverApp->createShowAlist($alist);
	return $alist;
	$alist['__title_main'] =  $this->getTitleWithSync();
	$alist[] = "a=show&l[class]=ffile&l[nname]=/";
	$this->getToggleUrl($alist);
	//$this->getCPToggleUrl($alist);

	//$alist[] = "a=list&c=ticket";
	//$this->getListActions($alist, 'utmp');
	return $alist;

}




function getId()
{
	return $this->nname;
}

static function getUserDescription($name)
{
	return "System User for $name";
}

static function initThisObjectRule($parent, $class, $name = null)
{
	if ($parent->is__table('pserver')) {
		dprint("argh ... <br> ");
		exit;
	}

		
	if (!isset($parent->username) || !$parent->username) {
		$parent->uuser_o = null;
		return null;
	}
	$nname = $parent->username;
	return $nname;
}


// This is needed becasue the inithisobjecturle does return null in certain circumstances (parent->username is null), and then inithisobject will be called. We need to override the lxdb inithisobject here and return null too.
static function initThisObject($parent, $class, $name = null) { return null; }


static function initThisListRule($parent, $class)
{
	if (!$parent->is__table('pserver')) {
		print("attempt to hack... <br> ");
		exit;
	}

	$res[] = array("syncserver", '=',  "'$parent->nname'");

	return $res;


}

}
