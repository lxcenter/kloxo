<?php

class Dns extends DnsBase { 

// Core
static $__desc = array("", "",  "DNS");
static $__desc_nname = array("", "",  "domain_name", "a=show");
static $__desc_webipaddress = array("", "",  "web_ipaddress");
static $__desc_mmailipaddress = array("", "",  "mail_ipaddress");
static $__acdesc_show = array("", "",  "manage_dns");
static $__acdesc_list = array("", "",  "dns");
static $__table =  'dns';


function createExtraVariablesHyperVM()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$revc = $login->getObject('general')->reversedns_b;

	$this->syncserver = implode(",", $revc->dns_slave_list);
	$this->fixDateSerial();

	$dbaddon = new Sqlite(null, "dns");
	$addr = $dbaddon->getTable(array('nname'));
	$serverlist = explode(",", $this->syncserver);
	foreach($serverlist as $server) {
		$dlistv = "__var_domainlist_$server";
		$this->$dlistv = $addr;
	}
}


function createExtraVariables()
{
	// Not here. Two different extra variables are needed, so they are created in synctosystem.
	// Brought back here, since the secondary server concept has been abolished in favor of multiple primary servers.

	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->isHyperVm()) {
		$this->createExtraVariablesHyperVM();
		return;
	}


	$db = new Sqlite($this->__masterserver, "dns");

	$gen = $login->getObject('general')->generalmisc_b;

	$serverlist = explode(",", $this->syncserver);
	$list = null;
	foreach($serverlist as $server) {
		$string = "syncserver LIKE '%$server%'";
		$nlist = $db->getRowsWhere($string, array('nname'));
		$dlistv = "__var_domainlist_$server";
		$this->$dlistv = $nlist;
	}




	//FIXME: We should only get the addon domains for the domains configured on that particular server. IN the case of single server system, it is not a problem, since that means we will have to get all the domains. but in the case of distributed setup, we need to properly get only the add domains under the domains loaded above.
	$dbaddon = new Sqlite(null, "addondomain");
	$addr = $dbaddon->getTable(array('nname'));

	foreach($serverlist as $server) {
		$dlistv = "__var_domainlist_$server";
		$this->$dlistv = lx_array_merge(array($this->$dlistv, $addr));
	}

	$this->fixDateSerial();


	$this->__var_addonlist = $this->getParentO()->getList('addondomain');


	$mydb = new Sqlite(null, "ipaddress");
	$string = "syncserver = '$this->syncserver'";
	$this->__var_ipssllist = $mydb->getRowsWhere($string, array('ipaddr', 'nname'));

}

function fixDateSerial()
{
	$ddate = @ date("Ymd");

	$this->serial = $this->serial + 1;

	if ($this->serial > 99) {
		$this->serial = 0;
	}

	$v = $this->serial;

	if ($v < 10) { $v = "0$v"; }

	$ddate = "$ddate{$v}";

	$this->__var_ddate = $ddate;


	$p = $this->getClientParentO();
	if ($p->contactemail) {
		$this->__var_email = str_replace("@", ".", $p->contactemail);
	} else {
		$this->__var_email = "admin.{$this->nname}";
	}

}

static function switchProgramPre($old, $new)
{
	if ($new === 'bind') {
		lxshell_return("service", "djbdns", "stop");
		$ret = lxshell_return("yum", "-y", "install", "bind", "bind-chroot");
		if ($ret) { throw new lxexception('install_bind_failed', 'parent'); }
		lxshell_return("chkconfig", "named", "on");
		$pattern='include "/etc/kloxo.named.conf";';
		$file = "/var/named/chroot/etc/named.conf";
		$comment = "//Kloxo";
		addLineIfNotExistInside($file, $pattern, $comment);
		touch("/var/named/chroot/etc/kloxo.named.conf");
		chown("/var/named/chroot/etc/kloxo.named.conf", "named");
		lxshell_return("rpm", "-e", "djbdns");
		lunlink("/etc/init.d/djbdns");
	} else {
		lxshell_return("yum", "-y", "install", "djbdns", "daemontools");
		if ($ret) { throw new lxexception('install_djbdns_failed', 'parent'); }
		lxshell_return("service",  "named", "stop");
		lxfile_rm_rec("/var/tinydns");
		lxfile_rm_rec("/var/axfrdns");
		lxshell_return("__path_php_path", "../bin/misc/djbdnsstart.php");
		lxshell_return("rpm", "-e", "--nodeps", "bind");
		lxshell_return("rpm", "-e", "--nodeps", "bind-chroot");
		lxfile_cp("../file/djbdns.init", "/etc/init.d/djbdns");
		lxfile_unix_chmod("/etc/init.d/djbdns", "0755");
		lxshell_return("chkconfig", "djbdns", "on");
	}

}

