<?php 

#
# Project Issue #735
# DT10022014
# Removed all \n lines from <virtualhost> creation.
#

class web__apache extends lxDriverClass {

//######################################### SyncToSystem Starts Here

static function uninstallMe()
{
	global $gbl, $sgbl, $login, $ghtml;

	lxshell_return("service", "httpd", "stop");
	lxshell_return("rpm", "-e", "--nodeps", "httpd");
	if (file_exists("/etc/init.d/httpd")) {
		lunlink("/etc/init.d/httpd");
	}
}

static function installMe()
{
	global $gbl, $sgbl, $login, $ghtml;

	$ret = lxshell_return("yum", "-y", "install", "httpd", "mod_ssl");

	if ($ret) { throw new lxexception('install_httpd_failed', 'parent'); }

	lxshell_return("chkconfig", "httpd", "on");

	$cver = "###version0-6###";
	$fver = file_get_contents("/etc/httpd/conf.d/~lxcenter.conf");
	
	if(stristr($fver, $cver) === FALSE) {
		lxfile_cp("/usr/local/lxlabs/kloxo/file/apache/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
	}

	lxfile_cp("/usr/local/lxlabs/kloxo/file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");

	// MR -- old structure
	lxfile_rm("/etc/httpd/conf/kloxo");
	lxfile_rm("/home/httpd/conf");
	
	// MR -- new structure	
	$path = "/home/apache/conf";

	$list = array("defaults", "domains", "redirects", "webmails", "wildcards", "exclusive");

	foreach($list as $k => $l) {
		if (!lxfile_exists("{$path}/{$l}")) {
			lxfile_mkdir("{$path}/{$l}");
		}
	}

	// MR -- some vps include /etc/httpd/conf.d/swtune.conf
	lxfile_rm("/etc/httpd/conf.d/swtune.conf");

	// issue #527
	lxfile_cp("../file/apache/etc_init.d", "/etc/init.d/httpd");
	lxshell_return("{$sgbl->__path_php_path}", "../bin/misc/installsuphp.php");

	createRestartFile("apache");
}

function updateMainConfFile()
{
	global $gbl, $sgbl, $login, $ghtml;

	$init_file = "/home/apache/conf/defaults/init.conf";

	$vdomlist = $this->main->__var_vdomain_list; 

	$iplist = os_get_allips();

	$fdata = null;

	foreach($iplist as $key => $ip){
		if ($ip) {
			$fdata .= "NameVirtualHost {$ip}:80\n";
			$fdata .= "NameVirtualHost {$ip}:443\n\n";
		}
	}

	lfile_put_contents($init_file, $fdata);

	$virtual_file = "/home/apache/conf/defaults/stats.conf";

	$fdata = null;

	$fdata .= "Alias /awstatscss \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/css/\"\n";
	$fdata .= "Alias /awstatsicons \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/icon/\"\n\n";

	lfile_put_contents($virtual_file, $fdata);

	// MR -- override httpd.conf
	lxfile_cp("/usr/local/lxlabs/kloxo/file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");

	if (file_exists("/etc/httpd/conf/kloxo")) {
		// MR -- delete old structure
		exec("rm -rf /etc/httpd/conf/kloxo");
		exec("rm -rf /httpd/httpd/conf");

		$vdomlist = merge_array_object_not_deleted($vdomlist, $this->main);

		foreach((array) $vdomlist as $dom) {
			exec("rm -rf {$sgbl->__path_httpd_root}/{$dom['nname']}/conf");
		}
	}
}

function getServerIp()
{
	global $gbl, $sgbl, $login, $ghtml;

	foreach($this->main->__var_domainipaddress as $ip => $dom) {
		if ($dom === $this->main->nname) {
			return true;
		}
	}

	return false;
}

function getSslIpList()
{
	global $gbl, $sgbl, $login, $ghtml;

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
	global $gbl, $sgbl, $login, $ghtml;

	$string = "";

	if ($this->getServerIp()) {
		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue; }

			$string .= "{$ip}:{$port} ";
		}

		return $string;
	}

	$iplist = os_get_allips();

	foreach($iplist as $ip) {
		$string .= "{$ip}:{$port} ";
	}

