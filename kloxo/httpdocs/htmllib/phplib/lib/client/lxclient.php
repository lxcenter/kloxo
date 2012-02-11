<?php 



abstract class Lxclient  extends Lxdb {
	

//  Core
public $__view;

// Mysql
static $__desc_cpstatus  = array("e", "",  "CPS:cp status", "a=update&sa=toggle_cpstatus"); 
	static $__desc_cpstatus_v_off =    array("", "",  "disabled");
	static $__desc_cpstatus_v_on =    array("", "",  "enabled");
static $__desc_cttype =     array("e", "",  "t:client_type");
	static $__desc_cttype_v_mailaccount =    array("", "",  "customer");
	static $__desc_cttype_v_uuser =    array("", "",  "user");
	static $__desc_cttype_v_domain =    array("", "",  "domain");
	static $__desc_cttype_v_customer =    array("", "",  "customer");
	static $__desc_cttype_v_reseller =    array("", "",  "reseller");
	static $__desc_cttype_v_wholesale =    array("", "",  "wholesale_reseller_");
	static $__desc_cttype_v_master =    array("", "",  "master_reseller_");
	static $__desc_cttype_v_admin =    array("", "",  "admin");
	static $__desc_cttype_v_superadmin =    array("", "",  "superadmin");
static $__desc_password	   = array("n", "",  "password");
static $__desc_newresourceplan	   = array("", "",  "new_plan");
static $__desc_template_used	   = array("", "",  "new_plan");
static $__desc_resourceplan_used	   = array("", "",  "plan_name");
static $__desc_text_comment	   = array("t", "",  "comments");
static $__desc_extra_email_f	   = array("", "",  "extra_email");
static $__desc_disable_per =  array("", "",  "disable_when_usage_reaches_(percentage)_");

static $__desc_old_password_f    = array("n","",  "old_password"); 
// Objects
static $__desc_general_o = array('', "", "Virtual", "");
static $__desc_genlist_o = array('', "", "Virtual", "");


static $__desc_realname = array("", "",  "real_name"    );
static $__desc_add_address = array("", "",  "address"    );
static $__desc_add_city = array("", "",  "city"    );
static $__desc_add_country = array("", "",  "country"   );
static $__desc_add_telephone = array("", "",  "telephone_no"   );
static $__desc_add_fax = array("", "",  "fax"   );

static $__desc_temp_f = array("", "",  ""   );


// Lists
static $__desc_ssession_l = array("", "",  "");
static $__desc_ssessionlist_l = array("", "",  "");
static $__desc_ticket_l = array("db", "",  "");
static $__desc_ssession_o = array("", "",  "");
static $__desc_smessage_l = array("db", "",  "");
static $__desc_resource_l = array("r", "",  "");
static $__desc_information_l = array("r", "",  "");
static $__desc_permission_l = array("r", "",  "");
static $__desc_ticketconfig_o = array("", "",  "");
static $__desc_actionlog_l = array("", "",  "");
static $__desc_utmp_l = array("d", "",  "");
static $__desc_allowedip_l = array("db", "",  "");
static $__desc_blockedip_l = array("db", "",  "");


static $__acdesc_update_limit = array("", "",  "limit");
static $__acdesc_update_password =  array("","",  "password"); 
static $__acdesc_update_skin =  array("","",  "appearance"); 
static $__acdesc_update_toggle_cpstatus = array("", "",  "status");
static $__acdesc_update_backup = array("", "",  "backup");
static $__acdesc_update_restore = array("", "",  "restore");
static $__acdesc_update_cpdisable = array("", "",  "cp_disable");
static $__acdesc_update_cpenable = array("", "",  "cp_enable");
static $__acdesc_update_change_plan = array("", "",  "change_plan");
static $__acdesc_update_login_options = array("", "",  "login_options");
static $__acdesc_update_demo_status = array("", "",  "demo_status");
static $__acdesc_update_disable_per = array("", "",  "disable_policy");
static $__acdesc_update_miscinfo =  array("","",  "details"); 
static $__acdesc_update_resendwelcome =  array("","",  "resend_welcome_message"); 
static $__acdesc_update_dologin =  array("","",  "login_as"); 

// Misc

// Core

function __construct($masterserver, $readserver, $nname, $view = "parent", $force = "normal")
{
	$this->__view = $view;
	$this->__force = $force;
	$nname = strtolower($nname);

	/*
	if (csa($nname, ":")) {
		throw new lxException("name_cannot_contain_colon", 'nname');
	}
*/

	parent::__construct($masterserver, $readserver, $nname);
}


function getLoginType()
{
	return $this->cttype;

}

function getServerList($class)
{
	if ($this->isAdmin()) {
		return get_namelist_from_objectlist($this->getRealPserverList($class));
	}
	$var = "{$class}pserver_list";
	
	return $this->listpriv->$var;
}


function getKeyword($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $g_language_mes;

	if (isset($g_language_mes->__keyword[$var])) {
		return $g_language_mes->__keyword[$var];
	}
	return $var;
}

function getKeywordUc($var)
{
	return ucfirst($this->getKeyword($var));
}



function createShowMainImageList()
{
	if (cse($this->get__table(), "template")) {
		return null;
	}
	$vlist['status'] = null;
	return $vlist;
}

function getTicketMessageUrl(&$alist)
{
	$gob = $this->getObject('general')->generalmisc_b;
	//$this->getListActions($alist, 'ticket'); 

	if (isset($gob->ticket_url) && $gob->ticket_url) {
		$url = $gob->ticket_url;
		$url = add_http_if_not_exist($url);
		$alist[] = create_simpleObject(array('url' => $url, 'purl' => 'a=list&c=ticket', 'target' => 'target=_blank'));
	} else {
		$alist[] = "a=list&c=ticket";
	}
	
	$alist[] = "a=list&c=smessage";
}

function getAllContactEmail()
{
	$list = $this->getList('emailalert');
	$nlist = get_namelist_from_objectlist($list, 'nname', 'emailid');
	$email = $this->contactemail;
	if ($nlist) {
		$total = implode(",", $nlist);
		$email = "$email,$total";
	}
	return $email;
}


function notifyObjects($type)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name = ucfirst($sgbl->__var_program_name);
	$parent = $this->getParentO();
	// This actually returns itself most of the times. It is confusing and should be removed. The parent is actully the first the pervious one itself in most of the cases.
	$parent = $parent->getClientParentO();

