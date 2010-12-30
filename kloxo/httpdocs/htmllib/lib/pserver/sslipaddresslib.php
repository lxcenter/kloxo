<?php 


class SslIpaddress extends Lxdb {


static $__desc = array("", "",  "ssl_configuration");
static $__desc_nname =  array("n", "",  "client_template", URL_SHOW);
static $__desc_devname =  array("n", "",  "device_name", URL_SHOW);
static $__desc_syncserver =  array("n", "",  "server_name", URL_SHOW);
static $__desc_ipaddr =  array("", "",  "ip_address", URL_SHOW);
static $__desc_sslclient = array("", "",  "ssl_client");
static $__desc_ssldomain = array("", "",  "ssl_domain");
static $__desc_sslcert = array("", "",  "certificate");

static $__acdesc_update_update = array("", "",  "update");   
static $__desc_sslcert_l = array("d", "",  "virtual");
static $__rewrite_nname_const =    Array("devname", "syncserver");




function defaultValue($var)
{
	if ($var === 'ipaddr') {
		$db = new Sqlite($this->__masterserver, 'ipaddress');
		$res = $db->getRowsWhere("nname = '$this->nname'");
		return $res[0]['ipaddr'];
	}
	return null;
}

function updateform($subaction, $param)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'web');

	/*
	if ($this->getParentO()->is__table('ipaddress')) {
		$vlist['__m_message_pre'] = 'sslipaddress_updateform_update_pre_ipaddress';
	} else {
		$vlist['__m_message_pre'] = 'sslipaddress_updateform_update_pre_client';
	}
*/


	$clientparent = $this->getRealClientParentO();
	$list = $clientparent->getList('sslcert');

	$certlist = get_namelist_from_objectlist($list, 'nname');

	if ($certlist) {
		$vlist['sslcert'] = array('s', $certlist);
	} else {
		$vlist['sslcert'] = array('M', "Default Kloxo Certificate. Please Upload Your Own.");
		$vlist['__v_button'] = array();
	}

	return $vlist;

}
function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, $this->syncserver, 'web');
	$this->__var_webdriver = $driverapp;
}

function updateUpdate($param)
{
	// This is sort of a hack... The ssl configuration of the ipaddress has to be reflected properly in the domain too. So the web objects are initialized, the ssl parameters are changed on the fly and synced again. All this is because, apache will refuse to start if the ssl files are missing, which is fucking terrible. (later)... Apache is actually cool. The whole damn problem is with iis. Be careful about the createExtraVariables. As for now, the web object doesn't create objects from the parent domain object, and thus web object will work indepnednely without the help of the domain parent object. Thus the parent of the web can anything. But if in the at any time in the future the web object starts needing any parameter from the domain, then this will have to be rewritten. Then the domain objects have to initialized first, then the web objects are initialized, and the web can be created only UNDER the domain, and not directly under this object.

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass($this->__masterserver, $this->__readserver, 'web');

	if ($param['sslcert'] == '--Select One--') {
		throw new lxException("need_real_cert");
	}

	$this->ipaddr = $this->getParentO()->ipaddr;

	$sslcert = new SslCert(null, $this->__readserver, $param['sslcert']);

	$sslcert->get();

	if ($sslcert->dbaction === 'add') {
		throw new lxException("sslcert_does_not_exist");
	}

	$this->text_crt_content = $sslcert->text_crt_content;
	$this->text_key_content = $sslcert->text_key_content;
	$this->text_ca_content = $sslcert->text_ca_content;


	return $param;
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}



function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'goback=1&a=show';
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'goback=1&o=domainipaddress&a=show';
	if ($this->getParentO()->is__table('ipaddress') && $this->getParentO()->getParentO()->isAdmin()) {
		$alist['property'][] = 'goback=1&a=updateform&sa=exclusive';
	}
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	return null;
	return $alist;
}

}