static function switchProgramPost($old, $new)
{
	createRestartFile($new);
}

static function removeOtherDriver($driverapp)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($driverapp === 'bind') {
		lxshell_return("service", "djbdns", "stop");
		lxshell_return("chkconfig", "named", "on");
		$pattern='include "/etc/kloxo.named.conf";';
		$file = "/var/named/chroot/etc/named.conf";
		$comment = "//Kloxo";
		addLineIfNotExistInside($file, $pattern, $comment);
		touch("/var/named/chroot/etc/kloxo.named.conf");
		chown("/var/named/chroot/etc/kloxo.named.conf", "named");
		lxshell_return("rpm", "-e", "djbdns");
		lunlink("/etc/init.d/djbdns");
	} else {
		lxshell_return("service",  "named", "stop");
		lxshell_return("rpm", "-e", "--nodeps", "bind");
		lxshell_return("rpm", "-e", "--nodeps", "bind-chroot");
		lxshell_return("chkconfig", "djbdns", "on");
	}
}

function inheritSynserverFromParent() { return false; }
function oldsyncToSystem()
{
	global $gbl, $sgbl, $login, $ghtml; 

	//Clients who do not have dns permission should be able to create domains.
	if (!$login->priv->isOn('dns_manage_flag')) {
		if ($this->dbaction === 'update') {
			throw new lxexception('no_dns_permission', 'parent');
		}
	}
}

static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$revc = $login->getObject('general')->reversedns_b;
	validate_domain_name($param['nname']);


	$param['nameserver_f'] = $revc->primarydns;
	$param['secnameserver_f'] =  $revc->secondarydns;
	return $param;
}

static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$revc = $login->getObject('general')->reversedns_b;

	$res = get_namelist_from_objectlist($parent->vmipaddress_a);
	if (!$res) {
		throw new lxexception('no_ip_address', 'parent');
	}

	$vlist['nname'] = null;
	$vlist['webipaddress'] = array('s', $res);
	$vlist['mmailipaddress'] = array('s', $res);
	$vlist['nameserver_f'] = array('M', $revc->primarydns);
	$vlist['secnameserver_f'] = array('M', $revc->secondarydns);
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}


}

class all_dns extends dns {

static $__desc =  array("n", "",  "all_dns");
static $__acdesc_list = array("", "",  "all_dns");
static $__desc_parent_name_f =  array("n", "",  "owner");
static $__desc_parent_clname =  array("n", "",  "owner");

function isSelect() { return false ; }

static function initThisListRule($parent, $class)
{
	if (!$parent->isAdmin()) {
		throw new lxexception("only_admin_can_access", '', "");
	}

	return "__v_table";
}

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=list&c=reversedns";
	if ($login->isAdmin()) {
		$alist[] = "o=general&a=updateform&sa=reversedns";
		$alist[] = "a=list&c=all_dns";
		$alist[] = "a=list&c=all_reversedns";
	}
	return $alist;
}

static function createListSlist($parent)
{
	$nlist['nname'] = null;
	$nlist['parent_clname'] = null;
	return $nlist;
}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '100%';
	$nlist['parent_name_f'] = '100%';
	return $nlist;
}

}