	$noto = $parent->getObject('notification');
	$message = null;
	if ($type == 'add' && $this->isOn('send_welcome_f')) {
		$message = $noto->text_newaccountmessage;
		$message = $this->replace_keywords($message, $this);
		$subject = "Your account has been Setup by {$parent->nname}";

		if ($noto->text_newsubject) {
			$subject = $noto->text_newsubject;
		}

		$this->sendNotification($noto, $this, $subject, $message, $parent);

	}

	if ($noto->notflag_b->isOn("{$type}stuff_flag")) {
		$class = $this->get__table();
		if ($type === 'delete') {
			$txt = "$class {$this->nname} has been deleted from your account $parent->nname";
		} else {
			$txt = "$class {$this->nname} has been added to your account $parent->nname";
		}
		$txt .= "\n\n\n----- Welcome Message Sent to the User $this->contactemail -----\n";
		$txt .= $message;
		log_message("Sending Notification $name Notice: Account $type $parent->nname $parent->contactemail");
		$this->sendNotification($noto, $parent, "$name Notice: Account $type", $txt);
	}
}

function sendNotification($noto, $obj, $subject, $txt, $parent = null)
{
	if (!$obj->contactemail && !isset($obj->extra_email_f)) {
		log_log("mail_send", "No contactemail for {$obj->get__table()}:$obj->nname");
		return;
	}
	log_log("mail_send", "Sending mail to object: {$obj->get__table()}:$obj->nname");
	$from = null;
	if ($parent && $parent->contactemail) {
		$from = $parent->contactemail;
	}
	if ($noto->fromaddress) {
		$from = $noto->fromaddress;
	}
	callInBackground("lx_mail", array($from, $obj->contactemail, $subject, $txt));

	if (isset($obj->extra_email_f) && $obj->extra_email_f) {
		callInBackground("lx_mail", array($from, $obj->extra_email_f, $subject, $txt));
	}
}