	return $string;
}

static function staticcreateVirtualHostiplist($port)
{
	$string = "";

	$iplist = os_get_allips();

	foreach($iplist as $ip) {
		$string .= "{$ip}:{$port} ";
	}

	return $string;
}

function addSendmail()
{
	global $gbl, $sgbl, $login, $ghtml;

	// enabled (rev 461)

	$sendmailstring  = "\t\tphp_admin_value sendmail_path \"/usr/sbin/sendmail -t -i\"\n";
	$sendmailstring .= "\t\tphp_admin_value sendmail_from \"{$this->main->nname}\"\n";

	$string  = "\t<IfModule sapi_apache2.c>\n";
	$string .= $sendmailstring;
	$string .= "\t</IfModule>\n\n";

	$string .= "\t<IfModule mod_php5.c>\n";
	$string .= $sendmailstring;
	$string .= "\t</IfModule>\n\n";

	return $string;
}

function AddOpenBaseDir()
{
	global $gbl, $sgbl, $login, $ghtml;

	if (isset($this->main->webmisc_b) && $this->main->webmisc_b->isOn('disable_openbasedir')) {
		return null;
	}

	// MR -- fixed for 'disable' client
	if(!$this->main->isOn('status')) {
		return null;
	}

	$adminbasedir = trim($this->main->__var_extrabasedir);

	if ($adminbasedir) {
		$adminbasedir .= ":";
	}

	$uroot = $sgbl->__path_customer_root;

	$corepath = "{$uroot}/{$this->main->customer_name}";

	$httpdpath = "{$uroot}/{$this->main->nname}";

	$path  = "{$adminbasedir}";
	$path .= "{$corepath}:";
	$path .= "{$corepath}/kloxoscript:";
	$path .= "{$httpdpath}:";
	$path .= "{$httpdpath}/httpdocs:";
	$path .= "/tmp:";
	$path .= "/usr/share/pear:";
	$path .= "/var/lib/php/session/:";
	$path .= "/home/kloxo/httpd/script";

	$openbasdstring  = "php_admin_value open_basedir \"{$path}\"\n";

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
	global $gbl, $sgbl, $login, $ghtml;

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

function enablePhp()
{
	global $gbl, $sgbl, $login, $ghtml;

	$domname = $this->main->nname;
	$uname = $this->main->username;

	if (!$this->main->priv->isOn('php_flag'))  {
		return  "AddType application/x-httpd-php-source .php\n";
	}

	$string = null;

	lxfile_unix_chown("/home/httpd/{$domname}", "{$uname}:apache");
	lxfile_unix_chmod("/home/httpd/{$domname}", "0775");

	if (!lxfile_exists("/home/httpd/{$domname}/php.ini")) {
		// MR -- issue #650 - lxuser_cp doesn't work and change to lxfile_cp; lighttpd use lxfile_cp
		lxfile_cp("/etc/php.ini", "/home/httpd/{$domname}/php.ini");	
	}

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

	// MR -- don't need updateMainConfFile() for new structure but directly delete domain config file
//	$this->updateMainConfFile();

	$plist = array('domains', 'redirects', 'wildcards', 'exclusive');
	$bpath = "/home/apache/conf";

	foreach($plist as $k => $v) {
		lxfile_rm("{$bpath}/{$v}/{$this->main->nname}.conf");
	}

	$this->main->deleteDir();

	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
}

function clearDomainIpAddress()
{
	global $gbl, $sgbl, $login, $ghtml;

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
	
	$web_home = $sgbl->__path_httpd_root;

	$domainname = $this->main->nname;
	$log_path = "{$web_home}/{$this->main->nname}/stats";
	$cust_log = "{$log_path}/{$this->main->nname}-custom_log";
	$err_log = "{$log_path}/{$this->main->nname}-error_log";

	$v_file = "/home/apache/conf/wildcards/{$domainname}.conf";
	$string = "### No * (wildcards) for '{$domainname}' ###\n\n\n";
	lfile_put_contents($v_file, $string);

	$wcline = "\tServerAlias \\\n\t\t*.{$domainname}\n\n";

	// MR -- must set here to prevent if server_alias_a empty
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

		$string  = null;
		$string .= "<VirtualHost ";
		$string .= $this->createVirtualHostiplist("80");

		if (!$this->getServerIp()) {
			$string .= $this->createVirtualHostiplist("443");
		}

		$string .= ">\n";
		
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

		$string1 = $string;

		$string2 = "\n\n";
		
		$string = null;

		lxfile_mkdir($this->main->getFullDocRoot());

		$exclusiveip = false;

		if($this->main->priv->isOn('ssl_flag')) {

			// Do the ssl cert only if the ipaddress exists. Now when we migrate, 

			if ($this->getServerIp()) {

				$iplist = $this->getSslIpList();
				foreach($iplist as $ip) {
					$string .= "\n#### ssl virtualhost per ip {$ip} start\n";
					$ssl_cert = $this->sslsysnc($ip);
					if (!$ssl_cert) { continue; }
					$string .= "<VirtualHost ";
					$string .= "$ip:443";
					$string .= ">\n";

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

					$string .= $this->sslsysnc($ip);

					$string .= $this->middlepart($web_home, $domainname, $dirp); 

					$string .= $this->AddOpenBaseDir();

					$string .= $this->endtag();
					$string .= "#### ssl virtualhost per ip {$ip} end\n";
				}

				$exclusiveip = true;

				// --- for better appear
			//	$string = str_replace("\t", "||||", $string);
			//	$string = str_replace("\n", "\n\t", $string);
			//	$string = str_replace("||||", "\t", $string);

				$string2 = "\n\n<IfModule mod_ssl.c>\n{$string}\n</IfModule>\n\n\n";

			}
/*
			else {
				$string .= "\n#### ssl virtualhost start\n";
				$string .= "<VirtualHost \\\n";
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

				// MR -- still need first public cert especially for non 'exclusive ip'
				// ref - http://forum.lxcenter.org/index.php?t=msg&th=17211&goto=90589

				$string .= $this->sslsysnc(null);

				$string .= $this->middlepart($web_home, $domainname, $dirp); 
				$string .= $this->AddOpenBaseDir();
				$string .= $this->endtag();
				$string .= "#### ssl virtualhost end\n";
			}

			// --- for better appear
			$string = str_replace("\t", "||||", $string);
			$string = str_replace("\n", "\n\t", $string);
			$string = str_replace("||||", "\t", $string);

			$string2 = "\n\n<IfModule mod_ssl.c>\n{$string}\n</IfModule>\n\n\n";
*/	
		}
	
//		$string2 = "\n\n<IfModule mod_ssl.c>\n{$string}\n</IfModule>\n\n\n";

		$string = $string1.$string2;

		if ($c === 1) {
			$v_file = "/home/apache/conf/wildcards/{$domainname}.conf";
			lfile_put_contents($v_file, $string);
		}
		else {

			if ($exclusiveip) {
				$v_file = "/home/apache/conf/exclusive/{$domainname}.conf";

				$v2_file = "/home/apache/conf/domains/{$domainname}.conf";
				lfile_put_contents($v2_file, "### Have exclusive ip for '{$domainname}' ###\n\n\n");
			}
			else {
				$v_file = "/home/apache/conf/domains/{$domainname}.conf";

				$v2_file = "/home/apache/conf/exclusive/{$domainname}.conf";
				lfile_put_contents($v2_file, "### No exclusive ip for '{$domainname}' ###\n\n\n");
			}

			$mmaillist = $this->main->__var_mmaillist;

			foreach($mmaillist as $m) {
				if ($m['nname'] === $domainname) {
					$list = $m;
					break;
				}
			}

			// MR -- for the first time domain create

			if (!isset($list)) {
				$list = array('nname' => $domainname, 'parent_clname' => 'domain-'.$domainname, 'webmailprog' => '', 
					'webmail_url' => '', 'remotelocalflag' => 'local');
			}

			if($this->main->isOn('status')) {
				$string .= self::getCreateWebmail(array($list));
			}
			else {
				$string .= self::getCreateWebmail(array($list), $isdisabled = true);
			}

			lfile_put_contents($v_file, $string);

			$this->setAddon();		
		}
	}

	createRestartFile('apache');
}

