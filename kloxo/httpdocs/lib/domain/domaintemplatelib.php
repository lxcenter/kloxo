<?php 

class Domaintemplate extends DomainBase {


//Core
static $__desc = array("", "",  "domain_plan");

//Data
static $__desc_nname =  array("n", "",  "plan_name", URL_SHOW);
static $__desc_description = array("", "",  "description");
static $__desc_share_status = array("ef", "",  "share:share_this_plan_with_your_children");
static $__desc_share_status_v_on = array("", "",  "template_is_shared");
static $__desc_share_status_v_off = array("", "",  "template_is_not_shared");
static $__desc_dnspserver = array("s", "",  "dns_server");
static $__desc_ipaddress = array("s", "",  "ip_address");
static $__desc_secdnspserver = array("s", "",  "secondary_dns_server");
static $__desc_mmailpserver = array("s", "",  "mail_server");
static $__desc_webpserver = array("s", "",  "web_server");
static $__desc_dnstemplate = array("s", "",  "dns_template");
static $__desc_catchall = array("", "",  "catchall");

//Objects

//Lists
static $__desc_dnstemplate_o = array("", "",  "");

static $__acdesc_update_pserver = array("", "",  "servers");
static $__acdesc_update_ipaddress = array("", "",  "ipaddress");
static $__acdesc_update_dnstemplate = array("", "",  "edit_dnstemplate");
static $__acdesc_update_catchall = array("", "",  "set_catchall");

function display($var)
{

	if ($var === "status_client") {
		return $this->status;
	}

	if ($var === 'owner_f') {
		if ($this->isRightParent()) {
			return 'on';
		} else {
			return 'off';
		}
	}



	if ($var === "pvview") {
		return "";
	}

	return parent::display($var);

}


static function add($parent, $class, $param)
{
	Client::fixpserver_list($param);
	return $param;
}

// This is to override the continueformfinish in the domainbaselib. The continueformlistpriv will call finish, in domain it will call the complex finish, and in templates it will call the simple one.
static function continueFormFinish($parent, $class, $param, $continueaction)
{

	//$vlist['__m_message_pre'] = 'make_sure_ipaddress_template';
	$iplist = $parent->getIpaddress(array($param['listpriv_s_webpserver_sing']));
	if (!$iplist) {
		throw new lxexception('no_ip_address_matching_the_webserver', 'parent');
	}

	$vlist['ipaddress'] = array('s', $iplist);
	$res = DomainBase::getDnsTemplateList($parent);
	//$vlist['dbtype_list'] = null;
	$vlist['dnstemplate'] = array('s', $res);
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	$ret['param'] = $param;
	return $ret;
}

static function continueForm($parent, $class, $param, $continueaction)
{

	$param['nname'] = trim($param['nname']);
	if ($continueaction === 'server') {
		$ret = self::continueFormlistpriv($parent, $class, $param, $continueaction);
	} else {
		$ret = self::continueFormFinish($parent, $class,  $param, $continueaction);
	}

	return $ret;

}

static function addform($parent, $class, $typetd = null)
{

	$res = domain::getDnsTemplateList($parent);
	$vlist['nname'] = null;
	$vlist['description'] = null;

	$iplist = $parent->getIpaddress(array('localhost'));

	if (!$iplist) {
		$iplist = getAllIpaddress();
	}

	$vlist['ipaddress'] = array('s', $iplist);
	//$vlist['dbtype_list'] = null;
	$vlist['dnstemplate'] = array('s', $res);
	//$vlist['share_status'] = null;
	$vlist['__c_subtitle_quota'] = "Quota";
	$qvlist = getQuotaListForClass('domain', array());
	$vlist = lx_array_merge(array($vlist, $qvlist));
	$vlist['__c_subtitle_mail'] = "Mail";
	$vlist['catchall'] = array('s', array('--bounce--', 'postmaster', 'Delete'));
	$ret['action'] = "add";
	$ret['variable'] = $vlist;

	return $ret;
}

function isSync()
{
	return false;
}

function isSelect()
{
	if ($this->nname === '__default__') {
		return false;
	}
	return true;

}

static function createListAlist($parent, $class)
{
	if ($parent->isLogin() && !$parent->priv->isOn('domain_add_flag')) {
		return null;
	}

	return parent::createListAlist($parent, $class);


}


function createShowImageList()
{
	$vlist = ClientTemplate::createShowImageList();
	return $vlist;

}
function createShowUpdateform()
{

	$uflist = null;
	if (check_if_many_server()) {
		$uflist['pserver_s'] = null;
	}
	$uflist['limit'] = null;
	return $uflist;
}

function createShowRlist($subaction)
{
	return null;
}

function createShowPlist($subaction)
{
	return null;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = "a=show";

	$alist['property'][] = "a=updateForm&sa=description";
	$alist['property'][] = "a=updateForm&sa=catchall";
	$alist['property'][] = "a=updateForm&sa=ipaddress";
	$alist['property'][] = "a=updateForm&sa=disable_per";
	$alist['property'][] = "a=updateform&sa=dnstemplate";
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	//$alist[] = "a=updateForm&sa=pserver";
	//$alist[] = "a=updateForm&sa=limit";
	if (check_if_many_server()) {
		$alist['__title_main'] = $login->getKeywordUc('resource');
		$alist[] = "a=updateform&sa=ddatabasepserver";
		//$alist[] = "a=updateform&sa=dnstemplate";
	}

	return $alist;


}

function update($subaction, $param)
{
	if ($this->getparentO()->getClName() != $this->parent_clname) {
		throw new lxexception('template_not_owner', 'parent');
	}
	return $param;
}

static function createListNlist($parent, $view)
{
	//$nlist['owner_f'] = '3%';
	//$nlist['share_status'] = '3%';
	$nlist['nname'] = '30%';
	$nlist['description'] = '100%';
	return $nlist;
}


/*
static function initThisList($parent, $class)
{

	$db = new Sqlite($parent->__masterserver, "domaintemplate");
	$result = $db->getRows('parent_name', $parent->nname);
	//$newresult = $db->getRows('nname', "__default__");
	//$result = lx_array_merge(array($result, $newresult));

	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'domaintemplate', $result);
	if ($parent->isAdmin()) {
		return null;
	}
	$pparent = $parent->getparentO();

	if ($pparent) {
		$list = $pparent->getList('domaintemplate');

		$list = filter_object_list($list, '$this->isOn("share_status")');

		if (!$parent->domaintemplate_l) {
			$parent->domaintemplate_l = array();
		}
		if (!$list) {
			$list = array();
		}
		$new = $parent->domaintemplate_l +  $list;
	}

	return null;


}
*/




}



