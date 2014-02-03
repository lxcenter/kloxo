<?php 

class  Diskusage extends Lxclass {

// Core
static $__ttype = "transient";
static $__desc = array("", "",  "disk_usage");

// Data
static $__desc_nname = array("", "",  "file_system");
static $__desc_kblock = array("", "",  "total");
static $__desc_used = array("", "",  "used");
static $__desc_available = array("", "",  "available");
static $__desc_mountedon = array("", "",  "mounted_on");

static $__desc_pused = array("p", "",  "usage");

function get() { }
function write() {}


function perDisplay($var)
{
	if ($var === "pused") {
		return array($this->kblock, $this->used, "MB");
	}
}

Function display($var)
{
	if (array_search_bool($var, array('used', 'available', 'kblock'))) {
		return getGBOrMB($this->$var);
	}
	return $this->$var;
}




static  function createListNlist($parent, $view)
{
	$nlist["nname"] = "100%";
	$nlist["used"] = "15%";
	$nlist["kblock"] = "15%";
	//$nlist["available"] = "15%";
	$nlist["pused"] = "15%";
	$nlist["mountedon"] = "15%";
	return $nlist;
} 

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

function isSelect()
{
	return 0;
}

static function initThisList($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$vpsid = null;
	if ($parent->is__table('vps')) {
		$vpsid = $parent->vpsid;
	}
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->syncserver, 'diskusage');
	$result = rl_exec_get($parent->__masterserver, $parent->syncserver,  array("diskusage__$driverapp", "getDiskUsage"), null);
	return $result;

}



}