function updateDoLogin($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$session = initSession($this, null, $login->nname);
	$session->ssession_vars['return_url'] = "/display.php?frm_action=show&{$ghtml->get_get_from_current_post(array('frm_action', 'frm_subaction'))}";
	$session->write();
	$ghtml->print_redirect("/display.php?frm_consumedlogin=true&frm_action=show");
}

function updateResendWelcome($param)
{
	$this->realpass = $param['password'];
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);
	return $param;
}

function updateBoxpos($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	//log_log("ajax", var_export($param, true));

	$page = $param['page'];
	$page = explode(",", $page);
	foreach($page as $k => $v) {
		if (csb($v, "internal_")) {
			unset($page[$k]);
			continue;
		} else {
			$page[$k] = strfrom($v, "item_");
		}
	}
	$class = $param['title_class'];
	$boxpos = $this->boxpos["{$class}_show"];
	foreach($page as $v) {
		if (!isset($boxpos["__title_$v"])) {
			$ret["__title_$v"] = true;
		} else {
			$ret["__title_$v"] = $boxpos["__title_$v"];
		}
	}
	$this->boxpos["{$class}_show"] = $ret;
	return $param;
}

function updateBoxPosOpen($param)
{
	//log_log("ajax", var_export($param, true));
	$class = $param['title_class'];
	$title_name = $param['title_name'];
	$title_open = isOn($param['title_open'])? true: false;
	$this->boxpos["{$class}_show"]["__title_$title_name"] = $title_open;
	return $param;
}

function isDefaultSkin()
{
	global $gbl, $sgbl, $login, $ghtml; 
	return ($login->getSpecialObject('sp_specialplay')->skin_name === 'default');
}

function replace_keywords($text, $object)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$text = str_replace("%name%", "$object->nname\n" , $text);
	$text = str_replace("%clientname%",  $object->getParentName() . "\n" , $text); 
	$text = str_replace("%password%", $object->realpass, $text);

	if ($sgbl->isKloxo()) {
		$text = str_replace("%default_domain%", $object->default_domain, $text);
	}

	if (csa($text, "%ipaddress%")) {
		$db = new Sqlite($this->__masterserver, 'ipaddress');
		$iplist = $db->getRowsWhere("syncserver = 'localhost'");
		$text = str_replace("%ipaddress%", getFQDNforServer('localhost'), $text);
	}

	if (csa($text, "%masterserver%")) {
		$db = new Sqlite($this->__masterserver, 'ipaddress');
		$iplist = $db->getRowsWhere("syncserver = 'localhost'");
		$text = str_replace("%masterserver%", getFQDNforServer('localhost'), $text);
	}

	$string = null;
	foreach($this->priv as $k => $v) {
		if ($this->isQuotaVariable($k)) {
			$var = get_v_descr($this, $k);
			$var = get_form_variable_name($var);
			$string .= "$var: $v\n";
		}
	}

	$text = str_replace("%quota%", $string, $text);

	$tlist = explode("\n", $text);
	$inside = false ; $match = false;

	foreach($tlist as $tl) {
		$tl = trim($tl);
		if (csb($tl, "<%class:")) {
			$inside = true;
			$classname = strfrom($tl, "<%class:");
			$classname = strtil($classname, "%>");
			if ($classname === $this->get__table()) {
				$match = true;
			}
			continue;
		}
		if ($inside) {
			if (csb($tl, "<%/class%>")) {
				if ($match) {
					$total[] = $object->fillWelcomeMessage(implode("\n", $textinside));
				}
				$inside = false;
				$textinside = null;
			} else {
				$textinside[] = $tl;
			}
			continue;
		}
		$total[] = $tl;
	}

	return implode("\n", $total);

}



