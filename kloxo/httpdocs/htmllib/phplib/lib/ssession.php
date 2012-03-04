<?php


class Ssession extends Lxclass
{

static $__ttype = "transient";
static $__desc = array("", "",  "session");

//Data
static $__desc_nname = array("", "",  "session_id"); 
static $__desc_parent_name = array("", "",  "client_name"); 
static $__desc_parent_name_f = array("", "",  "client_name"); 
static $__desc_cttype =     array("e", "",  "t");
	static $__desc_cttype_v_superadmin =    array("", "",  "superadmin");
	static $__desc_cttype_v_admin =    array("", "",  "admin");
	static $__desc_cttype_v_reseller =    array("", "",  "reseller");
	static $__desc_cttype_v_customer =    array("", "",  "customer");
	static $__desc_cttype_v_mailaccount =    array("", "",  "mail_account");
	static $__desc_cttype_v_uuser =    array("", "",  "system_user");
	static $__desc_cttype_v_domain =    array("", "",  "domain");
	static $__desc_cttype_v_vps =    array("", "",  "vps");
static $__desc_ip_address = array("", "",  "ip_address");
static $__desc_current_f = array("e", "",  "cs");
static $__desc_current_f_v_on = array("", "",  "current_session");
static $__desc_current_f_v_dull = array("", "",  "");
static $__desc_logintime = array("", "",  "login_time");
static $__desc_auxiliary_id = array("", "",  "auxiliary_id");
static $__desc_consuming_parent = array("", "",  "consuming_parent");
static $__desc_last_access = array("", "",  "last_access");
static $__desc_timeout = array("", "",  "timeout");


function get()
{
	if (!lxfile_exists("__path_program_root/session/{$this->nname}")) {
		$this->dbaction = 'add';
		return;
	}

	$rmt = lfile_get_json_unserialize("__path_program_root/session/{$this->nname}");
	$this->modify($rmt, 'clean');
	$this->dbaction = 'clean';
}

function write()
{
	lxfile_mkdir("__path_program_root/session");
	if ($this->isDeleted()) {
		lunlink("__path_program_root/session/{$this->nname}");
	} else {
		foreach($this as $k => $v) {
			if (is_object($v)) { continue; }
			$array[$k] = $v;
		}
		lfile_put_json_serialize("__path_program_root/session/{$this->nname}", $array);
	}
}

static function searchVar()
{
	return "client_name";
}



function isSync() { return false; }


function isSelect()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (if_demo()) { return false; }
	if ($gbl->c_session->nname === $this->nname) {
		return false;
	}

	return true;
}


function setVars($name, $key, $value)
{
	$var = $name . "_vars";
	$this->{$var}[$key] = $value;
	$this->dbaction = "update";

}

function getId()
{
	return $this->getParentName();
}

static function defaultSortDir() { return "desc"; }

static function defaultSort() { return "logintime"; }

function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === "logintime" || $var === "timeout" || $var === 'last_access') {
		return " " . lxgettime($this->$var) . " ";
	}

	if ($var === 'current_f') {
		if ($gbl->c_session->nname === $this->nname) {
			return 'on';
		}
		return 'dull';
	}



	if ($var === 'ip_address') {
		if (if_demo()) {
			return 'Masked in Demo';
		}
	}
	return parent::display($var);

}
function hasDriverClass()
{
	return false;
}

static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$name_list["cttype"] = "3%";
	$name_list["current_f"] = "3%";
	$name_list["parent_name_f"] = "100%";
	$name_list["ip_address"] = "20%";
	$name_list["logintime"] = "20%";
	$name_list["auxiliary_id"] = "20%";
	$name_list["consuming_parent"] = "20%";
	$name_list["last_access"] = "20%";
	return $name_list;
}

static function createListAlist($object, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function initThisObjectRule($parent, $class, $name = null) { return null; }

static function initThisObject($parent, $class, $name = null)
{
	if ($name) {
		$ssession = new Ssession($parent->__masterserver, $parent->__readserver, $name);
	} else {
		$ssession = new Ssession($parent->__masterserver, $parent->__readserver, $parent->__session_id);
	}
	$ssession->get();
	return $ssession;
}




}

class SsessionList extends Ssession {

static $__table = 'ssession';


static function initThisListRule($parent, $class)
{
	return null;
}

static function canGetSingle()
{
	return false;
}

static function initThisList($parent, $class)
{
// Load entire Session


	$result = null;
	$list = lscandir_without_dot("__path_program_root/session");
	foreach($list as $l) {
		$pp = lfile_get_json_unserialize("__path_program_root/session/$l");
		if (!$pp) { lunlink("__path_program_root/session/$l"); continue; }
		if (!$parent->isAdmin()) {
			//$result = $db->getRowsWhere("parent_clname = '" . $parent->getClName() . "'");
			if ($pp['parent_clname'] !== $parent->getClName()) {
				continue;
			}
		}
		$result[] = $pp;
	}



	if ($result) {
		$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'ssessionlist', $result, true);
	}
	return null;
}

}


