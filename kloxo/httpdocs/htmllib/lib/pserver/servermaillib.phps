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
static $__desc_graylist_flag = array("f", "",  "enable_graylisting");



function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->setDefaultValue("greet_delay", "1");

}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=updateform&sa=update';
	$alist['property'][] = 'a=updateform&sa=spamdyke';
	$alist['property'][] = "a=list&c=mail_graylist_wlist_a";
	return $alist;
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
			break;

		case "spamdyke":
			$this->setDefaultValue("greet_delay", "1");
			//$vlist['greet_delay'] = null;
			$vlist['graylist_flag'] = null;
			break;
	}


	return $vlist;
}

}