function isSlave()
{
	global $gbl, $sgbl, $login, $ghtml; 
	return $gbl->is_slave;
}

function display($var)
{
	/*
	if ($var === 'nname') {
		return ucfirst($this->nname);
	}
*/
	return parent::display($var);
}

function getSkinDir()
{
	global $gbl, $sgbl, $login, $ghtml; 
	static $dir;
	if ($dir) { return $dir ; } 
	$dir =  "/img/skin/{$sgbl->__var_program_name}/{$this->getSpecialObject('sp_specialplay')->skin_name}/{$this->getSpecialObject('sp_specialplay')->skin_color}/";
	return $dir;
}

function getLightSkinColor()
{
	return "d1dffd";
}

function getSkinColor()
{
	static $col;
	$dir = $this->getSkinDir();
	if ($col) { return $col; }
	if (lxfile_exists("__path_program_htmlbase/$dir/base_color")) {
		$col = trim(lfile_get_contents("__path_program_htmlbase/$dir/base_color"));
	} else {
		$col =  "b1c0f0";
	}

	return "$col";
}

function getCPToggleUrl(&$alist)
{

	if ($this->isLogin()) {
		return;
	}
	if (isOn($this->cpstatus)) {
		$alist[] = "a=update&sa=cpdisable";
	} else {
		$alist[] = "a=update&sa=cpenable";
	}

}
function updateCpDisable($param)
{
	$this->cpstatus = 'off';
	$this->dbaction = 'update';
	$this->subaction = 'cpstatus';
	if_demo_throw_exception();
	return null;
}

function updateSwitchLpanel($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($gbl->isOn('show_lpanel')) {
		if (!if_demo()) {
			$this->getSpecialObject('sp_specialplay')->show_lpanel = 'off';
		}
		$gbl->setSessionV('show_lpanel', 'off');
	} else {
		$this->getSpecialObject('sp_specialplay')->show_lpanel = 'on';
		$gbl->setSessionV('show_lpanel', 'on');
	}

	$special = $this->sp_specialplay;
	$special->setUpdateSubaction('switchlpanel');
	$login->was();
	save_login();
	$ghtml->print_redirect_self("/");
	return null;
}

function createSessionProperties()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$gbl->setSessionV('show_lpanel', $login->getSpecialObject('sp_specialplay')->show_lpanel);
	$gbl->setSessionV('show_help', $login->getSpecialObject('sp_specialplay')->show_help);
}

function checkTicketUnread()
{
	$sql = new Sqlite($this->__masterserver, 'ticket');
<<<<<<< HEAD
	$res = $sql->getRowsWhere("sent_to = '{$this->getClName()}' AND unread_flag = 'on'");
=======
	$res = $sql->getRowsWhere("sent_to = :clname AND unread_flag = 'on'", array(':clname' => $this->getClName()));
>>>>>>> upstream/dev
	return count($res);
}


function checkMessageUnread()
{
	$sql = new Sqlite($this->__masterserver, 'smessage');
<<<<<<< HEAD
	$res = $sql->getRowsWhere("text_sent_to_cmlist LIKE '%,{$this->getClName()},%' AND text_readby_cmlist NOT LIKE '%,{$this->getClName()},%'");
=======
	$clname = $this->getClName();
	$res = $sql->getRowsWhere("text_sent_to_cmlist LIKE CONCAT('%', :clname1, '%') AND text_readby_cmlist NOT LIKE CONCAT('%', :clname2, '%')", array(':clname1' => $clname, ':clname2' => $clname));
>>>>>>> upstream/dev
	return count($res);
}




