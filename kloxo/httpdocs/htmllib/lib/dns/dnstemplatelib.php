<?php 
class Dnstemplate extends DnsBase { 


// Core
static $__desc = array("", "",  "DNS_template");
static $__desc_nname = array("n", "",  "DNS_template_name", URL_SHOW);
static $__desc_owner_f = array("e", "",  "owner");
static $__desc_owner_f_v_on = array("e", "",  "owner");
static $__desc_owner_f_v_off = array("e", "",  "not_owner");
static $__desc_parent_clname = array("n", "",  "real_owner", URL_SHOW);
static $__desc_used_f = array("e", "",  "used");
static $__desc_used_f_v_on = array("", "",  "used");
static $__desc_used_f_v_off = array("", "",  "not_used");
static $__desc_webipaddress = array("", "",  "web_ipaddress");
static $__desc_mmailipaddress = array("", "",  "mail_ipaddress");
static $__acdesc_update_ipaddress = array("", "",  "ipaddress");


function update($subaction, $param)
{
	if ($this->getParentO()->getClName() !== $this->parent_clname) {
		//throw new lxexception('template_not_owner', 'parent');
	}
	return $param;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist['__v_dialog_add'] = "a=addform&c=$class";
	return $alist;
}

static function createListNlist($parent, $view)
{
	//$nlist['used_f'] = '5%';
	//$nlist['owner_f'] = '5%';
	$nlist['nname'] = '100%';
	//$nlist['parent_clname'] = '10%';
	//$nlist['ipaddress'] = '10%';
	return $nlist;
}

function createUsed()
{

	if (isset($this->used_f)) {
		return $this->used_f;
	}

	$db = new Sqlite($this->__masterserver, 'domaintemplate');
	$res = $db->getRowsWhere("dnstemplate = '$this->nname'");
	if ($res) {
		$this->used_f = 'on';
	} else {
		$this->used_f = 'off';
	}
	return $this->used_f;
}

function display($var)
{

	return parent::display($var);
}

function isSelect()
{
	return true;
	$this->createUsed();
	if ($this->isOn('used_f')) {
		return false;
	} 

	return $this->isRightParent();
	return true;
}

static function add($parent, $class, $param)
{
	// issue #755 - creation of secondary mx entry at the dns template gives error
	// only alphanumeric, dot and minus accepted --> like domain name

//	if (!preg_match("/^[^\W][0-9a-zA-Z-._]+[^\W]$/", $param['nname'])) {
	if (!preg_match("/^[^\W][0-9a-zA-Z-.]+[^\W]$/", $param['nname'])) {
			throw new lxexception('invalid_char_in_template_name', 'nname');
	}

	if (strlen($param['nname']) > 60) {
		throw new lxException('template_name_over_char_limit', 'nname');
	}

	$param['nname'] = "{$param['nname']}.dnst";
	$param['shared'] = 'on';
	return $param;
}


static function addform($parent, $class, $typetd = null)
{

	$res = Dnsbase::getIpaddressList($parent);
	if (!$res) {
		lxshell_return("__path_php_path", "../bin/fixIpAddress.php");
	}
	$res = Dnsbase::getIpaddressList($parent);
	if (!$res) {
		throw new lxexception('no_ip_address', 'parent');
	}

    $vlist['__m_message_pre'] = 'vv_dns_template_message';
	$vlist['nname'] = null;
	$vlist['webipaddress'] = array('s', $res);
	$vlist['mmailipaddress'] = array('s', $res);
	$vlist['nameserver_f'] = null;
	$vlist['secnameserver_f'] = null;
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}
	

static function initThisObjectRule($parent, $class, $name = null) { return  null; }
static function initThisObject($parent, $class, $name = null)
{

	$obj = new $class($parent->__masterserver, $parent->syncserver, $name);
	$obj->get();
	return $obj;
	
}

/*
static function initThisListRule($parent, $class)
{
	return "__v_table";
}
*/

function isSync()
{
	return false;
}

/*
static function initThisList($parent, $class)
{

	$db = new Sqlite($parent->__masterserver, "dnstemplate");

	if (!$parent->priv->isOn('dns_manage_flag') && !$parent->isAdmin()) {

		$list = $parent->dnstemplate_list;

		$result = null;
		foreach((array) $list as $l) {
			$dbr = $db->getRows('nname', $l);
			$result[] = $dbr[0];
		}
		return $result;
	}

	$result = $db->getRows('parent_clname', $parent->getClName());


	//$newresult = $db->getRows('nname', "__default__");
	//$result = lx_array_merge(array($result, $newresult));

	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'dnstemplate', $result);
	if ($parent->isAdmin()) {
		return null;
	}
	$pparent = $parent->getparentO();

	if ($pparent) {
		$list = $pparent->getList('dnstemplate');

		//$list = filter_object_list($list, '$this->isOn("share_status")');

		if (!$parent->dnstemplate_l) {
			$parent->dnstemplate_l = array();
		}
		if (!$list) {
			$list = array();
		}
		$parent->dnstemplate_l = lx_array_merge(array($parent->dnstemplate_l, $list));
	}

	return null;


}
*/




}