function setAddon()
{
	global $gbl, $sgbl, $login, $ghtml;

	$string = "";

	$vaddonlist = $this->main->__var_addonlist;

	foreach((array) $vaddonlist as $v) {
		if ($v->ttype === 'redirect') {
			$string .= "<VirtualHost {$this->createVirtualHostiplist("80")}";
			$string .= "{$this->createVirtualHostiplist("443")}";
			$string .= ">\n";
			$string .= "\tServerName {$v->nname}\n";
			$string .= "\tServerAlias www.{$v->nname}\n";
			$dst = "{$this->main->nname}/{$v->destinationdir}/";
			$dst = remove_extra_slash($dst);
			$string .= "\tRedirect / \"http://{$dst}\"\n\n";
			$string .= "</VirtualHost>\n\n\n";
		}

		$domto = str_replace("domain-","", $v->parent_clname);
		$rlflag = ($v->mail_flag === 'on') ? 'remote' : 'local';

		$list = array('nname' => $v->nname, 'parent_clname' => $v->parent_clname, 'webmailprog' => '', 
			'webmail_url' => 'webmail.'.$domto, 'remotelocalflag' => $rlflag);

		if($this->main->isOn('status')) {
			$string .= self::getCreateWebmail(array($list));
		}
		else {
			$string .= self::getCreateWebmail(array($list), $isdisabled = true);
		}
	}

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "<VirtualHost {$this->createVirtualHostiplist("80")}";
		$string .= ">\n\n";
		$string .= "\tServerName {$this->main->nname}\n\n";
		$string .= "\tRedirect / \"http://www.{$this->main->nname}/\"\n\n";
		$string .= "</VirtualHost>\n\n";
	}

	if ($string === '') {
		$string = "### No domain(s) redirect to '{$this->main->nname}' ###\n\n\n";
	}

	$v_file = "/home/apache/conf/redirects/{$this->main->nname}.conf";

	lfile_put_contents($v_file, $string);
}

