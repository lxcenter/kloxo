<?php 
class auxiliary extends Lxclient {

static $__desc = array("", "", "auxiliary_login");
static $__desc_nname = array("", "", "auxiliary_login", "a=show");
static $__desc_delete_flag = array("f", "", "delete_privilege");
static $__desc_pserver_flag = array("f", "", "show_server");
static $__acdesc_update_privilege = array("", "", "privileges");
static $__table =  'auxiliary';
function isSync() { return false; }

function createShowUpdateform()
{
	$uflist['privilege'] = null;
	$uflist['password'] = null;
	return $uflist;
}


function updateform($subaction, $param)
{
	if ($subaction === 'privilege') {
		$vlist['delete_flag'] = null;
		$vlist['pserver_flag'] = null;
		return $vlist;
	}
	return parent::updateform($subaction, $param);
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = array('m', array('posttext' => ".aux"));
	$vlist['delete_flag'] = null;
	$vlist['pserver_flag'] = null;
	$vlist['password'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=auxiliary";
	$alist['__v_dialog_add'] = "a=addform&c=auxiliary";
	if ($parent->isAdmin()) {
		$alist[] = "a=list&c=all_auxiliary";
	}
	return $alist;
}

static function add($parent, $class, $param)
{
	if (!cse($param['nname'], ".aux")) {
		$param['nname'] .= ".aux";
	}

	if (!$parent->isAdmin()) {
		$param['nname'] = "{$parent->nname}_{$param['nname']}";
	}

	$param['cpstatus'] = 'on';
	$param['status'] = 'on';
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);
	return $param;
}


static function initThisObjectRule($parent, $class, $name = null) { return null ; }
static function initThisObject($parent, $class, $name = null) 
{ 
	return $parent->__auxiliary_object;
}

}

class all_auxiliary extends auxiliary {
static $__desc = array("", "", "all_auxiliary");
static $__desc_parent_name_f = array("", "", "parent");

static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

}
