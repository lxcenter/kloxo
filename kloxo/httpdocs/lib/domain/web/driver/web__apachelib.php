<?php 

class web__apache extends lxDriverClass {


//######################################### SyncToSystem Starts Here

static function uninstallMe()
{
	lxshell_return("service", "httpd", "stop");
	lxshell_return("rpm", "-e", "--nodeps", "httpd");
}

static function installMe()
{
	$ret = lxshell_return("yum", "-y", "install", "httpd", "mod_ssl");
	if ($ret) { throw new lxexception('install_httpd_failed', 'parent'); }
	lxshell_return("chkconfig", "httpd", "on");
	addLineIfNotExistInside("/etc/httpd/conf/httpd.conf", "Include /etc/httpd/conf/kloxo/kloxo.conf", "");
	lxshell_return("__path_php_path", "../bin/misc/installsuphp.php");
	//lxshell_return("__path_php_path", "../bin/fix/fixfrontpage.php");
	createRestartFile("apache");
}

function updateIpConfFile()
{
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
}


function updateMainConfFile()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$virtual_file = "$sgbl->__path_apache_path/kloxo/virtualhost.conf";
	$init_file = "$sgbl->__path_apache_path/kloxo/init.conf";
	$vdomlist = $this->main->__var_vdomain_list; 
	$iplist = $this->main->__var_ipaddress;
	$fdata = null;
	foreach($iplist as $ipaddr){
		$ip = trim($ipaddr['ipaddr']);
		if ($ip) {
			$fdata .= "NameVirtualHost {$ip}:80\n\n";
			$fdata .= "NameVirtualHost {$ip}:443\n\n";
		}
	}



	lfile_put_contents($init_file, $fdata);
    $fdata = null;

	$vdomlist = merge_array_object_not_deleted($vdomlist, $this->main);

	foreach((array) $vdomlist as $dom) {
		if (array_search_bool($dom['nname'], $this->main->__var_domainipaddress)) { continue; }
		if (lxfile_exists("{$sgbl->__path_httpd_root}/{$dom['nname']}/conf/kloxo.{$dom['nname']}")) {
			$fdata .= "Include {$sgbl->__path_httpd_root}/{$dom['nname']}/conf/kloxo.{$dom['nname']}\n\n";
		}
	}
	/// Start agiain....

	$fdata .= "Alias /awstatscss \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/css/\"\n";
	$fdata .= "Alias /awstatsicons \"{$sgbl->__path_home_root}/httpd/awstats/wwwroot/icon/\"\n\n";


	// Forward domains are added at the end. This makes sure that the ssl domains - which would configured as virtual domains - would work fine.
	if (!lfile_exists("__path_apache_path/kloxo/forward/")) {
		lxfile_mkdir("__path_apache_path/kloxo/forward/");
	}

	if (!lfile_exists("__path_apache_path/kloxo/forward/forwardhost.conf")) {
		lxfile_touch("__path_apache_path/kloxo/forward/forwardhost.conf");
	}

	lfile_put_contents($virtual_file, $fdata);

	$this->updateIpConfFile();
	
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
	$string = "\\\n";
	if ($this->getServerIp()) {
		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue; }
			$string .= "          {$ip}:{$port}\\\n";
		}
		return $string;
	}
	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$string .= "          {$ip}:{$port}\\\n";
	}
	return $string;
}

static function staticcreateVirtualHostiplist($port)
{
	$string = "";
	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$string .= "          {$ip}:{$port}\\\n";
	}
	return $string;
}

function addSendmail()
{
	$sendmailstring = "php_admin_value sendmail_path  \"/usr/sbin/sendmail -t -i -f postmaster@{$this->main->nname}\"\n";
	$string  = "\n\n<IfModule sapi_apache2.c>\n";

	$string .= $sendmailstring;
	$string .= "</IfModule>\n\n";

	$string  = "\n\n<IfModule mod_php5.c>\n";
	$string .= $sendmailstring;

	$string .= "</IfModule>\n\n";
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
	$corepath = "$sgbl->__path_customer_root/{$this->main->customer_name}/";

	$path = "{$sgbl->__path_httpd_root}/{$this->main->nname}/httpdocs:{$sgbl->__path_httpd_root}/{$this->main->nname}/{$this->main->nname}:$corepath";

	$openbasdstring = "php_admin_value open_basedir \"{$path}:{$adminbasedir}/tmp:/usr/share/pear:/var/lib/php/session/:/home/kloxo/httpd/script:/home/kloxo/httpd/webmail\"\n";

	$string = "<Location />\n";
	$string .= "<IfModule sapi_apache2.c>\n";
	$string .= $openbasdstring;
	$string .= "</IfModule>\n";
	$string .= "<IfModule mod_php5.c>\n";
	$string .= $openbasdstring;
	$string .= "</IfModule>\n";

	$string .= "</Location>\n";
	return $string;

}