static function createCpConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$vstring = self::staticcreateVirtualHostiplist('80') . " ";
	$sstring = self::staticcreateVirtualHostiplist('443') . " ";

	$list = array("default" => "_default.conf", "cp" => "cp_config.conf", "disable" => "disable.conf");

	foreach($list as $config => $file) {
		$string = null;
		$string .= "<VirtualHost ";
		$string .= $vstring;
		$string .= $sstring; 
		$string .= ">\n\n";
		$string .= "\tServerName {$config}\n";
		$string .= "\tServerAlias {$config}.*\n";
		$string .= "\tDocumentRoot \"/home/kloxo/httpd/{$config}/\"\n\n";

		if ($config === "default") {
			$string .= "\t<Ifmodule mod_userdir.c>\n";
			//-- to make sure http://ip/~client work because maybe 'disabled' on httpd.conf
			//-- not work with exist * on httpd version 2.2.20/2.2.21
			$string .= "\t\tUserDir enabled\n";
			$string .= "\t\tUserDir \"public_html\"\n";
			$string .= "\t</Ifmodule>\n\n";
		}

		$string .= "\t<IfModule mod_suphp.c>\n";
		$string .= "\t\tSuPhp_UserGroup lxlabs lxlabs\n";
		$string .= "\t</IfModule>\n\n";

		$string .= "</VirtualHost>\n\n";

		$fullfile = "/home/apache/conf/defaults/{$file}";

		lfile_put_contents($fullfile, $string);

		exec("chown lxlabs:lxlabs {$fullfile}");
	}
}

static function getVipString()
{
	global $gbl, $sgbl, $login, $ghtml;

	$iplist = os_get_allips();

	foreach($iplist as $ip) {
		$vstring[] = "{$ip}:80 {$ip}:443";
	}

	$vstring = implode("", $vstring);

	return $vstring;
}

