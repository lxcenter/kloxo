<?php 

class LxDnsClass extends Lxaclass { }
class dns_record_a extends LxDnsClass {

static $__desc = array("", "",  "dns_record");
static $__desc_nname = array("n", "",  "dns_record");
static $__desc_param = array("n", "",  "value");
static $__desc_hostname = array("n", "",  "hostname", "a=updateform&sa=edit");
static $__desc_ttype = array("", "",  "type");
static $__desc_ttype_v_mx = array("", "",  "MX");
static $__desc_ttype_v_ns = array("", "",  "NS");
static $__desc_ttype_v_a = array("", "",  "A");
static $__desc_ttype_v_aaaa = array("", "",  "AAAA");
static $__desc_ttype_v_txt = array("", "",  "TXT");
static $__desc_ttype_v_cname = array("", "",  "CNAME");
static $__desc_ttype_v_fcname = array("", "",  "FCNAME");
static $__desc_ttype_v_srv = array("", "",  "SRV");
static $__desc_ttype_v_ddns = array("", "",  "DDNS");
static $__desc_priority = array("", "",  "priority");
static $__desc_ttl = array("", "",  "ttl"); // 2010-06-08 LN: Added TTL per record
static $__desc_user = array("n", "",  "ddns_user"); // 2010-06-18 LN: Added username for DDNS
static $__desc_pwd = array("n", "",  "ddns_pwd"); // 2010-06-18 LN: Added password for DDNS
static $__desc_offline = array("", "",  "ddns_offline"); // 2010-06-18 LN: Added offline for DDNS
static $__desc_update_from = array("", "",  "ddns_update_from"); // 2010-06-18 LN: Added offline for DDNS
static $__desc_update_timestamp = array("", "",  "ddns_update_timestamp"); // 2010-06-18 LN: Added offline for DDNS
static $__desc_update_ua = array("", "",  "ddns_update_ua"); // 2010-06-18 LN: Added offline for DDNS
static $__desc_service = array("n", "",  "srv_service"); // 2010-06-18 LN: Added service for SRV
static $__desc_proto = array("", "",  "srv_proto"); // 2010-06-18 LN: Added proto for SRV
static $__desc_weight = array("", "",  "srv_weight"); // 2010-06-18 LN: Added weight for SRV
static $__desc_port = array("n", "",  "srv_port"); // 2010-06-18 LN: Added for for SRV

function isSelect()
{
	if ($this->nname === 'a___base__') {
		return false;
	}
	if ($this->nname === 'a_mail') {
		return true;
	}
	return true;
}

/** 
* Form construction for DNS records. Extracted from addform/updateform to get the same form for add and update
* @param Dnsbase $parent Dnsbase in some form, Dns or Dnstemplate
* @param dns_record_a $rr Resource record to be displayed, or null if add
* @param string $type Type of the resource record to add/update
* @return assoc Associative array of form field descriptors for a DNS resrource record
*/ 
static function createForm($parent, $rr, $type)
{
	$vlist = array();
	if ($type === 'ns') {
		$vlist['param'] = null;
		$vlist['__m_message_pre'] = 'vv_dns_ns_message';
	} else if ($type === 'aaaa') {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  null;
		$vlist['__m_message_pre'] = 'vv_dns_aaaa_message';
	} else if ($type === 'a') {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  null;
		$vlist['__m_message_pre'] = 'vv_dns_a_message';
	} else if ($type === 'txt') {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  null;
		$vlist['__m_message_pre'] = 'vv_dns_txt_message';
	} else if ($type === 'mx') {
		if ($update)
			$vlist['priority'] = array('', array('posttext' => ''));
		else
			$vlist['priority'] = array('s', array('5', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'));
		$vlist['param'] = null;
		$vlist['__m_message_pre'] = 'vv_dns_mx_message';
	} else  if ($type === 'cname') {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  array('m', array('posttext' => ".$parent->nname."));
		$vlist['__m_message_pre'] = 'vv_dns_cname_message';
	} else  if ($type === 'fcname') {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  array('m', array('posttext' => ""));
		$vlist['__m_message_pre'] = 'vv_dns_fcname_message';
	} else if ($type === 'srv') {
		// 2010-06-24 LN: Added support for SRV-record
		$vlist['service'] = array('m', array('posttext' => '&nbsp;Service name: http, ftp, smtp, etc.')); // RFC2782: Service
		$vlist['proto'] = array('s', array('tcp', 'udp')); // RFC2782: Proto
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname.")); // RFC2782: Name
		$vlist['priority'] = array('s', array('5', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100'));
		$vlist['weight'] = array('m', array('posttext' => '&nbsp;0-65535 (Default is 0)')); // RFC2782: Weight - defaults to zero
		$vlist['param'] = array('m', array('posttext' => '&nbsp;Target Host')); // RFC2782: Target - as with MX, this may be a different domain
		$vlist['port'] = array('m', array('posttext' => '&nbsp;Target Port')); // RFC2782: Port - target port
		$vlist['__m_message_pre'] = 'vv_dns_srv_message';
	} else {
		$vlist['hostname'] = array('m', array('posttext' => ".$parent->nname."));
		$vlist['param'] =  null;
		$vlist['__m_message_pre'] = 'vv_dns_blank_message';
	}
	// TTL for all rr types
	$vlist['ttl'] =  array('m', array('posttext' => "&nbsp;Seconds")); // 2010-06-08 LN: Added TTL for all record types
	
	// 2010-06-18 LN: Added extra fields for DDNS A-record
	if ($type === 'ddns') {
		$vlist['user'] =  array('m', array('posttext' => "&nbsp;Username for dynamic updates"));
		$vlist['pwd'] = array('m', array('posttext' => ''));
		$vlist['offline'] = array('f', array('posttext' => ''));
		if ($rr) {
			$vlist['update_from'] = array('M', $rr->update_from);
			$vlist['update_timestamp'] = array('M', @date("Y-m-d H:i.s", $rr->update_timestamp));
			$vlist['update_ua'] = array('M', $rr->update_ua);
		}
		$vlist['__m_message_pre'] = 'vv_dns_ddns_message';
	}
	return $vlist;
}

function updateform($subaction, $param)
{
	// 2010-06-25 LN: Moved form creation to createForm
	return self::createForm($this->__parent_o, $this, $this->ttype, true);
}

function isAction()
{
	if ($this->ttype === 'ns') {
		return false;
	}
	return true;
}

static function createListNlist($parent, $view)
{
	$nlist['hostname'] = '10%';
	$nlist['ttype'] = '10%';
	$nlist['ttl'] = '10%'; // 2010-06-08 LN: Added TTL in overview
	$nlist['priority'] = '10%';
	$nlist['param'] = '100%';
	return $nlist;

}

static function perPage()
{
	return 6000;
}

function display($var)
{
	if (!isset($this->$var) || $this->$var === '') {
		return '-';
	}

	if ($var === 'ttype') {
		return strtoupper($this->$var);
	}
	
	if ($var === 'param') {
		if ($this->ttype === 'txt') {
			if (strlen($this->$var) > 30) {
				return substr($this->$var, 0, 30) . "...";
			}
		} else
		if ($this->ttype === 'ddns') {
			$update = @date("Y-m-d H:i.s", $this->update_timestamp);
			return "{$this->$var}&nbsp;&nbsp;&nbsp;[username={$this->user}, Last update {$update} from {$this->update_from}]";
		} else
		if ($this->ttype === 'srv') {
			return "{$this->$var}&nbsp;&nbsp;&nbsp;[service={$this->service}, Protocol={$this->proto}, Port={$this->port} Weight={$this->weight}]";
		}
	}

	return $this->$var;
}


static function add($parent, $class, $param)
{
	// 2010-06-18 LN: Changed naming of RR's to avoid overwriting and misleading names.
	// Names are now consistently meaningless on the format <type>_XXXXXX
	$param['nname'] = $param['ttype'] . '_' . rand(100000,999999);
	// Set update info for DDNS
	if ($param['ttype'] === 'ddns') {
		$param['update_from'] = "Kloxo ({$_SERVER['REMOTE_ADDR']})";
		$param['update_timestamp'] = time();
		$param['update_ua'] = $_SERVER['HTTP_USER_AGENT'];
	}

	return $param;
}

function update($subaction, $param)
{
	parent::update($subaction, $param, false);
	// Set update info for DDNS
	if ($this->ttype === 'ddns') {
		$param['update_from'] = "Kloxo ({$_SERVER['REMOTE_ADDR']})";
		$param['update_timestamp'] = time();
		$param['update_ua'] = $_SERVER['HTTP_USER_AGENT'];
	}
	return $param;
}


static function addform($parent, $class, $typetd = null)
{
	// 2010-06-25 LN: Moved form creation to createForm
	$ret['variable'] = self::createForm($parent, null, $typetd['val']);
	$ret['action'] = 'Add';
	return $ret;
}

}

class Mx_rec_a extends LxDnsclass { }
class Ns_rec_a extends Lxdnsclass { } 
class Txt_rec_a extends Lxdnsclass { }
class A_rec_a extends Lxdnsclass { } 
class Cn_rec_a extends Lxdnsclass { } 



abstract class Dnsbase  extends Lxdb {

static $__desc_ttl = array("", "",  "ttl_(seconds)");
static $__desc_syncserver = array("sd", "",  "primary_dns");
static $__desc_ns_rec_a = array("", "",  "ns_record");
static $__desc_a_rec_a = array("", "",  "a_record");
static $__desc_mx_rec_a = array("", "",  "mx_record");
static $__desc_cn_rec_a = array("", "",  "cn_record");
static $__desc_zone_type = array("", "",  "type_of_dns_zone_file");
static $__desc_nameserver_f = array("n", "",  "primary_DNS");
static $__desc_newdnstemplate_f = array("n", "",  "new_dns_template");
static $__desc_secnameserver_f = array("", "",  "secondary_DNS");
static $__desc_soanameserver = array("", "",  "SOA_nameserver");
static $__acdesc_update_parameter = array("", "",  "general_settings");
static $__acdesc_update_switchdnsserver = array("", "",  "switch_server");
static $__acdesc_update_rebuild = array("", "",  "rebuild");

// 2010-06-08 LN: New values for SOA
//-----------------------------------
static $__desc_email = array("", "",  "email");
static $__desc_refresh = array("", "",  "refresh");
static $__desc_retry = array("", "",  "retry");
static $__desc_expire = array("", "",  "expire");
static $__desc_minimum = array("", "",  "minimum");
//-----------------------------------


function createDefaultTemplate($webipaddress, $mmailipaddress = "0.0.0.0.0", $nameserver =  "defaultnameserver", $secnamserver = null)
{
	$this->ttl = "86000";
	$this->minimum = "3600";
	$this->refresh = '3600';
	$this->retry = '600';
	$this->expire = '604800';
	$this->addRec('ns', $nameserver, $nameserver);
	if ($secnamserver) {
		$this->addRec('ns', $secnamserver, $secnamserver);
	}

	// Extra dot added at the end of a_rec...
	$cpip = getOneIPForServer("localhost");
	if (!$cpip) { $cpip = $webipaddress ; }
	$this->addRec("a", "cp", $cpip);
	$this->addRec("a", "__base__", $webipaddress);
	$this->addRec("a", "ns", $webipaddress);
	$this->addRec("a", "ns1", $webipaddress);
	$this->addRec("a", "ns2", $webipaddress);
	$this->addRec("a", "mail", $mmailipaddress);
	$this->addRec("cn", "www", "__base__");
	$this->addRec("cn", "ftp", "__base__");
	$this->addRec("cn", "webmail", "mail");
	$this->addRec("cn", "lists", "mail"); 
	$this->addRec("mx", "10", "mail.$this->nname");
	return;
}

function addRec($ttype, $name, $param)
{   
	$rname = "{$ttype}_$name";
	$__temp = new dns_record_a(null, null, $rname);
	if ($ttype === 'mx') {
		$__temp->hostname = $this->nname;
		$__temp->priority = $name;
		$__temp->param = $param;
	} else {
		$__temp->param = $param;
		$__temp->hostname = $name;
		// Extra fields for DDNS A-record
		if ($ttype === 'ddns') {
			$__temp->user = null;
			$__temp->pwd = null;
			$__temp->offline = false;
		}
	}
	$__temp->ttl = null; // 2010-06-08 LN: Use SOA TTL as default

	$__temp->ttype = $ttype;

	if (!isset($this->dns_record_a)) {
		$this->dns_record_a = array();
	}

	$this->dns_record_a[$rname] = $__temp;
	$this->setUpdateSubaction('subdomain');
}

function addDomainKey($key)
{
	$this->addRec("txt", "_domainkey", "t=y; o=-; r=postmaster@{$this->nname}");
	$this->addRec("txt", "private._domainkey", "k=rsa; p=$key");
}

function RemoveDomainKey()
{
	foreach($this->dns_record_a as $k => $v) {
		if ($v->ttype === 'txt' && ($v->hostname === "_domainkey" || $v->hostname === 'private._domainkey')) {
			dprint("removing domainkey for $this->nname\n");
			unset($this->dns_record_a[$k]);
		}
	}
}

function getIpForBaseDomain()
{
	foreach($this->dns_record_a as $d) {
		if ($d->ttype === 'a' && $d->hostname === '__base__') {
			return $d->param;
		}
	}
	return '0.0.0.0';
}

function copyObject($dns)
{
	//$this->ipaddress = $dns->ipaddress;
	$this->ttl = $dns->ttl;
	
	// 2010-06-08 LN: Added new values for SOA record
	//------------------------------------------------
	$this->email = isset($dns->email) ? $dns->email : null; // Using isset to avoid notice
	$this->refresh = isset($dns->refresh) ? $dns->refresh : null;
	$this->retry = isset($dns->retry) ? $dns->retry : null;
	$this->expire = isset($dns->expire) ? $dns->expire : null;
	$this->minimum = isset($dns->minimum) ? $dns->minimum : null;
	//------------------------------------------------
	
	if ($dns->isClass('dns')) {
		$this->soanameserver = $dns->soanameserver;
	} else {
		$this->soanameserver = str_replace($dns->nname, $this->nname, $dns->soanameserver);
	}

	$this->zone_type = $dns->zone_type;
	$name = $dns->nname;

	foreach($dns->dns_record_a as $k => $o) { 
		// 2010-06-08 LN: Where's the OOP? Polymorphism? Delegation? :)
		if ($dns->isClass('dns') && $o->ttype === 'ns' ) {
			$hostname = $o->hostname;
			$param = $o->param;
			$nname = $o->nname;
		} else {
			$hostname = str_replace($dns->nname, $this->nname, $o->hostname);
			$param = str_replace($dns->nname, $this->nname, $o->param);
			$nname = str_replace($dns->nname, $this->nname, $o->nname);
		}
		$this->dns_record_a[$nname] = new dns_record_a(null, null, $nname);
		$this->dns_record_a[$nname]->hostname = $hostname;
		$this->dns_record_a[$nname]->ttype = $o->ttype;
		$this->dns_record_a[$nname]->ttl = isset($o->ttl) ? $o->ttl : null; // 2010-08-06 LN: Added TTL per record
		if (isset($o->priority)) {
			$this->dns_record_a[$nname]->priority = $o->priority;
		}
		// 2010-06-18 LN: Added fields for DDNS and SRV
		$tmp = $this->dns_record_a[$nname];
		if ($o->ttype === 'ddns') {
			$tmp->user = $o->username;
			$tmp->pwd = $o->password;
			$tmp->offline = $o->offline;
			$tmp->update_from = $o->update_from;
			$tmp->update_timestamp = $o->update_timestamp;
			$tmp->update_ua = $o->update_ua;
		}
		else
		if ($o->ttype === 'srv') {
			$tmp->service = $o->service;
			$tmp->proto = $o->proto;
			$tmp->port = $o->port;
			$tmp->weight = $o->weight;
		}
		$this->dns_record_a[$nname]->param = $param;
	}

}


function copyObjectWithSave($dnstemplate)
{
	$saved = null;
	foreach($this->dns_record_a as $k => $v) {
		if ($v->ttype === 'txt') {
			$saved[$k] = $v;
		}
	}
	$this->dns_record_a = null;
	$this->copyObject($dnstemplate);

	foreach($saved as $k => $v) {
		if (!isset($this->dns_record_a[$k])) {
			$this->dns_record_a[$k] = $v;
		}
	}
}

function updateRebuild($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$dnstemplatename = $param['newdnstemplate_f'];
	$dnstemplate = new Dnstemplate($this->__masterserver, $this->__readserver, $dnstemplatename);
	// If template get the ip from the template.
	$dnstemplate->get();
	$this->copyObjectWithSave($dnstemplate);
	$gbl->__ajax_refresh = true;
	$this->rootpassword_changed = 'on';
	return $param;
}

function postAdd()
{
	$this->createDefaultTemplate($this->webipaddress, $this->mmailipaddress, $this->nameserver_f, $this->secnameserver_f);
}

function createShowClist($subaction)
{

	$clist["dns_record_a"] = null;
	return $clist;

}
function isRightParent()
{
	return ($this->getParentO()->getClName() === $this->parent_clname) ;
}

function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($this->isRightParent()) {
		$alist['property'][] = "a=show";
		if (!cse($this->get__table(), "template") && $sgbl->isKloxo()) {
			$alist['property'][] = "a=updateform&sa=rebuild";
		}
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=ns';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=a';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=cname';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=fcname';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=mx';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=aaaa';
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=txt';
		// 2010-06-18 LN: Added support for SRV-record
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=srv'; 
		// 2010-06-18 LN: Added support for DDNS A-record
		$alist['property'][] = 'a=addform&c=dns_record_a&dta[var]=ttype&dta[val]=ddns'; 
		$alist['property'][] = 'a=updateform&sa=parameter';
	}
}

function fixParentClName()
{
	foreach($this->dns_record_a as $d) {
		if (isset($d->parent_clname)) {
			return;
		}
		$d->parent_clname = $this->getClName();
	}
	$this->setUpdateSubaction();
	$this->write();
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->fixParentClName();

	return $alist;
}


static function getIpaddressList($parent)
{
	$db = new Sqlite($parent->__masterserver, 'ipaddress');
	$res = $db->getTable(array('ipaddr'));
	$res = get_namelist_from_arraylist($res, 'ipaddr');
	return $res;
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	switch($subaction) {

		// ONly fro dnstemplate
		case "ipaddress":
			$res = Dnsbase::getIpaddressList($this);
			$vlist['ipaddress'] = array('s', $res);
			return $vlist;

		case "parameter":
			foreach($this->dns_record_a as $d) {
				if ($d->ttype === 'ns') { $nslist[] = $d->param; }
			}
			$vlist['ttl'] = null;
			$vlist['soanameserver'] = array('s', $nslist);
			
			// 2010-06-08 LN: New values for SOA
			$vlist['email'] = null;
			$vlist['refresh'] = null;
			$vlist['retry'] = null;
			$vlist['expire'] = null;
			$vlist['minimum'] = null;
			$vlist['__m_message_pre'] = 'vv_dns_soa_message';
			return $vlist;

		case "switchdnsserver":
			$vlist['syncserver'] = array('s', $login->getServerList('syncserver'));
			return $vlist;


		case "rebuild":
			$vlist['newdnstemplate_f'] = array('s', domainbase::getDnsTemplateList($login));
			$vlist['__v_updateall_button'] = array();
			$vlist['__m_message_pre'] = 'vv_dns_rebuild_message';
			return $vlist;

	}
	return parent::updateform($subaction, $param);

}

function updateSwitchDnsServer($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	// Not much checking is needed now. You just add the files. Don't delete it from the old place. After all it is just one single dns file. We will come up with a better logic later.


	$this->syncserver = $param['syncserver'];

	$domain = $this->getParentO();
	$domain->dnspserver = $this->syncserver;
	$domain->setUpdateSubaction();
	$domain->write();

	$this->dbaction = 'syncadd';
	$this->was();
	/*
	$this->dbaction = 'syncdelete';
	$this->was();
	*/
	$ghtml->print_redirect_back_success('dns_switched_successfuly', null);
	exit;

}

}
