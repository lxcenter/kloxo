<?php 

// issue #571 - add httpd-worker and httpd-event for suphp
// issue #566 - Mod_ruid2 on Kloxo
// issue #567 - httpd-itk for kloxo
// issue #575 - More readable httpd config files
// issue #597 - Use cp. to redirect :7778 or :7777
// issue #563 - Remove overlap paramaters for SuPHP
// issue #589 - Change httpd config structure
	
class web__apache extends lxDriverClass {

//######################################### SyncToSystem Starts Here

static function uninstallMe()
{
	lxshell_return("service", "httpd", "stop");
	lxshell_return("rpm", "-e", "--nodeps", "httpd");
	if (file_exists("/etc/init.d/httpd")) {
		lunlink("/etc/init.d/httpd");
	}
}

static function installMe()
{
	$ret = lxshell_return("yum", "-y", "install", "httpd", "mod_ssl");
	if ($ret) { throw new lxexception('install_httpd_failed', 'parent'); }
	lxshell_return("chkconfig", "httpd", "on");

//	addLineIfNotExistInside("/etc/httpd/conf/httpd.conf", "Include /etc/httpd/conf/kloxo/kloxo.conf", "");

	$cver = "###version0-4###";
	$fver = file_get_contents("/etc/httpd/conf.d/~lxcenter.conf");
	
	if(stristr($fver, $cver) === FALSE) {
		lxfile_cp("/usr/local/lxlabs/kloxo/file/apache/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
	}

	lxfile_cp("/usr/local/lxlabs/kloxo/file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");

	//-- old structure
	lxfile_rm("/etc/httpd/conf/kloxo");
	lxfile_rm("/home/httpd/conf");
	
	//-- new structure	
	lxfile_mkdir("/home/apache/conf");
	lxfile_mkdir("/home/apache/conf/defaults");
	lxfile_mkdir("/home/apache/conf/domains");
	lxfile_mkdir("/home/apache/conf/redirects");
	lxfile_mkdir("/home/apache/conf/webmails");
	lxfile_mkdir("/home/apache/conf/wildcards");

	//--- some vps include /etc/httpd/conf.d/swtune.conf
	system("rm -f /etc/httpd/conf.d/swtune.conf");

	// rev 527
	lxfile_cp("../file/apache/etc_init.d", "/etc/init.d/httpd");
	lxshell_return("__path_php_path", "../bin/misc/installsuphp.php");
	//lxshell_return("__path_php_path", "../bin/fix/fixfrontpage.php");

	createRestartFile("apache");
}

function updateIpConfFile()
{
/* --- no need for new structure

	global $gbl, $sgbl, $login, $ghtml; 
	$fdata = null;
	$donelist = array();
	foreach((array) $this->main->__var_domainipaddress as $ip => $dom) {
		if (!lxfile_exists("/home/httpd/$dom/conf/kloxo.$dom")) { continue; }
		if ($dom === $this->main->nname && $this->main->isDeleted()) { continue; }
		if (array_search_bool($dom, $donelist)) { continue; }
		$donelist[] = $dom;
		$fdata .= "Include {$sgbl->__path_httpd_root}/$dom/conf/kloxo.$dom\n\n";
	}

	lfile_put_contents("/etc/httpd/conf/kloxo/domainip.conf", $fdata);
--- */
}

function updateMainConfFile()
{
	global $gbl, $sgbl, $login, $ghtml; 

//	$virtual_file = "$sgbl->__path_apache_path/kloxo/virtualhost.conf";
//	$init_file = "$sgbl->__path_apache_path/kloxo/init.conf";
	$virtual_file = "/home/apache/conf/defaults/stats.conf";
	$init_file = "/home/apache/conf/defaults/init.conf";

	$vdomlist = $this->main->__var_vdomain_list; 
	$iplist = $this->main->__var_ipaddress;

	$fdata = null;
	foreach($iplist as $ipaddr){
		$ip = trim($ipaddr['ipaddr']);
		if ($ip) {
			$fdata .= "NameVirtualHost {$ip}:80\n";
			$fdata .= "NameVirtualHost {$ip}:443\n\n";
		}
	}

	lfile_put_contents($init_file, $fdata);

	/// Start again....
	$fdata = null;

	$vdomlist = merge_array_object_not_deleted($vdomlist, $this->main);

/*
	foreach((array) $vdomlist as $dom) {
		if (array_search_bool($dom['nname'], $this->main->__var_domainipaddress)) { continue; }
		if (lxfile_exists("{$sgbl->__path_httpd_root}/{$dom['nname']}/conf/kloxo.{$dom['nname']}")) {
			$fdata .= "Include {$sgbl->__path_httpd_root}/{$dom['nname']}/conf/kloxo.{$dom['nname']}\n\n";
		}
	}
*/
//	$fdata .= "### Include /home/apache/conf/domains/*.conf\n\n";

	//--- delete unlisted domains config - begin

//	rename("/home/apache/conf/webmails/~webmail.conf", "/home/apache/conf/webmails/~webmail.conf.active");

	foreach((array) $vdomlist as $dom) {
		if (lxfile_exists("/home/apache/conf/domains/{$dom['nname']}.conf")) {
		//	lxfile_mv("/home/apache/conf/domains/{$dom['nname']}.conf", "/home/apache/conf/domains/{$dom['nname']}.conf.active");
			rename("/home/apache/conf/domains/{$dom['nname']}.conf", "/home/apache/conf/domains/{$dom['nname']}.conf.active");
			rename("/home/apache/conf/redirects/{$dom['nname']}.conf", "/home/apache/conf/redirects/{$dom['nname']}.conf.active");
			rename("/home/apache/conf/wildcards/{$dom['nname']}.conf", "/home/apache/conf/wildcards/{$dom['nname']}.conf.active");
		}
	}

	system("rm -rf /home/apache/conf/domains/*.conf");
	system("rm -rf /home/apache/conf/redirects/*.conf");
	system("rm -rf /home/apache/conf/wildcards/*.conf");

	system("rename .conf.active .conf /home/apache/conf/domains/*.conf.active");
	system("rename .conf.active .conf /home/apache/conf/redirects/*.conf.active");
	system("rename .conf.active .conf /home/apache/conf/wildcards/*.conf.active");

	//--- delete unlisted domains config - end

	$fdata .= "Alias /awstatscss \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/css/\"\n";
	$fdata .= "Alias /awstatsicons \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/icon/\"\n\n";


	// Forward domains are added at the end. This makes sure that the ssl domains
	// - which would configured as virtual domains - would work fine.
/* --- no need forward for new stucture
	if (!lfile_exists("__path_apache_path/kloxo/forward/")) {
		lxfile_mkdir("__path_apache_path/kloxo/forward/");
	}

	if (!lfile_exists("__path_apache_path/kloxo/forward/forwardhost.conf")) {
		lxfile_touch("__path_apache_path/kloxo/forward/forwardhost.conf");
	}

	if (!lfile_exists("/home/apache/conf/defaults/forwardhost.conf")) {
		lxfile_touch("/home/apache/conf/defaults/forwardhost.conf");
	}
--- */

	lfile_put_contents($virtual_file, $fdata);

//	$this->updateIpConfFile();

	// override httpd.conf
	lxfile_cp("/usr/local/lxlabs/kloxo/file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");
	// delete old structure
	system("rm -rf /etc/httpd/conf/kloxo");
	system("rm -rf /httpd/httpd/conf");

	foreach((array) $vdomlist as $dom) {
		system("rm -rf {$sgbl->__path_httpd_root}/{$dom['nname']}/conf");
	}
}

function getServerIp()
{
	foreach($this->main->__var_domainipaddress as $ip => $dom) {
		if ($dom === $this->main->nname) {
			return true;
		}
	}

	return false;
}

function getSslIpList()
{
	if ($this->getServerIp()) {
		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue; }
			$list[] = $ip;
		}
		return $list;
	}

	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$list[] = $ip;
	}

	return $list;
}