static function getCreateWebmail($list, $isdisabled = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	$vstring = self::getVipString();

	foreach($list as &$l) {
		$string = "";

		$rlflag = (!isset($l['remotelocalflag'])) ? 'local' : $l['remotelocalflag'];

		$prog = (!isset($l['webmailprog']) || ($l['webmailprog'] === '--system-default--')) ? "" : $l['webmailprog'];

		if ((!$prog) && ($rlflag !== 'remote') && (!$isdisabled)) {
			$string .= "### 'webmail.{$l['nname']}' handled by ../webmails/webmail.conf ###\n\n\n";
			continue;
		}

		$string .= "<VirtualHost {$vstring}";
		$string .= ">\n\n";
		$string .= "\tServerName webmail.{$l['nname']}\n\n";

		if ($rlflag === 'remote') {
			$l['webmail_url'] = add_http_if_not_exist($l['webmail_url']);
			$string .= "\tRedirect / \"{$l['webmail_url']}\"\n";
		} else {
			if ($isdisabled) {
				$string .= "\tDocumentRoot \"/home/kloxo/httpd/disable/\"\n\n";
			} else {
				$prog = ($l['webmailprog'] === '--chooser--') ? "" : $l['webmailprog'];

				if ($prog) {
					$string .= "\tDocumentRoot \"/home/kloxo/httpd/webmail/{$prog}/\"\n\n";
				} else {
					$string .= "\tDocumentRoot \"/home/kloxo/httpd/webmail/\"\n\n";
				}
			}

			$string .= "\t<IfModule mod_suphp.c>\n";
			$string .= "\t\tSuPhp_UserGroup lxlabs lxlabs\n";
			$string .= "\t</IfModule>\n";
		}

		$string .= "\n</VirtualHost>\n\n\n";
	}

	return $string;
}

function getDav()
{
	global $gbl, $sgbl, $login, $ghtml;

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
		$string .= "\t\t<Limit HEAD GET POST OPTIONS PROPFIND>\n";
		$string .= "\t\t\tAllow from all\n";
		$string .= "\t\t</Limit>\n";
		$string .= "\t\t<Limit MKCOL PUT DELETE LOCK UNLOCK COPY MOVE PROPPATCH>\n";
		$string .= "\t\t\tallow from all\n";
		$string .= "\t\t</Limit>\n";
		$string .= "\t\tRequire valid-user\n";
		$string .= "\t</Location>\n\n";
	}

	return $string;
}

static function createSSlConf($iplist, $domainiplist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;

	$alliplist = os_get_allips();

	foreach((array) $iplist as $ip) {

		if (!array_search_bool($ip['ipaddr'], $alliplist)) { continue; }

		// issue related to delete 'exclusive ip/domain'
		if (isset($domainiplist[$ip['ipaddr']])) {
			$v = $domainiplist[$ip['ipaddr']];
			if (($v !== '') && ($v !== '--Disabled--')) {
				continue;
			}
		}

		$string .= "\n\t<Virtualhost ";
		$string .= "{$ip['ipaddr']}:443";
		$string .= ">\n\n";
		$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);

		$ssl_root = $sgbl->__path_ssl_root;

		$certificatef = "{$ssl_root}/{$ssl_cert}.crt";
		$keyfile = "{$ssl_root}/{$ssl_cert}.key";
		$cafile = "{$ssl_root}/{$ssl_cert}.ca";

		sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

		$string .= "\t\tSSLEngine On \n";
		$string .= "\t\tSSLCertificateFile {$certificatef}\n";
		$string .= "\t\tSSLCertificateKeyFile {$keyfile}\n";
		$string .= "\t\tSSLCACertificatefile {$cafile}\n\n";
		$string .= "\t</Virtualhost>\n";
	}

	// issue #725, #760 - ssl.conf must be the first file in listing
	// MR -- so change name from ssl.conf to __ssl.conf
	
	system("rm -rf /home/apache/conf/defaults/ssl.conf");
	$sslfile = "/home/apache/conf/defaults/__ssl.conf";

	$string = "<IfModule mod_ssl.c>\n{$string}\n</IfModule>\n\n";
	$string .= "DirectoryIndex index.php index.htm default.htm default.html\n\n";

	lfile_put_contents($sslfile, $string);
}

