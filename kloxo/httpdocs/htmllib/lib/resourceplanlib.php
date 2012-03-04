<?php 

class resourceplan extends resourcecore {


static $__desc = array("", "",  "resource_plan");

//Data
static $__desc_nname =  array("", "",  "internal_name", URL_SHOW);
static $__desc_realname =  array("n", "",  "resource_plan", URL_SHOW);
static $__desc_owner =  array("n", "",  "client_plan", URL_SHOW);
static $__desc_description = array("", "",  "description");
static $__desc_copy_clientname_f = array("", "",  "copy_to_this_client");
static $__desc_realname_f = array("", "",  "newname");
static $__desc_share_status = array("ef", "",  "share:share_this_plan_with_your_children");
static $__desc_share_status_v_on = array("", "",  "plan_is_shared");
static $__desc_share_status_v_off = array("", "",  "plan_is_not_shared");
static $__desc_openvzostemplate_list = array("", "",  "openvz_template_list");
static $__desc_xenostemplate_list = array("", "",  "xen_template_list");
static $__desc_account = array("", "",  "accounts_on_this_plan");
static $__acdesc_update_dnstemplatelist  =  array("","",  "dns_template_pool"); 
static $__acdesc_update_ostemplatelist  =  array("","",  "ostemplate_list"); 
static $__acdesc_update_description = array("", "",  "information");
static $__acdesc_update_changerealname = array("", "",  "change_name");
static $__acdesc_update_ipaddress  =  array("","",  "ip_pool"); 
static $__acdesc_update_pserver_s  =  array("","",  "server_pool"); 
static $__acdesc_update_account  =  array("","",  "accounts_on_plan"); 
static $__acdesc_update_copyplan  =  array("","",  "copy_plan"); 
static $__rewrite_nname_const =    Array("realname", "parent_clname");


//Lists


function createShowMainImageList()
{
	return null;
}

function update($subaction, $param)
{

	if ($this->getParentO()->getClName() != $this->parent_clname) {
		throw new lxException('template_not_owner', 'parent');
	}
	return $param;
}

function postAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$parent = $this->getParentO();
	if ($sgbl->isKloxo()) {
		$this->dnstemplate_list = domainBase::getDnsTemplateList($parent);
		$this->listpriv->ipaddress_list = $parent->getIpaddress('localhost');
	}
}


static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist['__v_dialog_add'] = "a=addform&c=$class";
	return $alist;
}

function updateDnstemplatelist($param)
{
	$param['dnstemplate_list'] = lxclass::fixListVariable($param['dnstemplate_list']);
	return $param;
}

final function updatepserver_s($param)
{

	$this->fixpserver_list($param);
	return $param;

}


final function updateIpaddress($param)
{
	$this->fixpserver_list($param);
	return $param;
}

function isSync() { return false; }

function createShowUpdateform()
{

	$uflist['limit'] = null;
	return $uflist;
}