function updateSwitchHelp($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($gbl->isOn('show_help')) {
		$this->getSpecialObject('sp_specialplay')->show_help = 'off';
		$gbl->setSessionV('show_help', 'off');
	} else {
		if (!if_demo()) {
			$this->getSpecialObject('sp_specialplay')->show_help = 'on';
		}
		$gbl->setSessionV('show_help', 'on');
	}

	$special = $this->sp_specialplay_o;
	$special->setUpdateSubaction('switchhelp');
	$login->was();
	save_login();
	$ghtml->print_redirect_left_panel("/htmllib/mibin/lpanel.php");

	return null;
}

function updateCPEnable($param)
{
	if (isset($this->status) && !$this->isOn('status')) {
		//throw new lxException('the_account_is_disabled', 'status');
	}
	$this->cpstatus = 'on';
	$this->dbaction = 'update';
	$this->subaction = 'cpstatus';
	return null;
}

function isAuxiliary()
{
	return isset($this->__auxiliary_object);
}

function getAuxiliaryId()
{
	if (isset($this->__auxiliary_object)) {
		return $this->__auxiliary_object->nname;
	}
	return null;
}

function isBlocked()
{
	$list = $this->getList('blockedip');

	if (!$list) {
		return false;
	}

	foreach($list as $l) {
		if (is_ip($l->ipaddress, $_SERVER['REMOTE_ADDR'])) {
			return true;
		}
	}
	return false;
}

function createPublicPrivate()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$data["commonName"]  = "lxlabs.com";
	$data["countryName"] =  "IN";
	$data["stateOrProvinceName"] = "in";
	$data["localityName"]  = "in";
	$data["organizationName"]  = "lx";
	$data["organizationalUnitName"]  = "soft";
	$data["emailAddress"]  = "admin@lxlabs.com";
	
	foreach($data as $key => $value) {
		$ltemp[$key]  = $value;
	}


	$privkey = openssl_pkey_new();
	openssl_pkey_export($privkey, $text_key_content);
	$csr = openssl_csr_new($ltemp, $privkey);
	openssl_csr_export($csr, $text_csr_content);
	$sscert = openssl_csr_sign($csr, null, $privkey, 365);
	openssl_x509_export($sscert, $text_crt_content);


	$this->text_private_key = $text_key_content;
	$this->text_public_key = $text_crt_content;
}

function isAllowed()
{
	$list = $this->getList('allowedip');

	if (!$list) {
		return true;
	}

	foreach($list as $l) {
		if (is_ip($l->ipaddress, $_SERVER['REMOTE_ADDR'])) {
			return true;
		}
	}
	return false;
}

function updateToggle_CPstatus($param)
{
	if ($this->isOn('cpstatus')) {
		$this->updateCpDisable($param);
	} else {
		$this->updateCPEnable($param);
	}
	return null;
}

function updateChangeNname($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$this->__real_nname = $this->nname;
	$this->nname = $param['nname'];
	$this->nname = trim($this->nname);

	If ($this->is__table('vps')) {
		if (!cse($this->nname, ".vm")) {
			$this->nname .= ".vm";
		}
		if (csa($this->nname, '-')) {
			throw new lxexception('name_cannot_contain_dash', 'nname', '');
		}

		if (csa($this->nname, ' ')) {
			throw new lxexception('name_cannot_contain_space', 'nname', '');
		}

	}

	$sq = new Sqlite(null, $this->get__table());
	$res = $sq->rawQuery("select * from {$this->get__table()} where nname = '$this->nname'");
	if ($res) {
		throw new lxException('already_exists', 'nname', $this->nname);
	}
	$gbl->__this_redirect = '/display.php?frm_action=resource';
	return $param;
}