function sslsysnc($ipad)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$ssl_root = $sgbl->__path_ssl_root;

	$ssl_cert = null;

	foreach((array) $this->main->__var_ipssllist as $ip) {
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

	$certificatef = "{$ssl_root}/{$ssl_cert}.crt";
	$keyfile = "{$ssl_root}/{$ssl_cert}.key";
	$cafile = "{$ssl_root}/{$ssl_cert}.ca";

	sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

	$string .= "\tSSLEngine On \n";
	$string .= "\tSSLCertificateFile {$certificatef}\n";
	$string .= "\tSSLCertificateKeyFile {$keyfile}\n";
	$string .= "\tSSLCACertificatefile {$cafile}\n\n";

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
	global $gbl, $sgbl, $login, $ghtml;

	if($this->main->isOn('status')) {
		$nname = $this->main->nname;

		return self::staticgetSuexecString($username, $nname);
	}
	else {
		// handling for 'disable' client
		$string  = "\n\t<IfModule mod_suphp.c>\n";
		$string .= "\t\tSuPhp_UserGroup lxlabs lxlabs\n";
		$string .= "\t</IfModule>\n\n";

		return $string;
	}
}

static function staticgetSuexecString($username, $nname = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	// issue #567 -- change '$this->main->username' to '$username' for consistence
	$string  = "\n";

	// --- mod_suexec - begin
	$string .= "\t<IfModule suexec.c>\n";
	$string .= "\t\tSuexecUserGroup {$username} {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_suexec - end

	// --- mod_suphp - begin
	// still error for http://ip/~client for other then admin
	$string .= "\t<IfModule mod_suphp.c>\n";

	$string .= "\t\tSuPhp_UserGroup {$username} {$username}\n";
	if ($username !== 'lxlabs') {
		$string .= "\t\tsuPHP_Configpath \"/home/httpd/{$nname}\"\n";
	}
	$string .= "\t</IfModule>\n\n";
	// --- mod_suphp - end

	// --- mod_ruid2 - begin - issue #566
	$string .= "\t<IfModule mod_ruid2.c>\n";
	$string .= "\t\tRMode config\n";
	$string .= "\t\tRUidGid {$username} {$username}\n";
	$string .= "\t\tRMinUidGid {$username} {$username}\n";
	// disable because problem with awstats
	// $string .= "\t\tRGroups {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- mod_ruid2 - end

	// --- httpd-itk - begin - issue #567
	// still error for http://ip/~client
	$string .= "\t<IfModule itk.c>\n";
	$string .= "\t\tAssignUserId {$username} {$username}\n";
	$string .= "\t</IfModule>\n\n";
	// --- httpd-itk - end

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

	$path = "{$this->main->getFullDocRoot()}/";

	// Issue #656 - When adding a subdomain, the Document Root field is not being validated
	// Adding quotations so that we can work with directories with spaces
	// MR -- also for other lines

	$string = null;

	if($this->main->isOn('status')) {
		$string .= "DocumentRoot \"{$path}\"\n\n";
	} else {
		if ($this->main->__var_disable_url) {
			$url = add_http_if_not_exist($this->main->__var_disable_url);
			$string .= "\tRedirect / \"{$url}\"\n\n";
		} else {
			$disableurl = "/home/kloxo/httpd/disable/";
			$string .= "\tDocumentRoot \"{$disableurl}\"\n\n";
		}
	}

	return $string;
}

function getIndexFileOrder()
{
	global $gbl, $sgbl, $login, $ghtml;

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
	global $gbl, $sgbl, $login, $ghtml;

	$string = $this->hotlink_protection();
	$stlist[] = "### Kloxo Hotlink Protection";
	$endlist[] = "### End Kloxo Hotlink Protection";
	$startstring = $stlist[0];
	$endstring = $endlist[0];
	$htfile = "{$this->main->getFullDocRoot()}/.htaccess";
	file_put_between_comments($this->main->username, $stlist, $endlist, $startstring, $endstring, $htfile, $string);
	$this->norestart = 'on';
}