function getBlockIP()
{
	$t = trimSpaces($this->main->text_blockip);
	$t = trim($t);
	if (!$t) { return; }
	$t = str_replace(".*", "", $t);
	$string = null;
	$string .= "<Location />\n";
	$string .= "Order allow,deny\n";
	$string .= "deny from $t\n";
	$string .= "allow from all\n";
	$string .= "</Location>\n";
	return $string;
}

function disablePhp()
{

	if (!$this->main->priv->isOn('php_flag'))  {
		return  "AddType application/x-httpd-php-source .php\n";
	}

	$string = null;
	lxfile_unix_chown("/home/httpd/{$this->main->nname}", "{$this->main->username}:apache");
	lxfile_unix_chmod("/home/httpd/{$this->main->nname}", "0775");
	if (!lxfile_exists("/home/httpd/{$this->main->nname}/php.ini")) {
		lxuser_cp($this->main->username, "/etc/php.ini", "/home/httpd/{$this->main->nname}/php.ini");
	}
	$string .= "<IfModule mod_suphp.c>\n";
	$string .= "suPHP_Configpath /home/httpd/{$this->main->nname}\n";
	$string .= "</IfModule>\n";

	return $string;
}


function delDomain()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	// Very important. If the nname is null, then the 'rm -rf' command will delete all the domains. So please be carefule here. Must find a better way to delete stuff.
	if (!$this->main->nname) {
		return;
	}


	$this->updateMainConfFile();
	$this->main->deleteDir();
	$this->updateIpConfFile();
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

	$web_home = "$sgbl->__path_httpd_root";
	$domainname = $this->main->nname;
	$log_path = $web_home . "/{$this->main->nname}/stats"; 
	$cust_log 	= $log_path . "/". $this->main->nname . "-" . "custom_log"; 
	$err_log 	= $log_path ."/". $this->main->nname . "-" . "error_log";
	$v_file = "$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.{$this->main->nname}";

	$string = null;

	$dirp = $this->main->__var_dirprotect;
	
	$this->clearDomainIpAddress();


	$string = null;
	$string = "<VirtualHost  {$this->createVirtualHostiplist("80")}>\n";
	$string .= $this->syncToPort("80", $cust_log, $err_log);
	$string .= $this->middlepart($web_home, $domainname, $dirp); 
	$string .= $this->AddOpenBaseDir();
	$string .= $this->endtag();

	lxfile_mkdir($this->main->getFullDocRoot());

	if($this->main->priv->isOn('ssl_flag'))	 {

		// Do the ssl cert only if the ipaddress exists. Now when we migrate, 

			$string .= "\n\n\n\n<IfModule mod_ssl.c>\n";


			if ($this->getServerIp()) {
				$iplist = $this->getSslIpList();
				foreach($iplist as $ip) {
					$string .= "#### ssl virtualhost per ip\n";
					$ssl_cert = $this->sslsysnc($ip);
					if (!$ssl_cert) { continue; }
					$string .= "<VirtualHost $ip:443>\n";
					$string .= $this->syncToPort("443", $cust_log, $err_log);
					$string .= $this->sslsysnc($ip);
					$string .= $this->middlepart($web_home, $domainname, $dirp); 
					$string .= $this->AddOpenBaseDir();
					$string .= $this->endtag();
					$string .= "#### ssl virtualhost per ip $ip end\n\n\n";
				}
			} else {
				$string .= "#### ssl virtualhost per ip\n";
				$string .= "<VirtualHost  {$this->createVirtualHostiplist("443")}>\n";
				$string .= $this->syncToPort("443", $cust_log, $err_log);
				$string .= $this->sslsysnc(null);
				$string .= $this->middlepart($web_home, $domainname, $dirp); 
				$string .= $this->AddOpenBaseDir();
				$string .= $this->endtag();
				$string .= "#### ssl virtualhost per ip end\n\n\n";
			}

			$string .= "</IfModule>\n";
	}
	



	$string .= $this->getAddon();

	lfile_put_contents($v_file, $string);

}