function createVirtualHostiplist($port)
{
//	$string = "\\\n";
	$string = "";

	if ($this->getServerIp()) {
		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue; }
			$string .= "\t{$ip}:{$port}\\\n";
		}
		return $string;
	}
	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$string .= "\t{$ip}:{$port}\\\n";
	}

	return $string;
}

static function staticcreateVirtualHostiplist($port)
{
	$string = "";

	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$string .= "\t{$ip}:{$port}\\\n";
	}

	return $string;
}

function addSendmail()
{
	// enabled (rev 461)
//	return null;

	$sendmailstring = "php_admin_value sendmail_path \"/usr/sbin/sendmail -t -i -f emailcop@{$this->main->nname}\"\n";

	$string  = "\t<IfModule sapi_apache2.c>\n";
	$string .= "\t\t".$sendmailstring;
	$string .= "\t</IfModule>\n\n";

	$string  = "\t<IfModule mod_php5.c>\n";
	$string .= "\t\t".$sendmailstring;
	$string .= "\t</IfModule>\n\n";

	return $string;
}

function AddOpenBaseDir()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (isset($this->main->webmisc_b) && $this->main->webmisc_b->isOn('disable_openbasedir')) {
		return null;
	}

	$adminbasedir = trim($this->main->__var_extrabasedir);

	if ($adminbasedir) {
		$adminbasedir .= ":";
	}
	$corepath = "{$sgbl->__path_customer_root}/{$this->main->customer_name}/";

	$path = "{$sgbl->__path_httpd_root}/{$this->main->nname}/httpdocs:{$sgbl->__path_httpd_root}/{$this->main->nname}/{$this->main->nname}:$corepath";

	$openbasdstring = "php_admin_value open_basedir \"{$path}:{$adminbasedir}/tmp:/usr/share/pear:/var/lib/php/session/:/home/kloxo/httpd/script\"\n";

	$string = "\t<Location />\n";
	$string .= "\t\t<IfModule sapi_apache2.c>\n";
	$string .= "\t\t\t".$openbasdstring;
	$string .= "\t\t</IfModule>\n\n";
	$string .= "\t\t<IfModule mod_php5.c>\n";
	$string .= "\t\t\t".$openbasdstring;
	$string .= "\t\t</IfModule>\n";
	$string .= "\t</Location>\n\n";

	return $string;
}

function getBlockIP()
{
	$t = trimSpaces($this->main->text_blockip);
	$t = trim($t);
	if (!$t) { return; }
	$t = str_replace(".*", "", $t);
	$string = null;
	$string .= "\t<Location />\n";
	$string .= "\t\tOrder allow,deny\n";
	$string .= "\t\tdeny from $t\n";
	$string .= "\t\tallow from all\n";
	$string .= "\t</Location>\n\n";

	return $string;
}

// change name to enablePhp() like on web__lighttpdlib.php

// function disablePhp()
function enablePhp()
{
	if (!$this->main->priv->isOn('php_flag'))  {
		return  "AddType application/x-httpd-php-source .php\n";
	}

	$string = null;
	lxfile_unix_chown("/home/httpd/{$this->main->nname}", "{$this->main->username}:apache");
	lxfile_unix_chmod("/home/httpd/{$this->main->nname}", "0775");
	if (!lxfile_exists("/home/httpd/{$this->main->nname}/php.ini")) {
		// issue #650 - lxuser_cp doesn't work and change to lxfile_cp; lighttpd use lxfile_cp
	//	lxuser_cp($this->main->username, "/etc/php.ini", "/home/httpd/{$this->main->nname}/php.ini");
		lxfile_cp("/etc/php.ini", "/home/httpd/{$this->main->nname}/php.ini");	
	}
/* --- move to getSuexecString()
	$string .= "\t<IfModule mod_suphp.c>\n";
	$string .= "\t\tsuPHP_Configpath /home/httpd/{$this->main->nname}\n";
	$string .= "\t</IfModule>\n\n";
-- */
	return $string;
}

function delDomain()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	// Very important. If the nname is null, then the 'rm -rf' command will delete all the domains.
	// So please be carefule here. Must find a better way to delete stuff.
	if (!$this->main->nname) {
		return;
	}

	$this->createConffile();

	$this->updateMainConfFile();
	$this->main->deleteDir();
//	$this->updateIpConfFile();
}

function clearDomainIpAddress()
{
	$iplist = os_get_allips();
	foreach($this->main->__var_domainipaddress as $ip => $dom) {
		if (!array_search_bool($ip, $iplist)) {
			unset($this->main->__var_domainipaddress[$ip]);
		}
	}
}

