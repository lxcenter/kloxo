<?php 

class installappmisc_b extends Lxaclass {

static $__desc = array("", "",  "web");
static $__desc_title = array("", "",  "title");
static $__desc_admin_username = array("n", "",  "admin_username");
static $__desc_admin_name = array("n", "",  "admin_username");
static $__desc_adminarea = array("n", "",  "adminarea");
static $__desc_admin_password = array("n", "",  "admin_password");
static $__desc_admin_password_dbpass = array("n", "",  "admin_password");
static $__desc_admin_email = array("n", "",  "admin_email");
static $__desc_admin_email_login = array("n", "",  "admin_email_will_be_login_id");
static $__desc_admin_message = array("n", "",  "Message");
static $__desc_company_name = array("n", "",  "admin_company");
static $__desc_user_name = array("n", "",  "real_name");

}

class installapp extends Lxdb {

static $__desc  = array("","",  "installed_application"); 
static $__desc_nname  = array("","",  "Application");
static $__desc_link  = array("","Link",  "Link");
static $__desc_appname  = array("","",  "Application", "a=show");
static $__desc_ddate  = array("","",  "Date");
static $__desc_appname_f  = array("","",  "Application");

static $__desc_dbname  = array("n","",  "Database Name");
static $__desc_dbpass  = array("n","",  "database_password");
static $__desc_version  = array("n","",  "installed_version");
static $__desc_latest_f  = array("","",  "latest_version");
static $__desc_installdir  = array("","",  "location");
static $__desc_dbhost  = array("n","",  "database_host");
static $__desc_realhost  = array("","",  "real_host_name");
static $__desc_adminarea  = array("","",  "Admin");
static $__desc_dbtype  = array("","",  "database_type");
static $__desc_dbused  = array("","",  "database");
static $__acdesc_update_snapshot  = array("","",  "take_snapshot");

static $__rewrite_nname_const =    Array("appname", "parent_clname", "installdir");

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = "a=show";
	$alist['property'][] = "a=update&sa=snapshot";
	return $alist;
}

function updateSnapshot($param)
{
	$param['dval'] = 't';
	return $param;
}


function updateform($subaction, $param)
{
	switch($subaction) {
		case "update":
			$v = allinstallapp::getAllInformation($this->appname);
			$latest = $v['pversion'];
			$vlist['appname'] = array('M', null);
			$vlist['version'] = array('M', null);
			$vlist['latest_f'] = array('M', $latest);
			$vlist['__v_button'] = array();
			return $vlist;

		case "snapshot":
			$vlist['confirm_f'] = array('M', null);
			return $vlist;

	}

}

static function perPage()
{
	return 50;
}


function isSync()
{
	global $gbl, $sgbl, $login, $ghtml; 
	// Don't do anything if it is syncadd or if it is restore... When restoring, installapp is handled by the database, and then the web backup.
	if ($this->dbaction === 'syncadd') {
		return false;
	}

	if ($this->dbaction === 'syncdelete') {
		return false;
	}
	if (isset($gbl->__restore_flag) && $gbl->__restore_flag) {
		return false;
	}
	return true;
}

function display($var)
{
	$this->installdir = trim($this->installdir, "/");

	if ($var === 'installdir') {
		if (!$this->installdir) { return "Doc Root"; }
	}
	if ($var === 'link') {
		return "_lxurl:{$this->getParentName()}/{$this->installdir}:Go There:";
	}

	if ($var === 'adminarea') {
		$v = allinstallapp::getAllInformation($this->appname);

		if ($v['padminarea']) {
			$admin = $v['padminarea'];
			$url = remove_extra_slash("{$this->getParentName()}/{$this->installdir}/$admin");
			return "_lxurl:$url:Admin Area:";
		} else {
			return null;
		}
	}

	return parent::display($var);
}

function showRawPrint($subaction = null)
{
	//allinstallapp::showDescription($this->appname);
}

function createExtraVariables()
{
	$web = $this->getParentO();
	$this->__var_username = $web->username;
	if ($web->getRealClientParentO()) {
		$this->customer_name = $web->getRealClientParentO()->getPathFromName();
	}
	$this->__var_full_documentroot = $web->getFullDocRoot();

	$db = new mysqldb(null, null, $this->dbname);
	$db->get();
	$this->__var_dbuser = $db->dbname;
	$this->__var_dbpass = $db->dbpassword;

	$this->__var_snapbase = "__path_customer_root/$this->customer_name/__installappsnapshot/$web->nname";

}