function getAddon()
{
	$string = null;
	foreach((array) $this->main->__var_addonlist as $v) {
		if ($v->ttype !== 'redirect') {
			continue;
		}
		$string .= "<VirtualHost {$this->createVirtualHostiplist("80")}>\n";
		$string .= "Servername {$v->nname}\n";
		$string .= "ServerAlias www.{$v->nname}\n";
		$dst = "{$this->main->nname}/{$v->destinationdir}/";
		$dst = remove_extra_slash($dst);
		$string .= "Redirect / http://$dst\n";
		$string .= "</VirtualHost>\n\n";
	}

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "<VirtualHost {$this->createVirtualHostiplist("80")}>\n";
		$string .= "Servername {$this->main->nname}\n";
		$string .= "Redirect / http://www.{$this->main->nname}/\n";
		$string .= "</VirtualHost>\n\n";

		$string .= "\n\n\n\n<IfModule mod_ssl.c>\n";
		$string .= "<VirtualHost {$this->createVirtualHostiplist("443")}>\n";
		$string .= "Servername {$this->main->nname}\n";
		$string .= "Redirect / https://www.{$this->main->nname}\n";
		$string .= "</VirtualHost>\n\n";
		$string .= "\n<IfModule mod_ssl.c>\n\n";

	}

	return $string;
}


function createCpConfig()
{

	$vstring = web__apache::getVipString();
	$string = null;
	$string .= "\n<VirtualHost {$vstring}>\n";
	$string .= "servername cp\n";
	$string .= "serveralias cp.*\n";
	$string .= "DocumentRoot /home/kloxo/httpd/script/cp\n";
	$string .= "<IfModule mod_suphp.c>\n";
	$string .= "SuPhp_UserGroup lxlabs lxlabs\n";
	$string .= "</Ifmodule>\n";
	$string .= "</VirtualHost>\n";

	$file = "/etc/httpd/conf/kloxo/cp_config.conf";
	lfile_put_contents($file, $string);
}

static function getVipString()
{
	$iplist = os_get_allips();
	foreach($iplist as $ip) {
		$vstring[] = "   {$ip}:80\\\n";
	}
	$vstring = implode("", $vstring);
	return $vstring;
}

static function createWebmailRedirect($list)
{

	$vstring = web__apache::getVipString();
	dprintr($vstring);
	$string = null;
	foreach($list as $l) {
		$string .= "\n<VirtualHost  {$vstring}>\n";
		$string .= "servername webmail.{$l['nname']}\n";
		if ($l['remotelocalflag'] === 'remote') {
			$l['webmail_url'] = add_http_if_not_exist($l['webmail_url']);
			$string .= "Redirect / {$l['webmail_url']}\n";
		} else {

			if (is_disabled($prog)) {
				$string .= "DocumentRoot /home/kloxo/httpd/webmail/disabled/\n";
			} else {
				$string .= "DocumentRoot /home/kloxo/httpd/webmail/\n";
			}

			$prog = ($l['webmailprog'] == '--chooser--')? "": $l['webmailprog'];
			if ($prog) {
				$string .= "DirectoryIndex redirect-to-$prog.php index.php index.html\n";
			}
			$string .= "<Ifmodule mod_suphp.c>\n";
			//$string .= "SuPhp_UserGroup {$l['systemuser']} {$l['systemuser']}\n";
			$string .= "SuPhp_UserGroup lxlabs lxlabs\n";
			$string .= "</Ifmodule>\n";

		}
		$string .= "</VirtualHost>\n\n";
	}

	lfile_put_contents("/etc/httpd/conf/kloxo/webmail_redirect.conf", $string);
	createRestartFile('apache');
}