function createConffile()
{
	global $gbl, $sgbl, $login, $ghtml;

	//dprintr($this->main->__old_priv);

	$web_home = $sgbl->__path_httpd_root;
	$domainname = $this->main->nname;
	$log_path = "{$web_home}/{$this->main->nname}/stats";
	$cust_log = "{$log_path}/{$this->main->nname}-custom_log";
	$err_log = "{$log_path}/{$this->main->nname}-error_log";
//	$v_file = "$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.{$this->main->nname}";
//	$v_file = "/home/apache/conf/domains/{$domainname}.conf";

	$v_file = "/home/apache/conf/wildcards/{$domainname}.conf";
	$string = "### No * (wildcards) for '{$domainname}' ###\n\n\n";
	lfile_put_contents($v_file, $string);

	$wcline = "\tServerAlias \\\n\t\t*.{$domainname}\n\n";

	// must set here to prepend if server_alias_a empty
	$count = 1;

	foreach($this->main->server_alias_a as $val) {
		// issue 674 - wildcard and subdomain problem
		if ($val->nname === '*') { 
			$count = 2;
			break;
		}
	}

	for ($c = 0 ; $c < $count ; $c++) {

		$string = null;

		$dirp = $this->main->__var_dirprotect;
	
		$this->clearDomainIpAddress();

		$string = null;
		$string = "<VirtualHost \\\n{$this->createVirtualHostiplist("80")}";
		$string .= "\t\t>\n\n";
//		$string .= $this->syncToPort("80", $cust_log, $err_log);

		$syncto = $this->syncToPort("80", $cust_log, $err_log);

		if ($c === 1){
			$syncto = str_replace(" {$domainname}", " wildcards.{$domainname}", $syncto);
			$line  = $wcline;
		}
		else {
			$line = $this->createServerAliasLine();
		}

		$token = "###serveralias###";

		$string .= str_replace($token, $line, $syncto);

		$string .= $this->middlepart($web_home, $domainname, $dirp); 
		$string .= $this->AddOpenBaseDir();
		$string .= $this->endtag();

		lxfile_mkdir($this->main->getFullDocRoot());

		if($this->main->priv->isOn('ssl_flag')) {

			// Do the ssl cert only if the ipaddress exists. Now when we migrate, 

			$string .= "\n\n<IfModule mod_ssl.c>\n";

			if ($this->getServerIp()) {
				$iplist = $this->getSslIpList();
				foreach($iplist as $ip) {
					$string .= "#### ssl virtualhost per ip\n";
					$ssl_cert = $this->sslsysnc($ip);
					if (!$ssl_cert) { continue; }
					$string .= "<VirtualHost \\\n";
				//	$string .= "\t$ip:80\\\n";
					$string .= "\t$ip:443\\\n";
					$string .= "\t\t>\n\n";

					$syncto = $this->syncToPort("443", $cust_log, $err_log);

					if ($c === 1){
						$syncto = str_replace(" {$domainname}", " wildcards.{$domainname}", $syncto);
						$line  = $wcline;
					}
					else {
						$line = $this->createServerAliasLine();
					}

					$token = "###serveralias###";

					$string .= str_replace($token, $line, $syncto);

				//	if($this->main->priv->isOn('ssl_flag')) {
				//		$string .= "\t<IfModule mod_ssl.c>\n";
						$string .= $this->sslsysnc($ip);
				//		$string .= "\t</IfModule>\n\n";
				//	}
					$string .= $this->middlepart($web_home, $domainname, $dirp); 
					$string .= $this->AddOpenBaseDir();
					$string .= $this->endtag();
					$string .= "#### ssl virtualhost per ip $ip end\n\n";
				}
			} else {
				$string .= "#### ssl virtualhost per ip\n";
				$string .= "<VirtualHost \\\n";
			//	$string .= "{$this->createVirtualHostiplist("80")}";
				$string .= "{$this->createVirtualHostiplist("443")}";
				$string .= "\t\t>\n\n";

				$syncto = $this->syncToPort("443", $cust_log, $err_log);

				if ($c === 1){
					$syncto = str_replace(" {$domainname}", " wildcards.{$domainname}", $syncto);
					$line  = $wcline;
				}
				else {
					$line = $this->createServerAliasLine();
				}

				$token = "###serveralias###";

				$string .= str_replace($token, $line, $syncto);

				//--- consistence like lighttpd disable this for no 'exclusive domain/client'
			//	if($this->main->priv->isOn('ssl_flag')) {
			//		$string .= "\t<IfModule mod_ssl.c>\n";
			//		$string .= $this->sslsysnc(null);
			//		$string .= "\t</IfModule>\n\n";
			//	}
				$string .= $this->middlepart($web_home, $domainname, $dirp); 
				$string .= $this->AddOpenBaseDir();
				$string .= $this->endtag();
				$string .= "#### ssl virtualhost per ip end\n";
			}

			$string .= "</IfModule>\n\n\n";
		}

/*
		// --- using Sqlite not work here, so make __var_mmaillist in weblib.php

		$sq = new Sqlite(null, 'mmail');


//		$res = $sq->getRowsWhere("nname = '{$domainname}'");

		$res = $sq->rl_query("SELECT * WHERE nname = '{$domainname}'");

		$string .= self::getCreateWebmail($res);
*/
		if ($c === 1) {
			$v_file = "/home/apache/conf/wildcards/{$domainname}.conf";
			lfile_put_contents($v_file, $string);
		}
		else {
			$v_file = "/home/apache/conf/domains/{$domainname}.conf";

			$mmaillist = $this->main->__var_mmaillist;

			foreach($mmaillist as $m) {
				if ($m['nname'] === $domainname) {
					$list = $m;
					break;
				}
			}

			// --- for the first time domain create
			if (!isset($list)) {
			$list = array('nname' => $domainname, 'parent_clname' => 'domain-'.$domainname, 'webmailprog' => '', 'webmail_url' => '', 'remotelocalflag' => 'local');
			}

			$string .= self::getCreateWebmail(array($list));

			lfile_put_contents($v_file, $string);

			$this->setAddon();		
		}
	}

	createRestartFile('apache');
}

// function getAddon()
function setAddon()
{

	global $gbl, $sgbl, $login, $ghtml;

	$string = "";

	$vaddonlist = $this->main->__var_addonlist;

//	foreach((array) $this->main->__var_addonlist as $v) {
	foreach((array) $vaddonlist as $v) {

		if ($v->ttype === 'redirect') {
			$string .= "<VirtualHost \\\n{$this->createVirtualHostiplist("80")}";
			$string .= "{$this->createVirtualHostiplist("443")}";
			$string .= "\t\t>\n\n";
			$string .= "\tServerName {$v->nname}\n";
			$string .= "\tServerAlias \\\n\t\twww.{$v->nname}\n\n";
			$dst = "{$this->main->nname}/{$v->destinationdir}/";
			$dst = remove_extra_slash($dst);
			$string .= "\tRedirect / http://$dst\n\n";
			$string .= "</VirtualHost>\n\n\n";
		}

		$domto = str_replace("domain-","", $v->parent_clname);
		$rlflag = ($v->mail_flag === 'on') ? 'remote' : 'local';

		$list = array('nname' => $v->nname, 'parent_clname' => $v->parent_clname, 'webmailprog' => '', 'webmail_url' => 'webmail.'.$domto, 'remotelocalflag' => $rlflag);
		$string .= self::getCreateWebmail(array($list));
	}

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "<VirtualHost \\\n{$this->createVirtualHostiplist("80")}";
		$string .= "{$this->createVirtualHostiplist("443")}";
		$string .= "\t\t>\n\n";
		$string .= "\tServerName {$this->main->nname}\n\n";
		$string .= "\tRedirect / http://www.{$this->main->nname}/\n\n";
		$string .= "</VirtualHost>\n\n";

		$string .= "<IfModule mod_ssl.c>\n";
		$string .= "\t<VirtualHost {$this->createVirtualHostiplist("443")}";
		$string .= "\t\t>\n\n";
		$string .= "\t\tServerName {$this->main->nname}\n\n";
		$string .= "\t\tRedirect / https://www.{$this->main->nname}\n\n";
		$string .= "\t</VirtualHost>\n";
		$string .= "<IfModule mod_ssl.c>\n\n\n";
	}

//	return $string;

	if ($string === '') {
		$string = "### No domain(s) redirect to '{$this->main->nname}' ###\n\n\n";
	}

	$v_file = "/home/apache/conf/redirects/{$this->main->nname}.conf";

	lfile_put_contents($v_file, $string);
}