function syncToPort($port, $cust_log, $err_log)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$base_root = "$sgbl->__path_httpd_root";

	$user_home = "{$this->main->getFullDocRoot()}/";
	$domname = $this->main->nname;

	$string  = null;

	// issue #656 - When adding a subdomain, the Document Root field is not being validated
	// Adding quotations so that we can work with directories with spaces
	// MR -- also for other lines

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "\tServerName www.{$domname}\n" ;
	} else {
		$string .= "\tServerName {$domname}\n" ;
	}

	$string .= "###serveralias###";
	
	$string .= "\t".$this->getBlockIP();

	$string .= $this->getDocumentRoot('www');
	$string .= "\t".$this->getIndexFileOrder();

	$string .= "\t".$this->getAwstatsString();

	$string .= "\t".$this->getSuexecString($this->main->username);

	foreach((array) $this->main->redirect_a as $red) {
		$rednname = remove_extra_slash("/{$red->nname}");

		if ($red->ttype === 'local') {
			// dkstiler changing for issue 1004 according to williams response
			//$string .= "\tAlias \"{$rednname}\" \"{$user_home}\"/{$red->redirect}\"\n";
			$string .= "\tAlias \"{$rednname}\" \"{$user_home}/{$red->redirect}\"\n";
		} else {
			if (!redirect_a::checkForPort($port, $red->httporssl)) { continue; }

			$string .= "\tRedirect \"{$rednname}\" \"{$red->redirect}\"\n";
		}
	}

	if ($this->main->__var_statsprog === 'awstats') {
		$string .= "\tRedirect /stats \"http://{$domname}/awstats/awstats.pl?config={$domname}\"\n";
		$string .= "\tRedirect /stats/ \"http://{$domname}/awstats/awstats.pl?config={$domname}\"\n\n";
	} else {
		$string .= "\tAlias /stats {$base_root}/{$domname}/webstats/\n\n";
	}

	$string .= "\tAlias /__kloxo \"/home/{$this->main->customer_name}/kloxoscript/\"\n\n";

	$string .= "\tRedirect /kloxo \"https://cp.{$domname}:{$this->main->__var_sslport}\"\n";
	$string .= "\tRedirect /kloxononssl \"http://cp.{$domname}:{$this->main->__var_nonsslport}\"\n\n";

	$string .= "\tRedirect /webmail \"http://webmail.{$domname}\"\n\n";
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

	$string .= "\t<Directory \"{$user_home}/\">\n";
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
	$string .= "\t<Directory \"{$base_root}/{$domname}/webstats/\">\n";
	$string .= "\t\tAllowOverride All\n";
	$string .= "\t</Directory>\n\n";

	if (isset($this->main->webindexdir_a)) foreach((array) $this->main->webindexdir_a as $webi) {
		$string .= "\t<Directory {$user_home}/{$webi->nname}>\n";
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
	global $gbl, $sgbl, $login, $ghtml;

	$string .= "\tProxyPass /{$app} http://localhost:{$apport}/\n";
	$string .= "\tProxyPassReverse /{$app} http://localhost:{$apport}\n";
	$string .= "\tProxyPreserveHost on\n\n";
}

function getDirIndexCore($dir)
{
	global $gbl, $sgbl, $login, $ghtml;

	$string = null;

	$dir = remove_extra_slash("/{$dir}");

	$string .= "\t<Location {$dir}>\n";
	$string .= "\t\tOptions +Indexes\n";
	$string .= "\t</Location>\n\n";

	return $string;
}

function EndTag()
{
	global $gbl, $sgbl, $login, $ghtml;

	$string  = null;

	$string .= "</VirtualHost>\n";  

	return $string;
}

function DeleteSubWeb()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$docroot = $this->main->getFullDocRoot();

	foreach ($this->main->__t_delete_subweb_a_list as $t) {
		$file = "{$docroot}/{$t->nname}";
	}
}

function createServerAliasLine()
{
	global $gbl, $sgbl, $login, $ghtml;

	// MR -- alias too long if one line (http://forum.lxcenter.org/index.php?t=msg&th=16556)

	$string  = null;
	if ($this->main->isOn('force_www_redirect')) {
		$string .= "\tServerAlias ";
	} else {
		$string .= "\tServerAlias www.{$this->main->nname}";
	}
	foreach($this->main->server_alias_a as $val) {
		// MR -- issue 674 - wildcard and subdomain problem
		if ($val->nname === '*') { continue; }

		$string .= " {$val->nname}.{$this->main->nname}";
	}

	foreach((array) $this->main->__var_addonlist as $d) {
		if ($d->ttype === 'redirect') {
			continue;
		}

		$string .= " {$d->nname} www.{$d->nname}";
	}

	$string .= "\n\n";

	return $string;
}