function getDav()
{
	
	$string = null;
	$bdir = "/home/httpd/{$this->main->nname}/__webdav";
	lxfile_mkdir($bdir);
	foreach($this->main->__var_davuser as $k => $v) {
		$file = get_file_from_path($k);
		$file = "{$bdir}/{$file}";
		$string .= "<Location {$k}>\n";
		$string .= "DAV On\n";
		$string .= "AuthType Basic\n";
		$string .= "AuthName \"WebDAV Restricted\"\n";
		$string .= "AuthUserFile {$file}\n";
		//$string .= "<LimitExcept GET HEAD OPTIONS>\n";
		$string .= "<Limit HEAD GET POST OPTIONS PROPFIND>\n";
		$string .= "Allow from all\n";
		$string .= "</Limit>\n";
		$string .= "<Limit MKCOL PUT DELETE LOCK UNLOCK COPY MOVE PROPPATCH>\n";
		$string .= "allow from all\n";
		$string .= "</Limit>\n";
		$string .= "Require valid-user\n";
		//$string .= "</LimitExcept>\n";
		$string .= "</Location>\n";
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
	$v_dir 		= $web_path . "/{$this->main->nname}/conf";
	$log_path 	= $web_path . "/{$this->main->nname}/stats";
	$log_path1 	= $log_path . "/logs";
	$cust_log 	= $log_path1 . "/{$this->main->nname}-custom_log"; 
	$err_log 	= $log_path1 ."/{$this->main->nname}-error_log";
	$awstat_conf 	= "$sgbl->__path_real_etc_root/awstats/";
	$awstat_dirdata 	= "$sgbl->__path_kloxo_httpd_root/awstats/";
	$user_home = "{$this->main->getFullDocRoot()}/";
	return;
    
	 
	if ($this->main->priv->isOn('frontpage_flag')) {

		$htaccessstring = null;
		$htaccessstring .= "";
		$web_path = "$sgbl->__path_httpd_root/";
		$for_file ="$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.frontpage.{$this->main->nname}";
		//$for_file = lx_tmp_file("{$this->main->nname}_frontpage");

		$extra  = "ServerRoot  \"/etc/httpd/\"";
		$extra .= "\n";
		$extra .= $this->syncToPort("80", "ttt", "ttt", true);
		$extra .= "</VirtualHost>";
		lfile_put_contents($for_file, $extra);
		$password = $this->main->__var_sysuserpassword['realpass']? $this->main->__var_sysuserpassword['realpass']: 'something';

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
		$for_file ="$sgbl->__path_httpd_root/{$this->main->nname}/conf/kloxo.frontpage.{$this->main->nname}";
		lunlink($for_file);

	}

	return $string;
}


static function createSSlConf($iplist, $domainiplist)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;
	$alliplist = os_get_allips();
	dprintr($domainiplist);
	foreach((array) $iplist as $ip) {
		if (!array_search_bool($ip['ipaddr'], $alliplist)) {
			continue;
		}

		// Skip if it is in the domain ip list. We need to create it only for the ipaddresses that do not have domains set for them. Don't skip. The ssl is loaded first. The only issue is that https://ip will show default apache page.
		if (isset($domainiplist[$ip['ipaddr']]) && $domainiplist[$ip['ipaddr']]) {
			continue;
		}
		$string .= "\n<Virtualhost {$ip['ipaddr']}:443>\n";
		$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);

		$certificatef = "{$sgbl->__path_ssl_root}/{$ssl_cert}.crt";
		$keyfile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.key";
		$cafile = "{$sgbl->__path_ssl_root}/{$ssl_cert}.ca";

		sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

		$string .= "SSLEngine On \n";
		$string .= "SSLCertificateFile {$certificatef}\n";
		$string .= "SSLCertificateKeyFile {$keyfile}\n";
		$string .= "SSLCACertificatefile {$cafile}\n";
		$string .= "</Virtualhost>\n";
	}

	//	$string .= "SSLLogFile /\n";
	$sslfile = "/etc/httpd/conf/kloxo/ssl.conf";
	$string = "<IfModule mod_ssl.c>\n {$string}\n</IfModule>\n\n";
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

	$string .= "SSLEngine On \n";
	$string .= "SSLCertificateFile {$certificatef}\n";
	$string .= "SSLCertificateKeyFile {$keyfile}\n";

	$string .= "SSLCACertificatefile {$cafile}\n";

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
			$string .= "ErrorDocument {$num} {$nv}\n";
		}
	}

	$string .= $this->disablePhp();

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
	$string = null;

	// http://project.lxcenter.org/issues/74
	$path = remove_extra_slash("\"/{$path}\"");

	$string .= "<Location {$path}>\n";
	$string .= "AuthType Basic\n";
	$string .= "AuthName \"{$authname}\"\n";

	// http://project.lxcenter.org/issues/74
	$string .= "AuthUserFile \"{$sgbl->__path_httpd_root}/{$this->main->nname}/__dirprotect/{$file}\"\n";

	$string .= "require  valid-user\n";
	$string .= "</Location>\n";
	return $string;
}