static function createParentShowList($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function createAddformAlist($parent, $class, $typetd = null)
{
	dprintr($typetd);
	//$alist[] = "a=list&c=allinstallapp";
	$alist[] = "a=show&k[class]=allinstallapp&k[nname]={$typetd['val']}";
	$alist[] = "a=list&c=installapp";
	return $alist;
}

static function createListAlist($parent, $class)
{
	//$alist[] = "a=list&c=allinstallapp";
	$alist[] = "a=show&k[class]=allinstallapp&k[nname]=installapp";
	$alist[] = "a=list&c=installapp";
	$alist[] = "a=list&c=installappsnapshot";
	return $alist;
}

function createDatabase($dom, $dbhost, $dbtype, $dbname, $dbpass, $appname)
{
	$nname = $dbname;
	if (exists_in_db($dom->__masterserver, 'mysqldb', $nname)) {
		throw new lxException('database_already_exists', 'dbname', '');
	}
	$ddatabase = new Mysqldb($dom->__masterserver, $dbhost, $nname);
	$ddatabase->initThisDef();
	$res['dbname'] = $dbname;
	$res['dbtype'] = $dbtype;
	$res['username'] = $dbname;
	$res['dbpassword'] = $dbpass;
	$res['installapp_flag'] = 'on';
	$res['used_by'] = $appname;
	$res['installapp_app'] = $this->getClName();
	$res['parent_clname'] = $dom->getClientParentO()->getClName();
	$ddatabase->create($res);
	$dom->addToList('mysqldb', $ddatabase);
	// Sync the ddatbase here itself. Otherwise the app will get installed but the databse won't be created.
	$ddatabase->was();
}

function postAdd()
{

	$list = $this->getParentO()->getList('installapp');

	foreach($list as $l) {
		if ($l->nname === $this->nname) {
			continue;
		}
		if ($l->installdir === $this->installdir) {
			throw new lxException('another_appliation_exists_in_the_same_location', 'dbname', '');
		}
	}

	$list = allinstallapp::getAllInformation($this->appname);
	$this->version = $list['pversion'];

	$list = installapp::getVariablelist($this->appname);
	foreach($list as $l) {
		if (csa($l, "_static_")) {
			$var = strtil($l, "_static_");
			$val = strfrom($l, "_static_");
			$val = self::getRealMessage($val);
			$this->installappmisc_b->$var = $val;
		}
	}

	$dom = $this->getParentO()->getParentO();
	if ($this->dbname) {

		$pp = $this->getRealClientParentO();
		$this->dbhost = $pp->mysqldbsyncserver;
		if (!$this->dbhost) { $this->dbhost = 'localhost'; }


		$count = 0;

		while (true) {
			$count++;
			if ($count > 20) {
				throw new lxException('couldnt_create_database_after_20_tries', 'dbname', '');
			}

			if (!$this->dbpass) {
				$this->dbpass = substr(md5(time()), 0, 10);
			}

			try {
				$this->createDatabase($dom, $this->dbhost, $this->dbtype, $this->dbname, $this->dbpass, $this->appname);
				break;
			} catch (Exception $e) {
				$this->dbname = databasecore::getDbName($dom->nname, "$count{$this->appname}");
				$this->dbuser = $this->dbname;
			}
		}

		if (!$this->isLocalhost()) {
			$this->realhost = getRealhostName($this->dbhost);
		} else {
			$this->realhost = $this->dbhost;
		}
	}
}


static function add($parent, $class, $param)
{
	$web = $parent;
	$pname = $web->nname;
	$dom = $web->getParentO();
	$param['installdir'] = trim($param['installdir'], "/");
	//$param['installdir'] = "/{$param['installdir']}";

	if ($param['installdir']) {
		//$param['installdir'] = "/{$param['installdir']}";
	}


	/*
	if (!$param['installdir']) {
		throw new lxException('install_dir_cannot_be_null', 'installdir', '');
	}
*/

	$param['ddate'] = time();
	$dom = $parent->getParentO();
	$client = $parent->getClientParentO();

	if (isQuotaGreaterThanOrEq($client->used->mysqldb_num, $client->priv->mysqldb_num)) {
		throw new lxException('mysqldb_quota_exceeded', '', '');
	}



	if (isset($param['installappmisc_b_s_admin_email'])) {
		if (!validate_email($param['installappmisc_b_s_admin_email'])) {
			throw new lxException('email_is_not_valid', 'installappmisc_b_s_admin_email', '');
		}
	}
	
	if (isset($param['installappmisc_b_s_admin_email_login'])) {
		if (!validate_email($param['installappmisc_b_s_admin_email_login'])) {
			throw new lxException('email_is_not_valid', 'installappmisc_b_s_admin_email_login', '');
		}
	}
	
	$list = allinstallapp::getAllInformation($param['appname']);
	$var = $list['pvar'];
	if (csa($var, "__db")) {
		$param['dbname'] = databasecore::getDbName($web->nname, $param['appname']);
		$param['dbuser'] = $param['dbname'];
	}

	if (isset($param['installappmisc_b_s_admin_password_dbpass'])) {
		$param['dbpass'] = $param['installappmisc_b_s_admin_password_dbpass'];
	}


	$param['domain_name'] = $dom->nname;
	//$param['subdom'] = 'www';
	$param['dbtype'] = 'mysql';
	$param['parent_clname'] = $web->getClName();
	return $param;
}



static function getVariablelist($name)
{

	$list = allinstallapp::getAllInformation($name);

	$var = $list['pvar'];

	$var = trimSpaces($var);

	$vlist = explode(" ", $var);
	$out = null;
	foreach($vlist as $k => $v) {
		$out[$v] = $v;
	}
	return $out;

}

static function getRealMessage($val)
{
	static $array = array("password_sent_to_email" => "Password Will be Sent to Email");

	if (isset($array[$val])) {
		return $array[$val];
	} else {
		return $val;
	}

}

function postSync()
{
	if ($this->dbaction === 'add') {
		$list = installapp::getVariablelist($this->appname);
		if (!isset($list['admin_message_static_password_sent_to_email']) && $this->installappmisc_b->admin_email) {
			$string  = null;
			$string .= "Application: $this->appname\n";
			$string .= "Url: http://{$this->getParentO()->nname}/$this->installdir\n";
			$v = allinstallapp::getAllInformation($this->appname);
			if ($v['padminarea']) {
				$ar = remove_extra_slash("{$this->getParentO()->nname}/$this->installdir/{$v['padminarea']}");
				$string .= "Admin Area: http://$ar\n";
			}
			$string .= "Admin Username: {$this->installappmisc_b->admin_name}\n";
			$string .= "Admin Password: {$this->installappmisc_b->admin_password}\n";

			$subject = "Application $this->appname installed on {$this->getParentO()->nname}";
			callInBackground("lx_mail", array(null, $this->installappmisc_b->admin_email, $subject, $string));
		}
	}
}


static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$web = $parent;
	$dom = $web->getParentO();

	$contact = $dom->getParentO()->contactemail;
	if (!$contact) {
		$contact = $login->contactemail;
	}
	$infolist = allinstallapp::getAllInformation($typetd['val']);
	$version = $infolist['pversion'];
	$vlist['appname_f'] = array('M', $typetd['val']);
	$vlist['installdir'] = array('m', array("pretext" => "http://$parent->nname/"));
	$vlist['latest_f'] = array('M', $version);
	$vlist["installappmisc_b_s_admin_email"] = array('m', $contact);

	$list = self::getVariablelist($typetd['val']);

	$dbprefix = databasecore::fixDbname($web->nname);

	foreach($list as $l) {
		if ($l == '__db') {
			$vlist['__c_subtitle_db'] = "Database";
			$vlist['dbused'] = array('M', $login->getKeywordUc('db_will_be_used'));
			continue;
		}
		$vlist['__c_subtitle_misc'] = "Extra Information";
		if (csa($l, "_static_")) {
			$var = strtil($l, "_static_");
			$val = strfrom($l, "_static_");
			$val = self::getRealMessage($val);
			$vlist["installappmisc_b_s_{$var}"] = array('M', $val);
		} else {
			if ($l === 'admin_email') {
				// admin email is set anyways.
			} else if ($l === 'admin_username') {
				$vlist["installappmisc_b_s_admin_name"] = array('m', 'admin');
			} else if ($l === 'admin_password') {
				$vlist["installappmisc_b_s_{$l}"] = array('m', 'admin');
			} else {
				$vlist["installappmisc_b_s_{$l}"] = null;
			}
		}
	}

	if (isset($infolist['padminarea'])) {
		//$vlist['installappmisc_b_s_adminarea'] = array('M', $infolist['padminarea']);
	}
	
	$vlist['__v_button'] = 'Install';

	$vlist['__m_message_pre'] = "installapp_addform__pre";
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function deleteSpecific()
{
	if (!$this->dbname) {
		return;
	}
	$web = $this->getParentO();
	$dom = $web->getParentO();
	$client = $dom->getRealClientParentO();
	$sq = new Sqlite($this->__masterserver, 'mysqldb');
	$res = $sq->getRowsWhere("installapp_app = '{$this->getClName()}'");
	if (!$res) {
		return;
	}
	$dtb = $client->getFromList('mysqldb', $res[0]['nname']);
	$dtb->delete();
	$dtb->was();
}

static function defaultSortDir() { return 'desc' ; }
static function defaultSort() { return 'ddate' ; }

static function createListNlist($parent, $view)
{
	$nlist['appname'] = '100%';
	$nlist['abutton_update_s_snapshot'] = '10%';
	$nlist['version'] = '10%';
	//$nlist['subdom'] = '100%';
	if (check_if_many_server()) {
		$nlist['dbhost'] = '10%';
		$nlist['realhost'] = '10%';
	}
	$nlist['ddate'] = '10%';
	$nlist['installdir'] = '50%';
	$nlist['link'] = '50%';
	$nlist['adminarea'] = '50%';
	return $nlist;
}


}