function updateCopyPlan($param)
{
	$parent = $this->getParentO();
	$clientname = $param['copy_clientname_f'];
	if ($clientname === $parent->nname) {
		$newclient = $parent;
	} else {
		$newclient = $parent->getFromList('client', $clientname);
	}

	$newres = clone $this;
	$newres->dbaction = 'add';
	$newres->realname = $param['realname_f'];
	$newres->nname = "{$newres->realname}___{$newclient->getClName()}";
	$newres->parent_clname = $newclient->getClName();
	$newclient->addToList('resourceplan', $newres);
	return $param;
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


function postUpdate()
{
	if ($this->subaction === 'changerealname') { return; }
	if ($this->subaction === 'copyplan') { return; }

	$list = $this->getAccountList();
	$qparent = $this->getParentO();
	$this->write();

	foreach($list as $class => $l) {
		foreach($l as $k => $v) {
			$ob = new $class(null, null, $v);
			$ob->get();
			$param['newresourceplan'] = $this->nname;
			$ob->updatechange_plan($param);
			$ob->was();
		}
	}
}

function updateChangeRealName($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->__real_nname = $this->nname;
	$namereal = str_replace(" ", "_", $param['realname']);
	$namereal = strtolower($namereal);
	$nname = "{$namereal}___{$this->getParentO()->getClName()}";

	if (exists_in_db(null, 'resourceplan', $nname)) {
		throw new lxException('already_exists', 'realname');
	}
	$this->nname = $nname;
	$gbl->__this_redirect = $ghtml->getFullUrl("goback=1&a=show&l[class]=resourceplan&l[nname]=$nname");
	return $param;
	
}

function updateOstemplateList($param)
{
	
	$param['xenostemplate_list'] = lxclass::fixlistvariable($param['xenostemplate_list']);
	$param['openvzostemplate_list'] = lxclass::fixlistvariable($param['openvzostemplate_list']);
	return $param;
}


function getAccountList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$sq = new Sqlite(null, 'client');
	$res = $sq->getRowsWhere("resourceplan_used = '$this->nname'", array('nname'));
	$total['client'] = get_namelist_from_arraylist($res);

	if ($sgbl->isHyperVm()) {
		$sq = new Sqlite(null, 'vps');
		$res = $sq->getRowsWhere("resourceplan_used = '$this->nname'", array('nname'));
		$vlist = get_namelist_from_arraylist($res);
		$total['vps'] = $vlist;
	}
	return $total;
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$parent = $this->getParentO();

	switch($subaction) {

		case "copyplan":
			$clist = get_namelist_from_objectlist($parent->getList('client'));
			$clist[] = $parent->nname;
			$vlist['copy_clientname_f'] = array('s', $clist);
			$vlist['realname_f'] = array('m', $this->realname);
			return $vlist;

		case "account":
			$total = $this->getAccountList();
			$total = lx_array_merge($total);
			$vlist['account'] = array('M', implode(" ", $total));
			$vlist['__v_button'] = array();
			return $vlist;


		case "limit_s":
		case "limit":
			$vlist = getQuotaListForClass('client');
			$vlist['__m_message_pre'] = "resourceplan_change_pre";
			// This is patently wrong. In update, the object is inititialized properly and we are suppsed to get the quota for the specific type of object and not for the class.... Changing it to $this.
			$sgbl->method = 'post';
			return $vlist;

		case "dnstemplatelist":
			$parent = $this->getParentO();
			$nlist = domainBase::getDnsTemplateList($parent);
			$vlist['dnstemplate_list'] = array('U', $nlist);
			return $vlist;


		case "pserver_s":
			$parent = $this->getParentO();
			$list = null;
			$serverlist = client::getPserverListPriv();
			if ($this->isLogin() || !$this->isRightParent()) {
				foreach($serverlist as $s) {
					$slist = "{$s}_list";
					$vlist["{$s}_list"] = array('M', $this->listpriv->$slist);
				}

				$vlist['__v_button'] = array();
				//$vlist['dbtype_list'] = array('M', $this->listpriv->dbtype_list);
				return $vlist;
			} else {
				$vlist['server_detail_f'] = null;

				foreach($serverlist as $s) {
					$slist = "{$s}_list";
					$vlist["{$s}_list"] = null;
					if ($parent->isAdmin()) {
						$plist = $parent->getServerList(strtilfirst($s, "pserver"));
					} else {
						$plist = $parent->listpriv->$slist;
					}

					if ($parent->isAdmin()) {
						unset($parent->listpriv->$slist);
					}
					$list = lx_array_merge(array($list, $plist));
				}
				$vlist['server_detail_f'] = array('M', pservercore::createServerInfo($list));
				//$vlist['dbtype_list'] = null;
				return $vlist;
			}

		case "ipaddress":
			dprintr($this->listpriv->ipaddress_list);
			$parent = $this->getParentO();
			if ($this->isLogin() || !$this->isRightParent()) {
				$vlist['ipaddress_list'] = array('M', $this->getIpaddress($this->listpriv->webpserver_list));
				$vlist['__v_button'] = array();
			} else {
				if (check_if_many_server()) {
					dprintr($this->listpriv->webpserver_list);
					$iplist = $parent->getIpaddress($this->listpriv->webpserver_list);
				} else {
					$iplist = $parent->getIpaddress(array('localhost'));
				}

				dprintr($iplist);
				$vlist['ipaddress_list'] = array('Q', $iplist);
			}
			return $vlist;

		case "description":
			if ($this->islogin()) { throw new lxException('you_cannot_set_your_own_limit', ''); }
			$vlist['disable_per'] = array('s', array('off', '95', '100', '110', '120', '130'));
			if ($sgbl->isHyperVm() && $sgbl->isDebug()) {
				//$vlist['centralbackup_flag'] = null;
			}
			$vlist['description'] = null;
			//$vlist['share_status'] = null;
			if (!$this->isRightParent()) {
				$this->convertToUnmodifiable($vlist);
			}
			return $vlist;

		case "changerealname":
			$vlist['realname'] = null;
			return $vlist;

		case "ostemplatelist":
			getResourceOstemplate($vlist);
			return $vlist;

	}

	return parent::updateform($subaction, $param);

}

static function createListNlist($parent, $view)
{
	//$nlist['owner_f'] = '3%';
	//$nlist['share_status'] = '3%';
	$nlist['nname'] = '30%';
	$nlist['realname'] = '30%';
	$nlist['description'] = '100%';
	return $nlist;
}


function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['property'][] = 'a=show';
	$alist['property'][] = "a=updateForm&sa=description";
	$alist['property'][] = "a=updateForm&sa=account";

	if ($sgbl->isKloxo() && $this->getParentO()->isLte('reseller')) {
		$alist['property'][] = "a=updateform&sa=dnstemplatelist";
	}
	if ($sgbl->isHyperVm()) {
		$alist['property'][] = "a=updateForm&sa=pserver_s";
		$alist['property'][] = "a=updateForm&sa=ostemplatelist";
	}
	if ($sgbl->isKloxo() && check_if_many_server()) {
		//$alist['property'][] = "a=updateForm&sa=pserver_s";
	}
	$alist['property'][] = "a=updateForm&sa=copyplan";
	$alist['property'][] = "a=updateForm&sa=changerealname";

}

function createShowAlist(&$alist, $subaction = null)
{
	
	global $gbl, $sgbl, $login, $ghtml; 


	return $alist;


}

static function add($parent, $class, $param)
{
	$param['realname'] = fix_nname_to_be_variable_without_lowercase($param['realname']);
	ClientBase::fixpserver_list($param);
	return $param;
}


static function continueForm($parent, $class, $param, $continueaction)
{
	$param['realname'] = trim($param['realname']);
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

	$vlist['realname'] = "";
	$vlist['description'] = null;
	//$vlist['share_status'] = null;


	$qvlist = getQuotaListForClass('client', array());
	$vlist = lx_array_merge(array($vlist, $qvlist));


	$ret['variable'] = $vlist;
	$ret['action'] = "add";   

	return $ret;
}

}