function getSuexecString($username)
{
	$string = "\n";
	// --- mod_suexec - begin
	$string .= "<IfModule suexec.c>\n";
	$string .= "SuexecUserGroup {$this->main->username} {$this->main->username}\n";
	$string .= "</IfModule>\n\n";
	// --- mod_suexec - end

	// --- mod_suphp - begin
	$string .= "<IfModule mod_suphp.c>\n";

/* --- too much code and overlap with suphp.conf (http://project.lxcenter.org/issues/563)
	$string .= "AddType application/x-httpd-php .php\n";
	$string .= "RemoveHandler .php\n";
	$string .= "<FilesMatch \"\.php$\" >\n";
	$string .= "SetHandler x-httpd-php\n";
	$string .= "</FilesMatch>\n";
	$string .= "<Location />\n";
	$string .= "suPHP_AddHandler x-httpd-php \n";
	$string .= "</Location>\n";
--- */

	$string .= "SuPhp_UserGroup {$this->main->username} {$this->main->username}\n";
	$string .= "</IfModule>\n\n";
	// --- mod_suphp - end

	// --- mod_ruid2 - begin - http://project.lxcenter.org/issues/566
	$string .= "<IfModule mod_ruid2.c>\n";
	$string .= "RMode config\n";
	$string .= "RUidGid {$this->main->username} {$this->main->username}\n";
	$string .= "RMinUidGid {$this->main->username} {$this->main->username}\n";
	$string .= "RGroups {$this->main->username}\n";
	$string .= "</IfModule>\n\n";
	// --- mod_ruid2 - end

	// --- httpd-itk - begin - http://project.lxcenter.org/issues/567
	$string .= "<IfModule itk.c>\n";
	$string .= "AssignUserId {$this->main->username} {$this->main->username}\n";
	$string .= "</IfModule>\n\n";
	// --- httpd-itk - end

	return $string;
}

function getAwstatsString()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;
	$string .= "ScriptAlias     /awstats/  {$sgbl->__path_kloxo_httpd_root}/awstats/wwwroot/cgi-bin/\n";
	if ($this->main->stats_password) {
		$string .= $this->getDirprotectCore("Awstats", "/awstats", "__stats");
	}
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);
	return $string;
}

