<?php 

class mail_graylist_wlist_a extends lxaclass {

static $__desc = array("", "",  "whitelist_ip");

//Data
static $__desc_nname  	 = array("", "",  "whitelist_ip");

static function createListAddForm($parent, $class)
{
	return true;
}

static function createListAlist($object, $class)
{
	$alist = servermail::createShowPropertyList($alist);
	return $alist['property'];
}

}

class ServerMail extends lxdb {

static $__desc = array("", "",  "server_wide_mail_configuration");
static $__desc_queuelifetime = array("", "",  "queue_life_time");
static $__desc_myname = array("", "",  "my_name");
static $__desc_queuelifetime_v_604800 = array("", "",  "queue_life_time");
static $__desc_concurrencyremote = array("", "",  "no_of_mail_send");
static $__desc_enable_maps = array("f", "",  "enable_maps_protection");
static $__desc_spamdyke_flag = array("f", "",  "enable_spamdyke");
static $__desc_domainkey_flag = array("f", "",  "enable_domainkey");
static $__desc_smtp_instance = array("", "",  "max_smtp_instances");
static $__desc_max_size = array("", "",  "max_mail_attachment_size(bytes)");
static $__desc_additional_smtp_port = array("", "",  "additional_smtp_port");
static $__desc_virus_scan_flag = array("f", "",  "enable_virus_scan");
static $__acdesc_update_update = array("", "",  "server_mail_settings");
static $__acdesc_update_spamdyke = array("", "",  "spamdyke");
static $__desc_greet_delay = array("", "",  "greet_delay");
static $__desc_max_rcpnts = array("", "",  "maximum_recipients");
static $__desc_graylist_flag = array("f", "",  "enable_graylisting");
static $__desc_graylist_min_secs = array("", "",  "graylist_min_secs");
static $__desc_graylist_max_secs = array("", "",  "graylist_max_secs");
static $__desc_reject_empty_rdns_flag = array("f", "",  "reject_empty_rdns");
static $__desc_reject_ip_in_cc_rdns_flag = array("f", "",  "reject_ip_in_cc_rdns");
static $__desc_reject_missing_sender_mx_flag = array("f", "",  "reject_missing_sender_mx");
static $__desc_reject_unresolvable_rdns_flag = array("f", "",  "reject_unresolvable_rdns");
static $__desc_dns_blacklists = array("", "",  "dns_blacklists");
static $__desc_alt_smtp_sdyke_flag = array("f","","alt_smtp_sdyke");


function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->setDefaultValue("greet_delay", "1");
	$this->setDefaultValue("max_rcpnts","30");
	$this->setDefaultValue("graylist_min_secs","300");
	$this->setDefaultValue("graylist_max_secs","1814400");
	$this->setDefaultValue("reject_empty_rdns_flag","1");
	$this->setDefaultValue("reject_ip_in_cc_rdns_flag","1");
	$this->setDefaultValue("reject_missing_sender_mx_flag","1");
	$this->setDefaultValue("reject_unresolvable_rdns_flag","1");
	$this->setDefaultValue("alt_smtp_sdyke_flag",1);

}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=updateform&sa=update';
	$alist['property'][] = 'a=updateform&sa=spamdyke';
	$alist['property'][] = "a=list&c=mail_graylist_wlist_a";
	return $alist;
}

function postUpdate($subaction = null)
{
	if ($subaction === 'update') {
		//--- for to make sure clam status -- function declare in lib.php
		// function declare on lib.php
		setFreshClam($nolog = 'yes');
	}
}

function updateform($subaction, $param)
{
	switch($subaction) {

		case "update":
			$vlist['myname'] = null;
			//$vlist['enable_maps'] = null;
			$vlist['spamdyke_flag'] = null;
			if (csa($this->getParentO()->osversion, " 5")) {
				$vlist['domainkey_flag'] = null;
				$vlist['virus_scan_flag'] = null;
				if (!$this->max_size) {
					$this->max_size = "20971520";
				}
				$vlist['max_size'] = null;
			}
			$vlist['queuelifetime'] = null;
			$vlist['smtp_instance'] = null;
			$vlist['additional_smtp_port'] = null;
			$vlist['alt_smtp_sdyke_flag'] = null;

			$this->postUpdate($subaction);

			break;

		case "spamdyke":
			$vlist['greet_delay'] = null;
			$vlist['max_rcpnts']= null;
			$vlist['graylist_flag'] = null;
			$vlist['graylist_min_secs'] = null;
			$vlist['graylist_max_secs'] = null;
			$vlist['reject_empty_rdns_flag'] = null;
			$vlist['reject_ip_in_cc_rdns_flag'] = null;
			$vlist['reject_missing_sender_mx_flag'] = null;
			$vlist['reject_unresolvable_rdns_flag'] = null;
			$vlist['dns_blacklists'] = null;
			break;
	}


	return $vlist;
}

}
