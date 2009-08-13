<?php 

abstract class DomainBase extends DomainCore {


static $__desc_ddate	 = array("", "",  "date");



//static $__desc_dbtype_list =  array("Q", "",  "database_types");
static $__desc_webpserver_sing =  array("Q", "",  "web_server");
static $__desc_mmailpserver_sing =  array("Q", "",  "mail_server");
static $__desc_dnspserver_sing =  array("Q", "",  "dns_server");
static $__desc_secdnspserver_sing =  array("Q", "",  "secondary_dnsserver");
static $__desc_ipaddress_sing =  array("Q", "",  "ip_address");
static $__desc_dnstemplate_sing =  array("Q", "",  "dns_template");
static $__desc_previewdomain =  array("", "",  "preview_domain");


static $__desc_domain_num = array("", "",  "sd:sub_domains", URL_SHOW);
static $__desc_maindomain_num = array("", "",  "sd:sub_domains", URL_SHOW);
static $__acdesc_update_changeowner = array("", "",  "change_owner");
static $__acdesc_update_pserver_s = array("", "",  "server_info");
static $__acdesc_update_description = array("", "",  "information");
static $__acdesc_update_redirect_domain = array("", "",  "Domain Info");
static $__acdesc_update_preview_config = array("", "",  "configure_preview");


static $__desc_owner_f = array("ef", "",  "owner");
static $__desc_owner_f_v_on = array("", "",  "you_are_the_owner_of_plan");
static $__desc_owner_f_v_off = array("", "",  "you_are_the_not_owner_of_plan");





function createShowPlist($subaction)
{
	// Only for Domain, and not valid for template.
	$rlist = null;
	if (isset($this->ttype) && $this->ttype === 'forward') {
		return null;
	}
	if (!$subaction) {
		$rlist['priv'] = null;
	}
	return $rlist;

}
function createShowRlist($subaction)
{
	$rlist = null;
	// Only for Domain, and not valid for template.
	if (isset($this->ttype) && $this->ttype === 'forward') {
		return null;
	}

	if (!$subaction) {
		$rlist['priv'] = null;
	}
	return $rlist;
}



function isRightParent()
{
	return ($this->getParentO()->getClName() === $this->parent_clname) ;
}


function isSelect()
{
	global $gbl, $sgbl, $login, $ghtml;
	if ($login->isLteAdmin()) {
		return true;
	} else {
		return $login->priv->isOn('domain_add_flag');
	}
}


static function getDnsTemplateList($parent)
{
	$res = $parent->dnstemplate_list;
	dprintr($res);
	if (!$res) {
		$sq = new Sqlite(null, "dnstemplate");
		$res = $sq->getTable();
		$res = get_namelist_from_arraylist($res);
	}

	if (!$res) {
		throw new lxException("err_no_dns_template", '', '');
	}

	return $res;
}


function updateform($subaction, $param)
{

	switch($subaction) {
	
		// For template Only... For domain, the ip address is kept in web.

		case "preview_config":
			$vlist['previewdomain'] = null;
			return $vlist;


		case "fix_openbasedir":
			$vlist['remove_openbasedir'] = null;
			return $vlist;


		case "catchall":
			$name[] = "--bounce--";
			$name[] = "postmaster";
			$vlist['catchall'] = array('s', $name);
			return $vlist;


		case "redirect_domain":
			$vlist['redirect_domain'] = array('M', $this->getObject('web')->redirect_domain);
			//$vlist['web_s_syncserver'] = array('M', $this->getObject('web')->syncserver);
			//$vlist['mmail_s_syncserver'] = array('M', $this->getObject('mmail')->syncserver);
			//$vlist['dns_s_syncserver'] = array('M', $this->getObject('dns')->syncserver);

			$mmail = $this->getObject('mmail');

			if ($mmail->ttype !== 'forward') {
				$this->syncserver =  $this->getParentO()->getFromList('domain', $this->getObject('web')->redirect_domain)->getObject('mmail')->syncserver;
				$mmail->ttype = 'forward';
				$mmail->redirect_domain = $this->getObject('web')->redirect_domain;
			}

			$vlist['__v_button'] = array();
			return $vlist;


		case "ipaddress":
			if (!$this->isRightParent()) {
				$vlist['ipaddress'] = array('M', $this->ipaddress_sing);
				return $vlist;
			}


			$iplist = $this->getParentO()->getIpaddress(array('localhost'));
			if (!$iplist) {
				$iplist = getAllIpaddress();
			}
			$vlist['ipaddress'] = array('s', $iplist);
			return $vlist;

		//ONly for Template...
		case "description":
			$vlist['description'] = null;
			//$vlist['share_status'] = null;
			if (!$this->isRightParent()) {
				$this->convertToUnmodifiable($vlist);
			}
			return $vlist;

		case "dnstemplate":
			$res = DomainBase::getDnsTemplateList($this->getParentO());
			$vlist['dnstemplate'] = array('s', $res);
			return $vlist;

		case "information":
			$web = $this->getObject('web');
			$mmail = $this->getObject('mmail');
			$dns = $this->getObject('dns');

			$vlist['nname'] = array('M', $this->nname);
			$vlist['uuser_dummy'] = array('M', $web->ftpusername);
			$vlist['ddate']= array('M', @date('d-m-Y', $this->ddate));
			$vlist['parent_name_f'] = array('M', $this->getParentName());
			$webserv = " (ftp.$this->nname)";
			$mailserv = " (mail.$this->nname)";
			$vlist['web_s_syncserver'] = array('M', $webserv);
			if ($web->iisid) {
				$vlist['web_s_iisid'] = array('M', $web->iisid);
			}
			$vlist['mmail_s_syncserver'] = array('M', $mailserv);
			//$vlist['dns_s_syncserver'] = array('M', $dns->syncserver);
				//$vlist['dbtype_list'] = array('M', $this->listpriv->dbtype_list);
			//$vlist['contactemail'] = "";
			if (!$this->isLogin()) {
				$vlist['text_comment'] = null;
			}
			return $vlist;


		// Only for template...
		case "pserver_s":
			$parent = $this->getParentO();
			$vlist['webpserver_sing'] = null;
			$vlist['mmailpserver_sing'] = null;
			$vlist['dnspserver_sing'] = null;
			$vlist['secdnspserver_sing'] = array('Q', add_disabled($parent->listpriv->secdnspserver_list));
			if (!$this->isRightParent()) {
				$this->convertToUnmodifiable($vlist);
			}
			return $vlist;

	}
	return parent::updateform($subaction, $param);
}


}