function getDocumentRoot($subweb)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$base_root = "$sgbl->__path_httpd_root";
	$web_home = "$sgbl->__path_httpd_root";

	$path = "{$this->main->getFullDocRoot()}/";

	$string = null;
	if($this->main->isOn('status')) {
		$string .= "DocumentRoot   {$path}\n";
	} else {
		if ($this->main->__var_disable_url) {
			$url = add_http_if_not_exist($this->main->__var_disable_url);
			$string .= "Redirect / {$url}\n";
		} else {
			$disableurl = "/home/kloxo/httpd/disable/";
			$string .= "DocumentRoot {$disableurl}\n";
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
	$string = null;

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "servername    www.{$this->main->nname}\n" ;
	} else {
		$string .= "servername    {$this->main->nname}\n" ;
	}

	$string .= $this->createServerAliasLine();
	$domname = $this->main->nname;
	
	//$string .= $this->hotlink_protection();
	$string .= $this->getBlockIP();


	$string .= $this->getDocumentRoot('www');
	$string .= $this->getIndexFileOrder();

	$string .= $this->getAwstatsString();

	$string .= $this->getSuexecString($this->main->username);
	foreach((array) $this->main->redirect_a as $red) {
		$rednname = remove_extra_slash("/{$red->nname}");
		if ($red->ttype === 'local') {
			$string .= "Alias {$rednname} {$user_home}/{$red->redirect}\n";
		} else {
			if (!redirect_a::checkForPort($port, $red->httporssl)) { continue; }
			$string .= "Redirect {$rednname} {$red->redirect}\n";
		}
	}

	if ($this->main->__var_statsprog === 'awstats') {
		$string .= "Redirect     /stats http://$domname/awstats/awstats.pl?config=$domname\n";
		$string .= "Redirect     /stats/ http://$domname/awstats/awstats.pl?config=$domname\n";
	} else {
		$string .= "Alias     /stats  {$sgbl->__path_httpd_root}/{$domname}/webstats/\n";
	}
	$string .= "Alias     /__kloxo  /home/{$this->main->customer_name}/kloxoscript\n";
	$string .= "Redirect     /kloxononssl  http://cp.{$this->main->nname}:{$this->main->__var_nonsslport}\n";
	$string .= "Redirect     /kloxo	https://cp.{$this->main->nname}:{$this->main->__var_sslport}\n";
	$string .= "Redirect     /webmail	https://webmail.{$this->main->nname}\n";
	$string .= "<Directory /home/httpd/{$domname}/kloxoscript>\n";
	$string .= "AllowOverride All\n";
	$string .= "</Directory>\n";


	$string .= $this->addSendmail();

	if ($this->main->priv->isOn('cgi_flag')) {
		$string .= "ScriptAlias     /cgi-bin/   {$user_home}/cgi-bin/\n";
	}
	if ($port === '80') {
		$string .= "CustomLog      {$cust_log} combined  \n";
		$string .= "ErrorLog       {$err_log}\n";
	}

	// hack for frontpage. It needs the proper directory.
	if ($frontpage) {
		$string .= "<Directory {$this->main->getFullDocRoot()}/>\n";
		$string .= "AllowOverride All\n";
		$string .= "</Directory>\n";
	} else {
		$string .= "<Directory {$this->main->getFullDocRoot()}/>\n";
		$string .= "AllowOverride All\n";
		$string .= "</Directory>\n";
		$string .= "<Location />\n";
		$extrastring = null;
		if (isset($this->main->webmisc_b)) {
			if ($this->main->webmisc_b->isOn('execcgi')) {
				$extrastring .= "+ExecCgi";
			}
			if ($this->main->webmisc_b->isOn('dirindex')) {
				$extrastring .= " +Indexes";
			}
		}

		$string .= "Options +Includes +FollowSymlinks {$extrastring}\n";

		if (isset($this->main->webmisc_b) && $this->main->webmisc_b->isOn('execcgi')) {
			$string .= "AddHandler cgi-script .cgi\n";
		}

		$string .= "</Location>\n";
		$string .= "<Directory {$sgbl->__path_httpd_root}/{$this->main->nname}/webstats>\n";
		$string .= "AllowOverride All\n";
		$string .= "</Directory>\n";
	}

	if (isset($this->main->webindexdir_a)) foreach((array) $this->main->webindexdir_a as $webi) {
		$string .= "<Directory {$this->main->getFullDocRoot()}/{$webi->nname}>\n";
		$string .= "AllowOverride All\n";
		$string .= "Options +Indexes\n";
		$string .= "</Directory>\n";
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
	$string .= "ProxyPass /$app http://localhost:$apport/\n";
	$string .= "ProxyPassReverse /$app http://localhost:$apport\n";
	$string .= "ProxyPreserveHost on\n";
}

function getDirIndexCore($dir)
{
	$string = null;
	$dir = remove_extra_slash("/{$dir}");
	$string .= "<Location {$dir}>\n";
	$string .= "Options +Indexes\n";
	$string .= "</Location>\n";
	return $string;
}

function EndTag()
{
	$string = null;
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

	lxfile_mkdir( "__path_apache_path/kloxo/forward/");
	lxfile_touch("__path_apache_path/kloxo/forward/{$this->main->nname}");
	lxfile_touch("__path_apache_path/kloxo/forward/forwardhost.conf");
	//lxfile_unix_chmod("__path_apache_path/kloxo/forward/{$this->main->nname}", "0710");
}



function createServerAliasLine()
{
	$string = null;
	if ($this->main->isOn('force_www_redirect')) {
		$string .= "ServerAlias ";
	} else {
		$string .= "ServerAlias www.{$this->main->nname}";
	}
	foreach($this->main->server_alias_a as $val) {
		$string .= " {$val->nname}.{$this->main->nname}";
	}

	foreach((array) $this->main->__var_addonlist as $d) {
		if ($d->ttype === 'redirect') {
			continue;
		}
		$string .= " {$d->nname} www.{$d->nname}";
	}

	$string .= "\n";

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

	return;
	$forwardincludefile = "$sgbl->__path_apache_path/kloxo/forward/forwardhost.conf";
	$result = $this->main->__var_fdomain_list;
	$fdata = null;
	$result = merge_array_object_not_deleted($result, $this->main);
	foreach((array) $result as $dom){
		if ($dom['nname'] === $this->main->nname) {
			continue;
		}
	}

	lfile_put_contents($forwardincludefile, $fdata);
}


function denyByIp()
{
	$string = null;
	$string .= "<Ifmodule mod_access.c>\n";
	$string .= "<Location />\n";
	$string .= "Order Allow,Deny\n";
	$string .= "Deny from 6.28.130.\n";
	$string .= "Allow from all\n";
	$string .= "</Location>\n";
	$string .= "</Ifmodule>\n";
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

	dprint(getcwd());
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

	$string = null;
	$string .= "RewriteEngine on\n";
	$string .= "RewriteCond %{HTTP_REFERER} !^$\n";

	$ht = trim($this->main->hotlink_redirect, "/");
	$ht = "/$ht";
	foreach($allowed_domain_list as $l) {
		$l = trim($l);
		if (!$l) { continue; }
		$string .= "RewriteCond %{HTTP_REFERER} !^http://.*$l.*$ [NC]\n";
		$string .= "RewriteCond %{HTTP_REFERER} !^https://.*$l.*$ [NC]\n";
	}
	$l = $this->main->nname;
	$string .= "RewriteCond %{HTTP_REFERER} !^http://.*$l.*$ [NC]\n";
	$string .= "RewriteCond %{HTTP_REFERER} !^https://.*$l.*$ [NC]\n";
	$string .= "RewriteRule .*[JrRjP][PpdDAa][GfFgrR]$|.*[Gg][Ii][Ff]$ $ht [L]\n";

	return $string;
}

static function createWebmailConfig()
{
	global $gbl, $sgbl, $login, $ghtml; 


	$fdata = null;

	$fdata .= "<VirtualHost  \\\n";

	$fdata .= web__apache::staticcreateVirtualHostiplist("80");
	$fdata .= "                   >\n";

	$defaultdata = $fdata;
	$defaultdata .= "DocumentRoot {$sgbl->__path_kloxo_httpd_root}/default/\n";
	$defaultdata .= "servername   default\n";
	$defaultdata .= "ServerAlias   default.*\n";
	$defaultdata .= "<Ifmodule mod_userdir.c>\n";
	$defaultdata .= "Userdir \"public_html\"\n";
	$defaultdata .= "</Ifmodule>\n";
	$defaultdata .= "</VirtualHost>\n\n\n";

	$defaultfile = "$sgbl->__path_apache_path/kloxo/default.conf";
	lfile_put_contents($defaultfile, $defaultdata);


	$webdata = null;
	$webdata .= "<VirtualHost  \\\n";
	$webdata .= web__apache::staticcreateVirtualHostiplist("80");
	$webdata .= web__apache::staticcreateVirtualHostiplist("443");
	$webdata .= "                   >\n";
	$webdata .= "DocumentRoot {$sgbl->__path_kloxo_httpd_root}/webmail/\n";
	$webdata .= "servername   webmail\n";
	$webdata .= "ServerAlias   webmail.*\n";
	$webdata .= "<Ifmodule mod_suphp.c>\n";
	$webdata .= "SuPhp_UserGroup lxlabs lxlabs\n";
	$webdata .= "</Ifmodule>\n";
	$webdata .= "</VirtualHost>\n\n\n";
	$webmailfile = "__path_real_etc_root/httpd/conf/kloxo/webmail.conf";

	lfile_put_contents($webmailfile, $webdata);


	createRestartFile("apache");

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
	//lxshell_return("/etc/init.d/httpd", "reload");
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
	//lfile_put_contents("$user_home/{$subweb->nname}/index.html", "Subdomain Created by Kloxo");
	}

}


function fullUpdate()
{

	$domname = $this->main->nname;
	lxfile_mkdir("__path_httpd_root/$domname/webstats");

	$this->main->createPhpInfo();
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);

	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
	if ($this->main->ttype === 'forward') {
		$this->createForwardconf();
	} else {
		$this->createConffile();
		$this->frontPageEnable();
		$this->updateMainConfFile();
	}
	lxfile_unix_chown_rec("{$this->main->getFullDocRoot()}/", "{$this->main->username}:{$this->main->username}");
	lxfile_unix_chmod("{$this->main->getFullDocRoot()}/", "0755");
	lxfile_unix_chmod("{$this->main->getFullDocRoot()}", "0755");
	lxfile_unix_chown("__path_httpd_root/{$this->main->nname}", "{$this->main->username}:apache");
	self::createWebmailConfig();
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
	$fullpath = "$sgbl->__path_customer_root/{$this->main->customer_name}/";

	$this->main->do_restore($docd);

	lxfile_unix_chown_rec($fullpath, $this->main->username);

}


}