static function createCpConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$vstring = self::staticcreateVirtualHostiplist('80');
	$sstring = self::staticcreateVirtualHostiplist('443');

	$list = array("default" => "_default.conf", "cp" => "cp_config.conf", "disable" => "disable.conf");

	foreach($list as $config => $file) {
		$string = null;
		$string .= "<VirtualHost \\\n{$vstring}{$sstring}"; 
		$string .= "\t\t>\n\n";
		$string .= "\tServerName {$config}\n";
		$string .= "\tServerAlias {$config}.*\n\n";
		$string .= "\tDocumentRoot /home/kloxo/httpd/{$config}/\n";

		if ($config === "default") {
			$string .= "\n\t<Ifmodule mod_userdir.c>\n";
			//-- to make sure http://ip/~client work because maybe 'disabled' on httpd.conf
			//-- not work with exist * on httpd version 2.2.20/2.2.21
		//	$string .= "\t\tUserDir enabled *\n";
			$string .= "\t\tUserDir enabled\n";
			$string .= "\t\tUserDir \"public_html\"\n";
			$string .= "\t</Ifmodule>\n\n";
		}

		//--- issue #705 - don't include for handling error http://ip/~client
	//	$string .= self::staticgetSuexecString('lxlabs');

		$string .= "</VirtualHost>\n\n";
	/*
		if ($file === '_default.conf') {
			$string = '';
		}
	*/
		$fullfile = "/home/apache/conf/defaults/{$file}";

		lfile_put_contents($fullfile, $string);
		system("chown lxlabs:lxlabs {$fullfile}");
	}
}

static function getVipString()
{
	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$vstring[] = "\t{$ip}:80\\\n\t{$ip}:443\\\n";
	}
	$vstring = implode("", $vstring);

	return $vstring;
}

static function createWebmailRedirect($list)
{
	// un-used
}

static function getCreateWebmail($list)
{

	global $gbl, $sgbl, $login, $ghtml;

	$vstring = self::getVipString();
//	dprintr($vstring);
//	$string = null;
	foreach($list as &$l) {
		$string = "";

		$rlflag = (!isset($l['remotelocalflag'])) ? 'local' : $l['remotelocalflag'];

	//	$rlflag = $l['remotelocalflag'];

		$prog = (!isset($l['webmailprog']) || ($l['webmailprog'] === '--system-default--')) ? "" : $l['webmailprog'];

		if ((!$prog) && ($rlflag !== 'remote')) {
			$string .= "### 'webmail.{$l['nname']}' handled by ../webmails/webmail.conf ###\n\n\n";
			continue;
		}

		$string .= "<VirtualHost \\\n{$vstring}";
		$string .= "\t\t>\n\n";
		$string .= "\tServerName webmail.{$l['nname']}\n\n";

		if ($rlflag === 'remote') {
			$l['webmail_url'] = add_http_if_not_exist($l['webmail_url']);
			$string .= "\tRedirect / {$l['webmail_url']}\n\n";
		} else {
			if (is_disabled($l['webmailprog'])) {
				$string .= "\tDocumentRoot /home/kloxo/httpd/webmail/disabled/\n";
			} else {
			//	$string .= "\tDocumentRoot /home/kloxo/httpd/webmail/\n";

				$prog = ($l['webmailprog'] === '--chooser--') ? "" : $l['webmailprog'];
				if ($prog) {
					$string .= "\tDocumentRoot /home/kloxo/httpd/webmail/$prog/\n";
				}
				else {
					$string .= "\tDocumentRoot /home/kloxo/httpd/webmail/\n";
				}
			}

		/*
			$prog = ($l['webmailprog'] === '--chooser--')? "": $l['webmailprog'];
			if ($prog) {
				$string .= "\n\tDirectoryIndex redirect-to-$prog.php index.php index.html\n";
			}
		*/

		//	$string .= "\t<Ifmodule mod_suphp.c>\n";
		//	$string .= "\t\tSuPhp_UserGroup {$l['systemuser']} {$l['systemuser']}\n";
		//	$string .= "\t\tSuPhp_UserGroup lxlabs lxlabs\n";

			$string .= self::staticgetSuexecString('lxlabs');

		//	$string .= "\t</Ifmodule>\n\n";
		}

		$string .= "</VirtualHost>\n\n\n";
	}

	return $string;


//	lfile_put_contents("/etc/httpd/conf/kloxo/webmail_redirect.conf", $string);
//	lfile_put_contents("/home/apache/conf/webmails/_webmail_redirect.conf", $string);
}

function getDav()
{
	$string = null;
	$bdir = "/home/httpd/{$this->main->nname}/__webdav";
	lxfile_mkdir($bdir);
	foreach($this->main->__var_davuser as $k => $v) {
		$file = get_file_from_path($k);
		$file = "{$bdir}/{$file}";
		$string .= "\t<Location {$k}>\n";
		$string .= "\t\tDAV On\n";
		$string .= "\t\tAuthType Basic\n";
		$string .= "\t\tAuthName \"WebDAV Restricted\"\n";
		$string .= "\t\tAuthUserFile {$file}\n";
		//$string .= "\t\t<LimitExcept GET HEAD OPTIONS>\n";
		$string .= "\t\t<Limit HEAD GET POST OPTIONS PROPFIND>\n";
		$string .= "\t\t\tAllow from all\n";
		$string .= "\t\t</Limit>\n";
		$string .= "\t\t<Limit MKCOL PUT DELETE LOCK UNLOCK COPY MOVE PROPPATCH>\n";
		$string .= "\t\t\tallow from all\n";
		$string .= "\t\t</Limit>\n";
		$string .= "\t\tRequire valid-user\n";
		//$string .= "\t\t</LimitExcept>\n";
		$string .= "\t</Location>\n\n";
	}
	return $string;
}

function frontPagePassword()
{
	return;
	$password = $this->main->__var_sysuserpassword['realpass']? $this->main->__var_sysuserpassword['realpass']: 'something';
	lxshell_return("htpasswd", "-b", "-c", "{$this->main->getFullDocRoot()}/_vti_pvt/service.pwd", $this->main->ftpusername, $password);
}

