<?php 

class ExclusiveIp extends Lxclass {

static $__desc   =  Array("", "",  "exclusive_ipaddress");
static $__desc_nname   =  Array("", "",  "device_name");
static $__desc_devname    =  Array("s", "",  "device_name", URL_SHOW);
static $__desc_ipaddr  =     Array("n", "",  "ipaddress", URL_SHOW);
static $__desc_clientname  =     Array("n", "",  "client");
static $__desc_netmask  =     Array("n", "",  "netmask", URL_SHOW);
static $__desc_syncserver   =     Array("", "",  "server_name");


static $__desc_sslipaddress_o =    Array("d", "",  "");
static $__desc_domainipaddress_o =    Array("d", "",  "");


function get() {}
function write() {}


static function createListNlist($parent, $view)
{
	//$nlist["nname"] = "3%";
	$nlist["ipaddr"] = "100%";
	$nlist["syncserver"] = "10%";
	if ($parent->isAdmin()) {
		$nlist['clientname'] = '10%';
	}
	$nlist["devname"] = "30%";
	return $nlist;
}

function isSelect()
{
	return false;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=show&o=sslipaddress';
	$alist['property'][] = 'a=show&o=domainipaddress';
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	return null;
	$alist['__title_main'] = $login->getKeywordUc('actions');
	return $alist;
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{
	//$vlist['devname'] = array("M", $this->devname);
	$vlist['ipaddr'] = array('M', $this->ipaddr);
	$vlist['netmask'] = array('M', $this->netmask);
	$vlist['__v_button'] = "";
	return $vlist;
}


static function initThisList($parent, $class)
{
	
	$db = new Sqlite($parent->__masterserver, "ipaddress");

	if ($parent->isAdmin()) {
		$result = $db->getTable();
	} else {
		$result = $db->getRowsWhere("clientname = '" . $parent->nname . "'");
	}
	return $result;


}


}