function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;

	switch($subaction) {

		case "dialogsize":
			$vlist['dialogsize'] = null;
			return $vlist;

		case "boxposopen":
			$vlist['title_class'] = null;
			$vlist['title_open'] = null;
			$vlist['title_name'] = null;
			return $vlist;

		case "boxpos":
			$vlist['title_class'] = null;
			$vlist['title_open'] = null;
			$vlist['title_name'] = null;
			$vlist['page'] = null;
			return $vlist;
	
		case "addshortcut":
			$vlist['shortcut'] = null;
			return $vlist;

		case "resendwelcome":
			$vlist['extra_email_f'] = null;
			$vlist['password'] = null;
			$vlist['__v_updateall_button'] = array();
			return $vlist;


		case "disable_per":
			if ($this->islogin()) { throw new lxException('you_cannot_set_your_own_limit', ''); }
			$vlist['disable_per'] = array('s', array('off', '95', '100', '110', '120', '130'));
			return $vlist;

		case "miscinfo":
			$vlist['nname']= array('M', $this->nname);
			$vlist['realname']= "";
			$vlist['add_address']= "";
			$vlist['add_city']= "";
			$vlist['add_country']= "";
			$vlist['add_telephone']= "";
			$vlist['add_fax']= "";
			return $vlist;

		case "limit_s":
		case "limit":
			if ($this->islogin()) { throw new lxException('you_cannot_set_your_own_limit', ''); }
			if (cse($this->get__table(), "template")) {
				$class = strtil($this->get__table(), "template");
				$vlist = getQuotaListForClass($class);
			} else {
				$vlist = $this->getQuotaVariableList();
			}
			// This is patently wrong. In update, the object is inititialized properly and we are suppsed to get the quota for the specific type of object and not for the class.... Changing it to $this.


			$sgbl->method = 'post';
			//$vlist['__v_updateall_button'] = array();
			return $vlist;


		case "change_plan":
			$parent = $this->getParentO();
			$ttlist = $parent->getTemplateList("resourceplan", false);
			$this->newresourceplan = $this->resourceplan_used;
			if (!$ttlist) {
				$vlist['newresourceplan'] = array("M", "No Plan in Parent");
			} else {
				$vlist['newresourceplan'] = array("A", $ttlist);
			}
			//$vlist['__v_updateall_button'] = array();
			return $vlist;

		case "password":
			if ($this->isLogin() || ($this->is__table('auxiliary') && $this->getParentO()->isAuxiliary())) {
				$vlist['old_password_f'] = "";
			}
			$vlist['password'] = "";
			$vlist['__v_updateall_button'] = array();
			return $vlist;



	}

	return parent::updateform($subaction, $param);

}



function updateAddShortCut($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$news = $param['shortcut'];
	//$news = base64_decode($param['shortcut']);
	$sh = new DskShortCut_a(null, null, $news);
	$this->dskshortcut_a[$news] = $sh;
	//dprintr($this->dskshortcut_a);
	$gbl->__this_redirect = base64_decode($news);
	$gbl->setSessionV("__refresh_lpanel", true);
	$this->setUpdateSubaction('shortcut');
}

function getLastLogin(&$ilist)
{
	$sq = new Sqlite(null, 'utmp');
	$res = $sq->rawQuery("select * from utmp where parent_clname = '{$this->getClName()}' order by (logintime + 0) DESC limit 2");
	//if (!$res) { return "Not Logged"; }
	if (!isset($res[1])) { return; }
	$url = "a=list&c=utmp";
	$ilist['Last Login'] = "_lxinurl:$url:{$res[1]['ip_address']}:";
	$date = @ date('h.i,d-M-Y', $res[1]['logintime']);
	$ilist['Last Login Time'] = "_lxinurl:$url:$date:";
}