function frontPageEnable()
{
	global $gbl, $sgbl, $login, $ghtml;
	//$this->main->nname;
	//$this->main->username;
	//"$sgbl->__path_httpd_root/{$this->main->nname}";
	$string = null;
	$web_path = $sgbl->__path_httpd_root;
	$base_root = $sgbl->__path_httpd_root;
	$v_dir = "{$web_path}/{$this->main->nname}/conf";
	$log_path = "{$web_path}/{$this->main->nname}/stats";
	$log_path1 = "{$log_path}/logs";
	$cust_log = "{$log_path1}/{$this->main->nname}-custom_log"; 
	$err_log = "{$log_path1}/{$this->main->nname}-error_log";
	$awstat_conf = "{$sgbl->__path_real_etc_root}/awstats/";
	$awstat_dirdata = "{$sgbl->__path_kloxo_httpd_root}/awstats/";
	$user_home = "{$this->main->getFullDocRoot()}/";
	return;

	if ($this->main->priv->isOn('frontpage_flag')) {

		$htaccessstring = null;
		$htaccessstring .= "";
		$web_path = "{$sgbl->__path_httpd_root}/";
//		$for_file ="$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.frontpage.{$this->main->nname}";
		$for_file ="/home/apache/conf/frontpage/{$this->main->nname}.conf";

		//$for_file = lx_tmp_file("{$this->main->nname}_frontpage");

		$extra  = "ServerRoot \"/etc/httpd/\"";
		$extra .= "\n";
		$extra .= $this->syncToPort("80", "ttt", "ttt", true);
		$extra .= "</VirtualHost>";
		lfile_put_contents($for_file, $extra);
		$password = $this->main->__var_sysuserpassword['realpass'] ? $this->main->__var_sysuserpassword['realpass'] : 'something';

		//lxfile_unix_chown($for_file, "root:root");
		//lxfile_mkdir( "$webpath/www/"
	//	lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o" ,"setadminport", "-p", "2222", "-s", $for_file, "-username", $this->main->username, "-pw", "w433iqoq","-t", "apache-2.0");
		$val = lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o", "install", "-t", "apache-2.0", "-p", "80", "-xu", $this->main->username, "-xg",  $this->main->username, "-s", $for_file, "-u", $this->main->ftpusername, "-pw", $password,  "-m", $this->main->nname);
		//$val = lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o", "setproperty", "-pn", "SMTPHost", "-pv", "mail.{$this->main->nname}", "-m", $this->main->nname);
		$val = lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o", "setproperty", "-pn", "SMTPHost", "-pv", "127.0.0.1", "-m", $this->main->nname);
		$val = lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o", "setproperty", "-pn", "MailSender", "-pv", "webmaster@{$this->main->nname}", "-m", $this->main->nname);
		lxshell_return("htpasswd", "-b", "{$this->main->getFullDocRoot()}/_vti_pvt/service.pwd", $this->main->ftpusername, $password);
		//unlink($for_file);
		//lfile_put_contents("$webpath/www/_vti_bin/_vti_adm/.htaccess", $htaccessstring);
		//lxfile_unix_chown("...", "$this->main->username, 
	} else {
		/// Remove frontpage...
		
		$val = lxshell_return("/usr/local/frontpage/version5.0/bin/owsadm.exe", "-o", "fulluninstall", "-p", "80", "-m", $this->main->nname);
//		$for_file ="$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.frontpage.{$this->main->nname}";
		$for_file ="/home/apache/conf/frontpage/{$this->main->nname}.conf";
		lunlink($for_file);

	}
	return $string;
}

static function createSSlConf($iplist, $domainiplist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;
	$alliplist = os_get_allips();
//	dprintr($domainiplist);
	foreach((array) $iplist as $ip) {
		if (!array_search_bool($ip['ipaddr'], $alliplist)) {
			continue;
		}

		// Skip if it is in the domain ip list. We need to create it only for the ipaddresses that do not have domains set for them.
		// Don't skip. The ssl is loaded first. The only issue is that https://ip will show default apache page.
		if (isset($domainiplist[$ip['ipaddr']]) && $domainiplist[$ip['ipaddr']]) {
			continue;
		}
		$string .= "<Virtualhost \\\n";
		$string .= "\t{$ip['ipaddr']}:443\\\n";
		$string .= "\t\t>\n\n";
		$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);

		$certificatef = "{$sgbl->__path_ssl_root}/{$ssl_cert}.crt";
		$keyfile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.key";
		$cafile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.ca";

		sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

		$string .= "\tSSLEngine On \n";
		$string .= "\tSSLCertificateFile {$certificatef}\n";
		$string .= "\tSSLCertificateKeyFile {$keyfile}\n";
		$string .= "\tSSLCACertificatefile {$cafile}\n\n";
		$string .= "</Virtualhost>\n\n";
	}

	//	$string .= "SSLLogFile /\n";
//	$sslfile = "/etc/httpd/conf/kloxo/ssl.conf";
	$sslfile = "/home/apache/conf/defaults/ssl.conf";

	$string = "<IfModule mod_ssl.c>\n\n{$string}\n</IfModule>\n\n";
	//$string = null;
	$string .= "DirectoryIndex index.php index.htm default.htm default.html\n\n";

	lfile_put_contents($sslfile, $string);
}

function sslsysnc($ipad)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$ssl_cert = null;
	foreach((array) $this->main->__var_ipssllist as $ip) {
		//Temporary hack... Ideally, we should loop through the domainip list, and create an ssl for each ip. But here, we are merely going to use the first ip.
/*
		if (isset($this->main->__var_domainipaddress[$ip['ipaddr']]) && $this->main->__var_domainipaddress[$ip['ipaddr']] === $this->main->nname) {

		}

		if (!$ssl_cert) {
			// If no ssl, then create a dummy one with the first ipssl.
			foreach((array) $this->main->__var_ipssllist as $ip) {
			if ($ip['ipaddr'] === $this->main->ipaddress) {
				$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);
				break;
			}
		}
		}
*/
		// Get the first certificate;
		if (!$ipad) {
			$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);
			break;
		}
		if ($ip['ipaddr'] === $ipad) {
			$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);
			break;
		}
	}

	if (!$ssl_cert) {
		return;
	}

	$string = null;
	$certificatef = "{$sgbl->__path_ssl_root}/{$ssl_cert}.crt";
	$keyfile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.key";
	$cafile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.ca";

	sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

	$string .= "\tSSLEngine On \n";
	$string .= "\tSSLCertificateFile {$certificatef}\n";
	$string .= "\tSSLCertificateKeyFile {$keyfile}\n";
	$string .= "\tSSLCACertificatefile {$cafile}\n\n";

	//	$string .= "SSLLogFile /\n";

	return $string;
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gen = $login->getObject('general')->generalmisc_b;

	$alist[] = "a=list&c=component";

	return $alist;
}

function middlepart($web_home, $domain, $dirp) 
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;

	foreach($this->main->customerror_b as $k => $v) {
		if (csb($k, "url_") && $v) {
			$num = strfrom($k, "url_");
			if (csb($v, "http:/")) {
				$nv = $v;
			} else {
				$nv = remove_extra_slash("/{$v}");
			}
			$string .= "\tErrorDocument {$num} {$nv}\n";
		}
	}

//	$string .= $this->disablePhp();
	$string .= $this->enablePhp();

	$string .= $this->getDirprotect('');

	return $string;
}

function getDirprotect()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$string = null;
	foreach((array) $this->main->__var_dirprotect as $prot) {
		if (!$prot->isOn('status') || $prot->isDeleted()) {
			continue;
		}
		$string .= $this->getDirprotectCore($prot->authname, $prot->path, $prot->getFileName());

	}

	return $string;
}

function getDirprotectCore($authname, $path, $file)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$string  = null;

	// issue #74
	$path = remove_extra_slash("\"/{$path}\"");

	$string .= "\t<Location {$path}>\n";
	$string .= "\t\tAuthType Basic\n";
	$string .= "\t\tAuthName \"{$authname}\"\n";

	// issue #74
	$string .= "\t\tAuthUserFile \"{$sgbl->__path_httpd_root}/{$this->main->nname}/__dirprotect/{$file}\"\n";

	$string .= "\t\trequire  valid-user\n";
	$string .= "\t</Location>\n";

	return $string;
}

function getSuexecString($username)
{
	$nname = $this->main->nname;

	return self::staticgetSuexecString($username, $nname);
}

