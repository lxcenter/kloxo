<?php 


class  Process extends Lxclass {
// Core
static $__ttype = "transient";
static $__desc = array("", "",  "process");

// Data
public static $__desc_nname = array("", "",  "pid");
public static $__desc_state = array("e", "",  "state");
public static $__desc_state_v_zz = array("", "",  "sleeping");
public static $__desc_state_v_r = array("", "",  "running");
public static $__desc_state_v_t = array("", "",  "stopped");
public static $__desc_state_v_d = array("", "",  "waiting");
public static $__desc_state_v_z = array("", "",  "zombie");
public static $__desc_username = array("", "",  "user");
public static $__desc_command = array("", "",  "command");
public static $__desc_memory = array("", "",  "memory_(MB)");

static $__acdesc_update_kill = array("", "",  "kill");
static $__acdesc_update_term = array("", "",  "terminate");


function write() { }
function get()
{
}

static function createListNlist($parent, $view)
{
	$nlist["nname"] = "5%";
	$nlist["state"] = "3%";
	$nlist["memory"] = "10%";
	$nlist["username"] = "10%";
	$nlist["command"] = "100%";
	return $nlist;
}





static function perPage()
{
	return 500;
}

static function defaultSortDir() { return "desc"; }
static function defaultSort() { return "memory"; }

function isSelect()
{
	if ($this->nname === "1" || $this->nname === "0") {
		return false;
	}

	if (strpos($this->nname, "lxhttpd") !== false) {
		return false;
	}
	return true;
}

function getId()
{
	$n = trim($this->command);
	$s = explode(' ', $n);
	return $s[0];
}


static function searchVar()
{
	return "command";
}

function updateTerm($param)
{
	$this->signal = 'TERM';
	$this->setUpdateSubaction('kill');
	return null;

}


function updateKill($param)
{
	$this->signal = 'KILL';
	$this->setUpdateSubaction('kill');
	return null;

}

static function createListBlist($parent, $class)
{
	$blist[] = array("a=update&sa=kill&c=$class");
	$blist[] = array("a=update&sa=term&c=$class");
	return $blist;

}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;

}
static function initThisList($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'process');
	$res = rl_exec_get($parent->__masterserver, $parent->__readserver,  array("process__$driverapp", "readProcessList"), null);
	return $res;
}

}