function updatechange_plan($param)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if_demo_throw_exception('changeplan');

	if ($this->isLogin()) {
		throw new lxException('cannot_change_plan', 'nname', $this->nname);
	}

	$gbl->__ajax_refresh = true;
	$tname = $param['newresourceplan'];
	$parent = $this->getParentO();
	$template = getFromAny(array($parent, $login), "resourceplan", $tname);
	if (!$template) {
		throw new lxException('cannot_find_the_resource_plan', 'nname', $this->nname);
	}
	$priv = $template->priv;
	$this->resourceplan_used = $param['newresourceplan'];
	$oldv = clone $this->priv;
	check_priv($parent, $this->get__table(), $this->priv, $priv);
	$this->distributeChildQuota($oldv);
	$this->changePlanSpecific($template);
	$this->setUpdateSubaction('change_plan');
	return null;
}


static function defaultSort() { return 'ddate' ; }
static function defaultSortDir() { return 'desc' ; }


function isDemo()
{
	return $this->getSpecialObject('sp_specialplay')->isOn('demo_status');
}


function canHaveChild()
{
	return false;
}

function getLxclientActions(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 


	if (!$this->isLogin() && !$this->isLteAdmin() && csb($this->nname, "demo_")) {
		$alist[] = "o=sp_specialplay&a=updateform&sa=demo_status";
	}
}

static function createListNlist($parent, $view)
{
	$nlist['cpstatus'] = '3%';
	$nlist['nname'] = '100%';
	return $nlist;
}


function updatePassword($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if_demo_throw_exception('lxclient');
	if ($this->isLogin() || ($this->is__table('auxiliary') && $this->getParentO()->isAuxiliary())) {
		if (!check_password($param['old_password_f'], $this->password)) {
			throw new lxException("Wrong+Password", 'old_password_f');
		}
		unset($param['old_password_f']);
	}
	$this->__old_password = $this->password;
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);
	// Hack hack... this is due the forced security password change in the admin. Most likely the referal url, to which it is redirected, is empty. So if you are changing the login password, you can anyway redirect to 'show';
	if ($this->isLogin()) {
		$gbl->__this_redirect = '/display.php?frm_action=show';
	}

	return $param;
}

function postUpdate()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($this->subaction === 'limit') {
		$this->distributeChildQuota();
	}
	if ($this->subaction === 'resendwelcome') {
		$this->send_welcome_f = 'On';
		$this->notifyObjects('add');
		$this->subaction = 'password';
	}

}




function getMenuList()
{
	$mlist[] = 'ticket';
	return $mlist;
}


function lxclientpostAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 

	// We need to inherit from a parent who is also an lxclient. The direct parent may not be an lxclient.
	$parent = $this->getParentO();
	$parent = $parent->getRealClientParentO();
	if (!$parent) { return; }
	// We need to convert childspeciaplay to special play.
	$tsc = clone $parent->getObject('sp_childspecialplay');
	$specialdisplay = new sp_specialplay(null, null, $tsc->nname);
	foreach($tsc as $k => $v) {
		$specialdisplay->$k = $v;
	}
	$this->ddate = time();
	//$specialdisplay->__table = 'sp_specialplay';
	$specialdisplay->nname = $this->getClName();
	$specialdisplay->dbaction = 'add';
	$this->addObject('sp_specialplay', $specialdisplay);
	$tsc->nname = $this->getClName();
	$tsc->dbaction = 'add';
	$this->addObject('sp_childspecialplay', $tsc);
	$notf = $parent->getObject('notification');
	$notification = clone $notf;
	$notification->dbaction = 'add';
	$notification->nname = $this->getClName();
	$notification->parent_clname = $this->getClName();
	$this->addObject('notification', $notification);
}



// This also should ideally be final, just like the lxdb:write, but I need it in superadmin.
function get()
{

	global $gbl, $sgbl, $login, $ghtml; 
	/// Removed the guest logic
	if (!$this->getThisFromDb()) {
		return 0;
	}

	if ($this->__view === "parent") {
		return 1;
	}

	if (!isset($this->cttype)) {
		$this->cttype = $this->get__table();
	}

	//$this->initListIfUndef('ssession');

	return 1;
}


}
