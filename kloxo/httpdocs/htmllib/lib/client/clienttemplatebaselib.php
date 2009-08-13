<?php 

class ClienttemplateBase extends ClientCore {

//Core
static $__desc = array("", "",  "client_plan");

//Data
static $__desc_nname =  array("n", "",  "client_plan", URL_SHOW);
static $__desc_description = array("", "",  "description");
static $__desc_share_status = array("ef", "",  "share:share_this_plan_with_your_children");
static $__desc_share_status_v_on = array("", "",  "plan_is_shared");
static $__desc_share_status_v_off = array("", "",  "plan_is_not_shared");


//Lists



function update($subaction, $param)
{

	if ($this->getParentO()->getClName() != $this->parent_clname) {
		throw new lxException('template_not_owner', 'parent');
	}
	return $param;
}

static function createListAlist($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$alist = null;
	if ($parent->isNotCustomer()) {
		$alist[] = "a=list&c=$class";
		$alist[] = "a=addform&c=$class";
	}
	return $alist;

}

function isSync() { return false; }

function createShowUpdateform()
{

	$uflist['pserver_s'] = null;
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

function display($var)
{

	if ($var === 'owner_f') {
		if ($this->isRightParent()) {
			return 'on';
		} else {
			return 'off';
		}
	}
	return $this->$var;

}

static function createListNlist($parent, $view)
{
	$nlist['owner_f'] = '3%';
	//$nlist['share_status'] = '3%';
	$nlist['nname'] = '30%';
	$nlist['description'] = '100%';
	return $nlist;
}


function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateForm&sa=ipaddress";
	$alist['property'][] = "a=updateForm&sa=description";
}

function createShowAlist(&$alist, $subaction = null)
{
	
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');
	if (!$this->priv->isOn('dns_manage_flag')) {
		$alist[] = "a=updateform&sa=dnstemplatelist";
	}
	$alist[] = "a=updateForm&sa=disable_per";


	return $alist;


}

static function add($parent, $class, $param)
{
	ClientBase::fixpserver_list($param);
	return $param;
}


static function continueForm($parent, $class, $param, $continueaction)
{
	$param['nname'] = trim($param['nname']);
	if ($continueaction === 'server') {
		$ret = self::continueFormlistpriv($parent, $class, $param, $continueaction);
	} else if ($continueaction === 'clientfinish') {
		$ret = client::continueFormClientFinish($parent, $class, $param, $continueaction);
	}
	return $ret;
}

function createShowImageList()
{
	return null;
	$vlist['owner_f'] = null;
	//$vlist['share_status'] = null;
	return $vlist;

}

static function addform($parent, $class, $typetd = null)
{

	$vlist['nname'] = "";
	$vlist['description'] = null;
	//$vlist['share_status'] = null;


	$qvlist = getQuotaListForClass('client', array());
	$vlist = lx_array_merge(array($vlist, $qvlist));


	$ret['variable'] = $vlist;
	$ret['action'] = "add";   

	return $ret;
}

/*
static function initThisList($parent, $class)
{
	$db = new Sqlite($parent->__masterserver, "clienttemplate");
	$result = $db->getRows('parent_name', $parent->nname);

	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'clienttemplate', $result);

	if ($parent->isAdmin()) {
		return null;
	}

	$pparent = $parent->getparentO();

	if ($pparent) {
		$list = $pparent->getList('clienttemplate');

		$list = filter_object_list($list, '$this->isOn("share_status")');

		$parent->clienttemplate_l = lx_array_merge(array($parent->clienttemplate_l, $list));
	}
	return null;
}
*/


}



