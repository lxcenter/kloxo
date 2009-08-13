<?php 

class Dbadmin extends Lxdb {
//Core
static $__desc = array("", "",  "database_admin");

//Data
static $__desc_nname   =  Array("", "",  "");
static $__desc_syncserver   =  Array("", "",  "");
static $__desc_dbadmin_name = array("", "",  "admin_user_name", URL_SHOW);
static $__desc_dbtype = array("", "",  "database_type");
static $__desc_dbpassword = array("", "",  "admin_password");
static $__acdesc_update_update = Array("", "",  "update_dbadmin");

static $__rewrite_nname_const =    Array("dbtype", "syncserver");




function createShowUpdateform()
{
	$alist['update'] = null;
	return $alist;

}
 
static function createListNlist($parent, $view)
{
	$nlist['dbtype'] = '10%';
	$nlist['dbadmin_name'] = '100%';
	return $nlist;

}

function updateDefault($param)
{
	// Why the should i do this... The reason seems to be that these r needed for the construcation of nname in the display.php system. But that strictly shouldn't be necessary. Should look into this.
	$param['syncserver'] = $this->syncserver;
	$param['dbtype'] = $this->dbtype;
	return $param;
}

function updateform($subaction, $param)
{

	$vlist['dbtype'] = array('M', $this->dbtype);
	$vlist['dbadmin_name'] = array('M', $this->dbadmin_name);
	$vlist['dbpassword'] = null;
	return $vlist;
}

static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->nname;
	return $param;
}

function updateUpdate($param)
{
	if ($param['dbpassword'] == '') {
		throw new lxException("dbpassword_cannot_be_null", 'dbpassword');
	}
	$this->old_db_password = $this->dbpassword;
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$vlist['dbtype'] = array('s', $sgbl->__var_dblist); 
	$vlist['dbadmin_name'] = null;
	$vlist['dbpassword'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}


}