// change to staticgetSuexecString() for accept call by static function
static function staticgetSuexecString($username, $nname = null)
{
	// issue #567 -- change '$this->main->username' to '$username' for consistence
	$string  = "\n";
/*
	$string .= "\t<Ifmodule mod_userdir.c>\n";
	$string .= "\t\tUserDir enabled\n";
	$string .= "\t\tUserDir \"public_html\"\n";
	$string .= "\t</Ifmodule>\n\n";
*/
	// --- mod_suexec - begin
	$string .= "\t<IfModule suexec.c>\n";
	$string .= "\t\tSuexecUserGroup {$username} {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_suexec - end

	// --- mod_suphp - begin
	// still error for http://ip/~client for other then admin
	$string .= "\t<IfModule mod_suphp.c>\n";

/* --- issue #563
	$string .= "\t\tAddType application/x-httpd-php .php\n";
	$string .= "\t\tRemoveHandler .php\n";
	$string .= "\t\t<FilesMatch \"\.php$\" >\n";
	$string .= "\t\t\tSetHandler x-httpd-php\n";
	$string .= "\t\t</FilesMatch>\n";
	$string .= "\t\t<Location />\n";
	$string .= "\t\t\tsuPHP_AddHandler x-httpd-php \n";
	$string .= "\t\t</Location>\n";
--- */

	$string .= "\t\tSuPhp_UserGroup {$username} {$username}\n";
	if ($username !== 'lxlabs') {
//		$string .= "\t\tsuPHP_Configpath \"/home/httpd/{$this->main->nname}\"\n";
		$string .= "\t\tsuPHP_Configpath \"/home/httpd/{$nname}\"\n";
	}
	$string .= "\t</IfModule>\n\n";
	// --- mod_suphp - end

	// --- mod_ruid2 - begin - issue #566
	$string .= "\t<IfModule mod_ruid2.c>\n";
	$string .= "\t\tRMode config\n";
	$string .= "\t\tRUidGid {$username} {$username}\n";
	// --- disable prevent http://ip/~client error
//	$string .= "\t\tRMinUidGid {$username} {$username}\n";
//	$string .= "\t\tRGroups {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_ruid2 - end

	// --- httpd-itk - begin - issue #567
	// still error for http://ip/~client
	$string .= "\t<IfModule itk.c>\n";
	$string .= "\t\tAssignUserId {$username} {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- httpd-itk - end
/*
	// --- mod_fastcgi - begin - issue #567
	$string .= "\t<IfModule mod_fastcgi.c>\n";
	$string .= "\t\t## TODO\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_fastcgi - end

	// --- mod_fcgi - begin - issue #567
	$string .= "\t<IfModule mod_fcgi.c>\n";
	$string .= "\t\t## TODO\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_fcgi - end
*/
	return $string;
}

function getAwstatsString()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string  = null;
	$string .= "ScriptAlias /awstats/ \"{$sgbl->__path_kloxo_httpd_root}/awstats/wwwroot/cgi-bin/\"\n";
	if ($this->main->stats_password) {
		$string .= "\t".$this->getDirprotectCore("Awstats", "/awstats", "__stats");
	}
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);

	return $string;
}

function getDocumentRoot($subweb)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$base_root = $sgbl->__path_httpd_root;
	$web_home = $sgbl->__path_httpd_root;

	$path = "{$this->main->getFullDocRoot()}/";

    // #656 When adding a subdomain, the Document Root field is not being validated
    // Adding quotations so that we can work with directories with spaces
	$string = null;
	if($this->main->isOn('status')) {
		$string .= "DocumentRoot \"{$path}\"\n\n";
	} else {
		if ($this->main->__var_disable_url) {
			$url = add_http_if_not_exist($this->main->__var_disable_url);
			$string .= "Redirect / {$url}\n\n";
		} else {
			$disableurl = "/home/kloxo/httpd/disable/";
			$string .= "DocumentRoot \"{$disableurl}\"\n\n";
		}
	}

	return $string;
}

function getIndexFileOrder()
{
	if ($this->main->indexfile_list) {
		$list = $this->main->indexfile_list;
	} else {
		$list = $this->main->__var_index_list;
	}
	if (!$list) { return; }

	$string = implode(" ", $list);
	$string = "DirectoryIndex $string\n";

	return $string;
}

function createHotlinkHtaccess()
{
	$string = $this->hotlink_protection();
	$stlist[] = "### Kloxo Hotlink Protection";
	$endlist[] = "### End Kloxo Hotlink Protection";
	$startstring = $stlist[0];
	$endstring = $endlist[0];
	$htfile = "{$this->main->getFullDocRoot()}/.htaccess";
	file_put_between_comments($this->main->username, $stlist, $endlist, $startstring, $endstring, $htfile, $string);
	$this->norestart = 'on';
}

function syncToPort($port, $cust_log, $err_log, $frontpage = false)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$base_root = "$sgbl->__path_httpd_root";
	$user_home = "{$this->main->getFullDocRoot()}/";
/*
	if (!$this->main->ipaddress) {
		throw new lxException("no_ipaddress", '');
	}
*/
	$string  = null;

    // #656 When adding a subdomain, the Document Root field is not being validated
    // Adding quotations so that we can work with directories with spaces
	if ($this->main->isOn('force_www_redirect')) {
		$string .= "\tServerName www.{$this->main->nname}\n" ;
	} else {
		$string .= "\tServerName {$this->main->nname}\n" ;
	}