function denyByIp()
{
	global $gbl, $sgbl, $login, $ghtml;

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
	global $gbl, $sgbl, $login, $ghtml;

	$this->main->createDir();
	$this->createConffile();
//	$this->updateMainConfFile();

	$this->main->createPhpInfo();

	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
}

function hotlink_protection()
{
	global $gbl, $sgbl, $login, $ghtml;

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
	$ht = "/{$ht}";

	foreach($allowed_domain_list as $l) {
		$l = trim($l);

		if (!$l) { continue; }

		$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*{$l}.*$ [NC]\n";
		$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*{$l}.*$ [NC]\n";
	}

	$l = $this->main->nname;

	$string .= "\tRewriteCond %{HTTP_REFERER} !^http://.*{$l}.*$ [NC]\n";
	$string .= "\tRewriteCond %{HTTP_REFERER} !^https://.*{$l}.*$ [NC]\n";
	$string .= "\tRewriteRule .*[JrRjP][PpdDAa][GfFgrR]$|.*[Gg][Ii][Ff]$ {$ht} [L]\n";

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
		$webmaildefpath = "{$webmaildef}/";
	}

	$webdata  = null;
	$webdata .= "<VirtualHost ";
	$webdata .= self::staticcreateVirtualHostiplist("80") . " ";
	$webdata .= self::staticcreateVirtualHostiplist("443") . " ";
	$webdata .= ">\n\n";
	$webdata .= "\tServerName webmail\n";
	$webdata .= "\tServerAlias webmail.*\n\n";
	$webdata .= "\tDocumentRoot \"{$sgbl->__path_kloxo_httpd_root}/webmail/{$webmaildefpath}\"\n\n";

	$webdata .= "\t<IfModule mod_suphp.c>\n";
	$webdata .= "\t\tSuPhp_UserGroup lxlabs lxlabs\n";
	$webdata .= "\t</IfModule>\n\n";

	$webdata .= "</VirtualHost>\n\n";

	$webmailfile = "/home/apache/conf/webmails/webmail.conf";

	lfile_put_contents($webmailfile, $webdata);
}

function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml;

	$this->addDomain();
	$this->main->doStatsPageProtection();
}

function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml;

	$this->delDomain();
}

function dosyncToSystemPost()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!$this->isOn('norestart')) {
		createRestartFile("apache");
	}
}

function addAllSubweb()
{
	global $gbl, $sgbl, $login, $ghtml;

	$this->AddSubWeb($this->subweb_a);
}

function AddSubWeb($list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$web_home = $sgbl->__path_httpd_root;
	$base_root = $sgbl->__path_httpd_root;

	$user_home = "{$this->main->getFullDocRoot()}/";

	foreach((array) $list as $subweb) {
		lxfile_mkdir("{$user_home}/subdomains/{$subweb->nname}");
	}
}

function fullUpdate()
{
	global $gbl, $sgbl, $login, $ghtml;

	$domname = $this->main->nname;
	$uname = $this->main->username;

	$hroot = $sgbl->__path_httpd_root;
	$droot = $this->main->getFullDocRoot();

	lxfile_mkdir("{$hroot}/{$domname}/webstats");

	$this->main->createPhpInfo();
	web::createstatsConf($domname, $this->main->stats_username, $this->main->stats_password);

	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);

	$this->createConffile();

	// Removed recursive
	lxfile_unix_chown("{$droot}/", "{$uname}:{$uname}");

	lxfile_unix_chmod("{$droot}/", "0755");
	lxfile_unix_chmod("{$droot}", "0755");
	lxfile_unix_chown("{$hroot}/{$domname}", "{$uname}:apache");
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

		case "changeowner":
			$this->main->webChangeOwner();
			$this->createConffile();
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

		case "fixipdomain":
			$this->createConffile();
			$this->updateMainConfFile();
			self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
			break;

		case "enable_php_manage_flag":
			$this->createConffile();
			$this->updateMainConfFile();
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

		case "static_config_update":
			self::createCpConfig();
			self::createWebDefaultConfig();
			$this->updateMainConfFile();
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
