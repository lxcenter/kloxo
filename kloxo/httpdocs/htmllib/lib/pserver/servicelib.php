<?php 


class Service extends Lxdb {


//Core
static $__desc = array("", "",  "service");

//Data
static $__desc_nname =  array("", "",  "name");
static $__desc_servicename =  array("", "",  "name");
static $__desc_install_state =  array("e", "",  "i");
static $__desc_install_state_v_on =  array("", "",  "installed");
static $__desc_install_state_v_dull =  array("", "",  "not_installed");
static $__desc_syncserver =  array("", "",  "server_name");
static $__desc_description =  array("", "",  "description");
static $__desc_grepstring =  array("", "",  "grep:string_to_search_in_process_list");
static $__desc_boot_state = array("e", "",  "sb:boot_status", "a=update&sa=toggle_boot_state");
static $__desc_boot_state_v_on= array("", "",  "service_started_at_boot");
static $__desc_boot_state_v_off= array("", "",  "service_not_started_at_boot");
static $__desc_boot_state_v_dull = array("", "",  "service_not_installed");

static $__desc_state= array("e", "",  "state",URL_TOGGLE_STATE);
static $__desc_button_stop_f = array("b", "",  "", "a=update&sa=stop");
static $__desc_button_restart_f = array("b", "",  "", "a=update&sa=restart");
static $__desc_button_start_f = array("b", "",  "", "a=update&sa=start");

static $__desc_state_v_on= array("", "",  "running");
static $__desc_state_v_off= array("", "",  "stopped");
static $__desc_state_v_dull = array("", "",  "not_installed");

static $__acdesc_update_stop = array("", "",  "stop");
static $__acdesc_update_start = array("", "",  "start");
static $__acdesc_update_restart = array("", "",  "restart");

static $__rewrite_nname_const =    Array("servicename", "syncserver");


//Objects

//Lists

//// These functions shouldn't exist. This is very wrong.

function update($subaction, $param)
{
	$param['non_f'] = 'hello';
	return $param;
}


static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function createListNlist($parent, $view)
{
	//$nlist["install_state"] = "5%";
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'service');

	$nlist["boot_state"] = "5%";
	$nlist["state"] = "5%";
	//$nlist["nname"] = "10%";
	//$nlist["syncserver"] = "10%";
	$nlist["servicename"] = "10%";
	if ($driverapp === 'redhat') {
		$nlist["grepstring"] = "10%";
	}

	$nlist["button_start_f"] = '5%';
	$nlist["button_stop_f"] = '5%';
	$nlist["button_restart_f"] = '5%';
	$nlist["description"] = "100%";
	return $nlist;
}

function isAction($var)
{
	if ($var === 'state' || $var === 'boot_state') {
		if ($this->install_state === 'dull') {
			return false;
		}
	}
	return true;
}

static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->nname;
	$param['boot_state'] = 'on';

	$param['state'] = 'off';
	return $param;
}


static function addform($parent, $class, $typetd = null)
{
 
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'service');
	$list = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("service__$driverapp", 'getServiceList'), null);

	if (!$list) {
		$list = array('httpd', 'lxmail');
	}
	$vlist['servicename'] = array('s', $list);
	$vlist['description'] = array('m', null);
	if ($driverapp === 'redhat') {
		$vlist['grepstring'] = array('m', null);
	}
	$ret['variable'] = $vlist;
	$ret['action'] = "add";
	return $ret;
}




static function searchVar()
{
	return "description";
}


function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($var === 'state' || $var === 'boot_state') {
		if ($this->install_state === 'dull') {
			return 'dull';
		}
	}
	return parent::display($var);
}

function updateToggle_State($param)
{
	// This is very much needed. Since param is null, no action would be set on its own.
	$this->state = $this->isOn('state')? 'off': 'on';
	$this->setUpdateSubaction('toggle_state');
	return null;
}
function updateToggle_boot_state($param)
{
	// This is very much needed. Since param is null, no action would be set on its own.
	$this->boot_state = $this->isOn('boot_state')? 'off': 'on';
	$this->setUpdateSubaction('toggle_boot_state');
	return null;
}


static function canGetSingle()
{
	return false;
}

static function initThisListRule($parent, $class)
{
}

static function initThisList($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$sql = new Sqlite($parent->__masterserver, "service");

	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'service');
	if (!$driverapp) { return; }

	$list = $sql->getRowsWhere("parent_clname = '{$parent->getClname()}'");
	foreach($list as $l) {
		$nlist[$l['servicename']] = $l;
	}
	$res = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("service__$driverapp", 'getServiceDetails'), array($nlist));
	return $res;

}

}