//	$string .= $this->createServerAliasLine();
	$string .= "###serveralias###";

	$domname = $this->main->nname;
	
	//$string .= $this->hotlink_protection();
	$string .= "\t".$this->getBlockIP();


	$string .= $this->getDocumentRoot('www');
	$string .= "\t".$this->getIndexFileOrder();

	$string .= "\t".$this->getAwstatsString();

	$string .= "\t".$this->getSuexecString($this->main->username);
	foreach((array) $this->main->redirect_a as $red) {
		$rednname = remove_extra_slash("/{$red->nname}");
		if ($red->ttype === 'local') {
			$string .= "\tAlias \"{$rednname}\" \"{$user_home}\"/{$red->redirect}\"\n";
		} else {
			if (!redirect_a::checkForPort($port, $red->httporssl)) { continue; }
			$string .= "\tRedirect \"{$rednname}\" \"{$red->redirect}\"\n";
		}
	}

	if ($this->main->__var_statsprog === 'awstats') {
		$string .= "\tRedirect /stats http://$domname/awstats/awstats.pl?config=$domname\n";
		$string .= "\tRedirect /stats/ http://$domname/awstats/awstats.pl?config=$domname\n\n";
	} else {
		$string .= "\tAlias /stats {$sgbl->__path_httpd_root}/{$domname}/webstats/\n\n";
	}
	$string .= "\tAlias /__kloxo \"/home/{$this->main->customer_name}/kloxoscript/\"\n\n";

	$string .= "\tRedirect /kloxo https://cp.{$this->main->nname}:{$this->main->__var_sslport}\n";
	$string .= "\tRedirect /kloxononssl http://cp.{$this->main->nname}:{$this->main->__var_nonsslport}\n\n";

	$string .= "\tRedirect /webmail http://webmail.{$this->main->nname}\n\n";
	$string .= "\t<Directory \"/home/httpd/{$domname}/kloxoscript/\">\n";
	$string .= "\t\tAllowOverride All\n";
	$string .= "\t</Directory>\n\n";

	$string .= $this->addSendmail();

	if ($this->main->priv->isOn('cgi_flag')) {
		$string .= "\tScriptAlias /cgi-bin/ \"{$user_home}/cgi-bin/\"\n\n";
	}
	if ($port === '80') {
		$string .= "\tCustomLog \"{$cust_log}\" combined  \n";
		$string .= "\tErrorLog \"{$err_log}\"\n\n";
	}

	// hack for frontpage. It needs the proper directory.
	if ($frontpage) {
		$string .= "\t<Directory \"{$this->main->getFullDocRoot()}/\">\n";
		$string .= "\t\tAllowOverride All\n";
		$string .= "\t</Directory>\n\n";
	} else {
		$string .= "\t<Directory \"{$this->main->getFullDocRoot()}/\">\n";
		$string .= "\t\tAllowOverride All\n";
		$string .= "\t</Directory>\n\n";
		$string .= "\t<Location />\n";
		$extrastring = null;
		if (isset($this->main->webmisc_b)) {
			if ($this->main->webmisc_b->isOn('execcgi')) {
				$extrastring .= "+ExecCgi";
			}
			if ($this->main->webmisc_b->isOn('dirindex')) {
				$extrastring .= " +Indexes";
			}
		}

		$string .= "\t\tOptions +Includes +FollowSymlinks {$extrastring}\n";

		if (isset($this->main->webmisc_b) && $this->main->webmisc_b->isOn('execcgi')) {
			$string .= "\t\tAddHandler cgi-script .cgi\n";
		}

		$string .= "\t</Location>\n\n";
		$string .= "\t<Directory \"{$sgbl->__path_httpd_root}/{$this->main->nname}/webstats/\">\n";
		$string .= "\t\tAllowOverride All\n";
		$string .= "\t</Directory>\n\n";
	}

	if (isset($this->main->webindexdir_a)) foreach((array) $this->main->webindexdir_a as $webi) {
		$string .= "\t<Directory {$this->main->getFullDocRoot()}/{$webi->nname}>\n";
		$string .= "\t\tAllowOverride All\n";
		$string .= "\t\tOptions +Indexes\n";
		$string .= "\t</Directory>\n\n";
	}
		
	if($this->main->text_extra_tag) {
		$string .= "\n\n#Extra Tags\n{$this->main->text_extra_tag}\n#End Extra Tags\n\n";
	}

	if ($this->main->stats_password) {
		$string .= $this->getDirprotectCore("stats", "/stats", "__stats");
	}

	$string .= $this->getDirIndexCore("/stats");

	return $string;
}

function getRailsConf($app)
{
	$string .= "\tProxyPass /$app http://localhost:$apport/\n";
	$string .= "\tProxyPassReverse /$app http://localhost:$apport\n";
	$string .= "\tProxyPreserveHost on\n\n";
}

function getDirIndexCore($dir)
{
	$string = null;
	$dir = remove_extra_slash("/{$dir}");
	$string .= "\t<Location {$dir}>\n";
	$string .= "\t\tOptions +Indexes\n";
	$string .= "\t</Location>\n\n";

	return $string;
}

function EndTag()
{
	$string  = null;
	$string .= "</VirtualHost>\n";  

	return $string;
}

function DeleteSubWeb()
{
	global $gbl, $sgbl, $login, $ghtml; 

	 $web_home = "$sgbl->__path_httpd_root" ;

	 foreach ($this->main->__t_delete_subweb_a_list as $t) {
		 $file = "{$this->main->getFullDocRoot()}/{$t->nname}";
		 //recursively_remove($file);
	 }
}

// The rest

function createForwarddir()
{
	global $gbl, $sgbl, $login, $ghtml; 
/* --- no need forward for new structure
	lxfile_mkdir( "__path_apache_path/kloxo/forward/");
	lxfile_touch("__path_apache_path/kloxo/forward/{$this->main->nname}");
	lxfile_touch("__path_apache_path/kloxo/forward/forwardhost.conf");
	//lxfile_unix_chmod("__path_apache_path/kloxo/forward/{$this->main->nname}", "0710");

	lxfile_mkdir( "/home/apache/conf/forward/");
	lxfile_touch("/home/apache/conf/forward/{$this->main->nname}.conf");
	lxfile_touch("/home/apache/conf/defaults/forwardhost.conf");
--- */
}

function createServerAliasLine()
{
	// --- alias too long if one line (http://forum.lxcenter.org/index.php?t=msg&th=16556)
	$string  = null;
	if ($this->main->isOn('force_www_redirect')) {
		$string .= "\tServerAlias ";
	} else {
		$string .= "\tServerAlias \\\n\t\twww.{$this->main->nname}";
	}
	foreach($this->main->server_alias_a as $val) {
		// issue 674 - wildcard and subdomain problem
		if ($val->nname === '*') { continue; }

//		$string .= " {$val->nname}.{$this->main->nname}";
//		$string .= "\tServerAlias {$val->nname}.{$this->main->nname}\n";
		$string .= "\\\n\t\t{$val->nname}.{$this->main->nname}";
	}

	foreach((array) $this->main->__var_addonlist as $d) {
		if ($d->ttype === 'redirect') {
			continue;
		}
//		$string .= " {$d->nname} www.{$d->nname}";
//		$string .= "\tServerAlias {$d->nname} www.{$d->nname}\n";
		$string .= "\\\n\t\t{$d->nname}\\\n\t\twww.{$d->nname}";
	}

	$string .= "\n\n";

	return $string;
}

function createForwardconf()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $gbl, $sgbl, $global_shell_error;
 	
	return;
}

function updateForwardconf()
{
	global $gbl, $sgbl, $login, $ghtml; 
/* --- no need forward for new structure
	return;
//	$forwardincludefile = "$sgbl->__path_apache_path/kloxo/forward/forwardhost.conf";
	$forwardincludefile = "/home/apache/conf/defaults/forwardhost.conf";

	$result = $this->main->__var_fdomain_list;
	$fdata = null;
	$result = merge_array_object_not_deleted($result, $this->main);
	foreach((array) $result as $dom){
		if ($dom['nname'] === $this->main->nname) {
			continue;
		}
	}

	lfile_put_contents($forwardincludefile, $fdata);
--- */
}

function denyByIp()
{
	$string  = null;
	$string .= "\t<Ifmodule mod_access.c>\n";
	$string .= "\t\t<Location />\n";
	$string .= "\t\t\tOrder Allow,Deny\n";
	$string .= "\t\t\tDeny from 6.28.130.\n";
	$string .= "\t\t\tAllow from all\n";
	$string .= "\t\t</Location>\n";
	$string .= "\t</Ifmodule>\n\n";

	return $string;
}

function addDomain()
{
	$this->main->createDir();
	$this->createConffile();
	$this->updateMainConfFile();
	if ($this->main->priv->isOn('frontpage_flag')) {
		$this->frontPageEnable();
	}

//	dprint(getcwd());
	$this->main->createPhpInfo();
	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
}

function hotlink_protection()
{
	if (!$this->main->isOn('hotlink_flag')) {
		return null;
	}

	$allowed_domain_string = $this->main->text_hotlink_allowed;
	$allowed_domain_string = trim($allowed_domain_string);
	$allowed_domain_string = str_replace("\r", "", $allowed_domain_string);
	$allowed_domain_list = explode("\n", $allowed_domain_string);

	$string  = null;
	$string .= "\tRewriteEngine on\n";
	$string .= "\tRewriteCond %{HTTP_REFERER} !^$\n";

	$ht = trim($this->main->hotlink_redirect, "/");
	$ht = "/$ht";
	foreach($allowed_domain_list as $l) {
		$l = trim($l);
		if (!$l) { continue; }
		$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*$l.*$ [NC]\n";
		$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*$l.*$ [NC]\n";
	}
	$l = $this->main->nname;
	$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*$l.*$ [NC]\n";
	$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*$l.*$ [NC]\n";
	$string .= "\tRewriteRule .*[JrRjP][PpdDAa][GfFgrR]$|.*[Gg][Ii][Ff]$ $ht [L]\n";

	return $string;
}

static function createWebDefaultConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 

	self::createCpConfig();
	self::createWebmailConfig();
	
	createRestartFile("apache");
}

static function createWebmailConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$fdata = null;

	$webmaildef = $login->getObject('general')->generalmisc_b->webmail_system_default;

	if (($webmaildef === '--chooser--') || (!isset($webmaildef))) {
		$webmaildefpath = '';
	}
	else {
		$webmaildefpath = $webmaildef."/";
	}

	$webdata  = null;
	$webdata .= "<VirtualHost \\\n";
	$webdata .= self::staticcreateVirtualHostiplist("80");
	$webdata .= self::staticcreateVirtualHostiplist("443");
	$webdata .= "\t\t>\n\n";
	$webdata .= "\tServerName webmail\n";
	$webdata .= "\tServerAlias webmail.*\n\n";
	$webdata .= "\tDocumentRoot {$sgbl->__path_kloxo_httpd_root}/webmail/$webmaildefpath\n";

	$webdata .= self::staticgetSuexecString('lxlabs');

	$webdata .= "</VirtualHost>\n\n";

//	$webmailfile = "__path_real_etc_root/httpd/conf/kloxo/webmail.conf";
	$webmailfile = "/home/apache/conf/webmails/webmail.conf";

	lfile_put_contents($webmailfile, $webdata);
}

function dbactionAdd()
{
	$this->addDomain();
	$this->main->doStatsPageProtection();
}

function dbactionDelete()
{
	$this->delDomain();
}

function dosyncToSystemPost()
{
	global $gbl, $sgbl, $login, $ghtml; 
//	lxshell_return("/etc/init.d/httpd", "reload");
	if (!$this->isOn('norestart')) {
		createRestartFile("apache");
	}
}

function addAllSubweb()
{
	$this->AddSubWeb($this->subweb_a);
}

function AddSubWeb($list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$web_home = "$sgbl->__path_httpd_root" ;
	$base_root = "$sgbl->__path_httpd_root";
	$user_home = "{$this->main->getFullDocRoot()}/";

	foreach((array) $list as $subweb) {
		lxfile_mkdir("$user_home/subdomains/{$subweb->nname}");
	//	lfile_put_contents("$user_home/{$subweb->nname}/index.html", "Subdomain Created by Kloxo");
	}
}

function fullUpdate()
{
	$domname = $this->main->nname;
	lxfile_mkdir("__path_httpd_root/$domname/webstats");

	$this->main->createPhpInfo();
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);

	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
/*
	if ($this->main->ttype === 'forward') {
		$this->createForwardconf();
	} else {
*/
		$this->createConffile();
		$this->frontPageEnable();
		$this->updateMainConfFile();
//	}

	self::createWebDefaultConfig();

	lxfile_unix_chown_rec("{$this->main->getFullDocRoot()}/", "{$this->main->username}:{$this->main->username}");
	lxfile_unix_chmod("{$this->main->getFullDocRoot()}/", "0755");
	lxfile_unix_chmod("{$this->main->getFullDocRoot()}", "0755");
	lxfile_unix_chown("__path_httpd_root/{$this->main->nname}", "{$this->main->username}:apache");

}

function dbactionUpdate($subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$this->main->customer_name) {
		log_log("critical", "Lack customername for web: {$this->main->nname}");
		return;
	}

	switch($subaction) {

		case "full_update":
			$this->fullUpdate();
			$this->main->doStatsPageProtection();
			break;

		case "add_subweb_a":
			$this->AddSubWeb($this->main->__t_new_subweb_a_list);
			$this->createConffile();
			break;

		case "delete_subweb_a":
			$this->DeleteSubWeb();
			$this->createConffile();
			break;

		case "enable_frontpage_flag":
			$this->frontPageEnable();
			//$this->createConffile();
			break;

		case "frontpage_password":
			$this->frontPagePassword();
			break;

		case "changeowner":
			$this->main->webChangeOwner();
			$this->createConffile();
			$this->frontPageEnable();
			break;

		case "create_config":
		case "addondomain":
			$this->createConffile();
			break;

		case "add_delete_dirprotect":
		case "extra_tag" : 
		case "add_dirprotect" : 
		case "custom_error":
		case "dirindex":
		case "docroot":
		case "ipaddress": 
		case "blockip";
		case "add_redirect_a":
		case "delete_redirect_a":
		case "delete_redirect_a":
		case "add_webindexdir_a":
		case "delete_webindexdir_a":
		case "add_server_alias_a" : 
		case "delete_server_alias_a" : 
		case "configure_misc":
			$this->createConffile();
			break;

		case "redirect_domain" :
			$this->createConffile();
			break;

		case "add_forward_alias_a" : 
		case "delete_forward_alias_a" : 
			$this->createForwardconf();
			break;

		case "fixipdomain":
			$this->createConffile();
			$this->updateMainConfFile();
			self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
			break;

		case "enable_php_manage_flag":
			$this->createConffile();
			break;

		case "toggle_status" : 
			$this->createConffile();
			break;

		case "hotlink_protection":
			$this->createHotlinkHtaccess();
			break;

		case "enable_php_flag":
		case "enable_cgi_flag":
		case "enable_inc_flag":
		case "enable_ssl_flag" : 
			$this->createConffile();
			//$this->updateMainConfFile();
			break;

		case "stats_protect":
			$this->main->doStatsPageProtection();
			$this->createConffile();
			break;

		case "default_domain":
			$this->main->setupDefaultDomain();
			break;

		case "graph_webtraffic":
			return rrd_graph_single("webtraffic (bytes)", $this->main->nname, $this->main->rrdtime);
			break;

		case "run_stats":
			$this->main->runStats();
			break;
	}
}

function do_backup()
{
	return $this->main->do_backup();
}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name = $this->main->nname;
	$fullpath = "{$sgbl->__path_customer_root}/{$this->main->customer_name}/";

	$this->main->do_restore($docd);

	lxfile_unix_chown_rec($fullpath, $this->main->username);
}

}
