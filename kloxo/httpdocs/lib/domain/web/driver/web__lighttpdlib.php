<?php 

class web__lighttpd extends lxDriverClass {


//######################################### SyncToSystem Starts Here

static function uninstallMe()
{
	lxshell_return("service",  "lighttpd", "stop");
	lxshell_return("rpm", "-e", "--nodeps", "lighttpd");
	lunlink("/etc/init.d/lighttpd");
}

static function installMe()
{
	$ret = lxshell_return("yum", "-y", "install", "lighttpd", "lighttpd-fastcgi");
	if ($ret) { throw new lxexception('install_lighttpd_failed', 'parent'); }
	lxshell_return("chkconfig", "lighttpd", "on");
	lxfile_mkdir("/etc/lighttpd/");
	lxfile_mkdir("/etc/lighttpd/conf/kloxo");
	lxfile_cp("../file/lighttpd/lighttpd.conf", "/etc/lighttpd/lighttpd.conf");
	lxfile_cp("../file/lighttpd/conf/kloxo/kloxo.conf", "/etc/lighttpd/conf/kloxo/kloxo.conf");
	lxfile_cp("../file/lighttpd/conf/kloxo/webmail.conf", "/etc/lighttpd/conf/kloxo/webmail.conf");
	lxfile_cp("../file/lighttpd/etc_init.d", "/etc/init.d/lighttpd");
	lxfile_unix_chmod("/etc/init.d/lighttpd", "0755");
	lxfile_unix_chmod("/etc/init.d/lighttpd", "0755");
	lxfile_mkdir("/home/kloxo/httpd/lighttpd");
	lxfile_unix_chown("/home/kloxo/httpd/lighttpd", "apache");
	createRestartFile("lighttpd");
}


function updateIpConfFile()
{
	$fdata = "\n";
	$donelist = array();
	foreach((array) $this->main->__var_domainipaddress as $ip => $dom) {
		if (!lxfile_exists("/etc/lighttpd/conf/kloxo/lighttpd.$dom")) { continue; }
		if ($dom === $this->main->nname && $this->main->isDeleted()) { continue; }
		if (array_search_bool($dom, $donelist)) { continue; }
		$donelist[] = $dom;
		$fdata .= "include \"conf/kloxo/lighttpd.$dom\"\n\n";
	}
	lfile_put_contents("/etc/lighttpd/conf/kloxo/domainip.conf", $fdata);
}


function getRailsConf($app)
{

	if ($this->isRailsDocroot() && !$app->isOn('accessible_directly')) {
		return '';
	}
	$appname = $app->appname;

	$appurl = null;
	if (!$app->isOn('accessible_directly')) {
		$appurl = "/$appname";
	}

	if ($app->priv->rubyfcgiprocess_num > 0) {
		$proc = $app->priv->rubyfcgiprocess_num;
	} else {
		$proc = 1;
	}
	$basepath = "/home/{$this->main->customer_name}/ror/{$this->main->nname}/";
	$uid = os_get_uid_from_user($this->main->username);
	$gid = os_get_gid_from_user($this->main->username);

	$string = null;
	if (!$app->isOn('accessible_directly')) {
		$string .= "\$HTTP[\"url\"] =~ \"^/$appname\" {\n";
	}
	$string .= "	server.document-root = \"$basepath/$appname/public/\"\n";

	if (!$app->isOn('accessible_directly')) {
		//$string .= "	alias.url = ( \"$appurl/\" => \"$basepath/$appname/public/\" )\n";
	} else {
		$string .= "	alias.url += ( \"$appurl/\" => \"$basepath/$appname/public/\" )\n";
	}

	$string .= "	server.error-handler-404 = \"$appurl/dispatch.fcgi\"\n";
	$string .= "	fastcgi.server  = (\".fcgi\" =>(( \"socket\" => \"/tmp/ror.socket.mick.com.$appname.\" + var.PID,\n";
	$string .= "	\"bin-path\" => \"/usr/bin/lxsuexec\",\n";
	$string .= "\"min-procs\" => 0,\n";
	$string .= "\"max-procs\" => $proc,\n";
	$string .= "   \"bin-environment\" => (\n";
	$string .= "       \"MUID\" => \"$uid\",\n";
	$string .= "       \"GID\" => \"$gid\",\n";
	$string .= "       \"TARGET\" => \"$basepath/$appname/public/dispatch.fcgi\",\n";
	//$string .= "       \"TARGET\" => \"/usr/bin/ror/public/dispatch.fcgi\",";
	$string .= "       \"NON_RESIDENT\" => \"1\"\n";
	$string .= "),\n";
	$string .= "\"idle-timeout\" => 3,\n";
	$string .= "\"strip-request-uri\" => \"$appurl/\"\n";
	$string .= "))\n";
	$string .= ")\n";
	if (!$app->isOn('accessible_directly')) {
		$string .= "}\n";
	}

	return $string;
}

function updateMainConfFile()
{
	global $gbl, $sgbl, $login, $ghtml; 

	lxfile_mkdir("__path_lighty_path/conf/kloxo");

	$virtual_file = "$sgbl->__path_lighty_path/conf/kloxo/virtualhost.conf";
	$init_file = "$sgbl->__path_lighty_path/conf/kloxo/init.conf";
	$vdomlist = $this->main->__var_vdomain_list; 
	$iplist = $this->main->__var_ipaddress;


	/// Start agiain....
	$fdata = null;

	$vdomlist = merge_array_object_not_deleted($vdomlist, $this->main);

	foreach((array) $vdomlist as $dom) {
		if (array_search_bool($dom['nname'], $this->main->__var_domainipaddress)) { continue; }
		$fdata .= "include \"conf/kloxo/lighttpd.{$dom['nname']}\"\n\n";
	}



	lfile_put_contents($virtual_file, $fdata);
	$this->updateIpConfFile();
	
}


function enablePhp()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$string = null;
	if (!$this->main->priv->isOn('php_flag'))  {
		return null;
	}

	if ($this->isRailsDocroot()) {
		return;
	}

	$fcgi_proc = 1;

	if ($this->main->priv->phpfcgiprocess_num > 0) {
		$fcgi_proc = $this->main->priv->phpfcgiprocess_num;
	}

	if (is_unlimited($this->main->priv->phpfcgiprocess_num)) {
		$fcgi_proc = "1";
	}

	if ($this->main->isOn('fcgi_children')) {
		$maxprocstring = "   \"max-procs\" => 1,\n";
		$fcgichildstring = "       \"PHP_FCGI_CHILDREN\" => \"$fcgi_proc\",\n";
	} else {
		$maxprocstring = "   \"max-procs\" => $fcgi_proc,\n";
		$fcgichildstring = "       \"PHP_FCGI_CHILDREN\" => \"0\",\n";
	}

	$phprc = null;
	lxfile_unix_chown("/home/httpd/{$this->main->nname}", "{$this->main->username}:apache");
	lxfile_unix_chmod("/home/httpd/{$this->main->nname}", "0775");
	if (!lxfile_exists("/home/httpd/{$this->main->nname}/php.ini")) {
		lxfile_cp("/etc/php.ini", "/home/httpd/{$this->main->nname}/php.ini");
	}
	$phprc = "       \"PHPRC\" => \"/home/httpd/{$this->main->nname}\",\n";

	if (!lxfile_exists("/var/tmp/lighttpd")) {
		lxfile_mkdir("/var/tmp/lighttpd");
		lxfile_unix_chown("/var/tmp/lighttpd", "apache:apache");
		lxfile_unix_chmod("/var/tmp/lighttpd", "0770");
	}


	if ($this->main->priv->isOn('phpfcgi_flag')) {
		$uid = os_get_uid_from_user($this->main->username);
		$gid = os_get_gid_from_user($this->main->username);
		$string .= "fastcgi.server  = (\".php\" =>";
		$string .= "(( \"socket\" => \"/var/tmp/lighttpd/php.socket.{$this->main->nname}.\" + var.PID,\n";
		$string .= "   \"bin-path\" => \"/usr/bin/lxsuexec\",\n";
		$string .= "   \"min-procs\" => 0,\n";
		$string .= $maxprocstring;
		$string .= "   \"bin-environment\" => (\n";
		$string .= "       \"MUID\" => \"$uid\",\n";
		$string .= "       \"GID\" => \"$gid\",\n";
		$string .= $phprc;
		$string .= "       \"TARGET\" => \"/usr/bin/php-cgi\",\n";
		$string .= "       \"NON_RESIDENT\" => \"0\",\n";
		$string .= $fcgichildstring;
		$string .= "       \"PHP_FCGI_MAX_REQUESTS\" => \"100000000\" ),\n";
		$string .= "   \"max-load-per-proc\" => 1000,\n";
		$string .= "   \"idle-timeout\" => 3\n";
		$string .= " ))\n";
		$string .= ")\n";
	} else {
		$string .= "cgi.assign = ( \".php\" => \"/home/httpd/{$this->main->nname}/phpsuexec.sh\", \n";
		$string .= "\".pl\" => \"/home/httpd/{$this->main->nname}/perlsuexec.sh\" )\n";
	}



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

	global $global_shell_error;

	//dprintr($this->main->__old_priv);


	$this->clearDomainIpAddress();


	$web_home   = $sgbl->__path_httpd_root ;
	$domainname = $this->main->nname;
	$log_path   = "$web_home/{$this->main->nname}/stats"; 
	$v_file     = "__path_lighty_path/conf/kloxo/lighttpd.{$this->main->nname}" ;

	$string = null;

	$dirp = $this->main->__var_dirprotect;
	
	$string = null;
	$aliasstring = $this->createServerAliasLine();

	/*
	if (0 && $this->getServerIp()) {
		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue ; }
			$string .= "\$SERVER[\"socket\"] == \"$ip:80\" {\n";
			$string .= $this->syncToPort("80", "www");
			$string .= $this->middlepart($domainname, $dirp); 
			$string .= "}\n";
		}
	} else {
		*/
	$string .= "\$HTTP[\"host\"] =~ \"$aliasstring\" {\n";
	$string .= $this->syncToPort("80", "www");
	$string .= $this->middlepart($domainname, $dirp); 
	$string .= "}\n";



	lxfile_mkdir($this->main->getFullDocRoot());

	if ($this->getServerIp()) {

		foreach($this->main->__var_domainipaddress as $ip => $dom) {
			if ($this->main->nname !== $dom) { continue ; }

			foreach($this->main->__var_ipssllist as $iip) {
				if ($iip['ipaddr'] === $ip) {
					break;
				}
			}
			$string .= "\$SERVER[\"socket\"] == \"$ip:443\" {\n";
			$string .= $this->syncToPort("443", "www");
			$string .= $this->middlepart($domainname, $dirp); 
			$string .= $this->getSslCert($iip);
			$string .= "}\n";
		}
	}



	$string .= $this->getAddon();



	$tmp = lx_tmp_file("light.{$this->main->nname}");
	lfile_put_contents($tmp, $string);

	$res = lxshell_return("lighttpd", "-t", "-f", $tmp);

	if ($res && $sgbl->isDebug()) {
		lxfile_cp($tmp, "/home/root/lighttpd_last_error");
	}

	lunlink($tmp);

	if ($res) {
		throw new lxException("lighttpd_configuration_check_failed", '', "{$this->main->nname}: $global_shell_error");
	}

	lfile_put_contents($v_file, $string);

}

function getAddon()
{
	$string = null;
	foreach((array) $this->main->__var_addonlist as $v) {
		if ($v->ttype !== 'redirect') {
			continue;
		}
		$string .= "\$HTTP[\"host\"] =~ \"^(www.)?$v->nname\" {\n";
		$dst = "{$this->main->nname}/$v->destinationdir";
		$dst = remove_extra_slash($dst);
		//$dst = trim($dst, "/");
		$string .= "url.redirect = ( \"/\" => \"http://$dst\")\n";
		$string .= "}\n\n";
	}

	if ($this->main->isOn('force_www_redirect')) {
		$string .= "\$HTTP[\"host\"] =~ \"^{$this->main->nname}$\" {\n";
		$string .= "url.redirect = ( \"^/(.*)\" => \"http://www.{$this->main->nname}/\$1\")\n";
		$string .= "}\n";
	}

	return $string;

}

function getBlockIP()
{
	$t = trimSpaces($this->main->text_blockip);
	$t = trim($t);
	if (!$t) { return; }
	$t = str_replace(".*", "", $t);
	$t = str_replace(" ", "|", $t);
	$string  = "\$HTTP[\"remoteip\"] =~ \"$t\" {\n";
	$string .= "url.access-deny = (\"\")\n";
	$string .= "}\n";
	return $string;
}


static function createSSlConf($iplist, $domainiplist)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$alliplist = os_get_allips();
	$string = null;
	foreach($iplist as $ip) {

		if (!array_search_bool($ip['ipaddr'], $alliplist)) { continue; }
		if (isset($domainiplist[$ip['ipaddr']]) && trim($domainiplist[$ip['ipaddr']])) { continue; }

		$ssl_cert = null;
		$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);
		$certificatef = "$sgbl->__path_ssl_root/$ssl_cert.crt";
		$keyfile = "$sgbl->__path_ssl_root/$ssl_cert.key";
		$pemfile = "$sgbl->__path_ssl_root/$ssl_cert.pem";
		$cafile = "$sgbl->__path_ssl_root/$ssl_cert.ca";

		sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

		if (!lxfile_exists($pemfile)) {
			$c = lfile_get_contents($certificatef);
			$k = lfile_get_contents($keyfile);
			lfile_put_contents($pemfile, "$c\n$k");
		}


		/*
		$string .= "\$SERVER[\"socket\"] == \"{$ip['ipaddr']}:80\" {\n";
		$string .= "	}\n";
		*/

		$string .= "\$SERVER[\"socket\"] == \"{$ip['ipaddr']}:443\" {\n";
		$string .= "ssl.engine = \"enable\"\n";
		$string .= "ssl.pemfile = \"$pemfile\"\n";
		$string .= "ssl.ca-file = \"$cafile\"\n";
		$string .= "	}\n";
	}

	$sslfile = "__path_lighty_path/conf/kloxo/ssl.conf";


	lfile_put_contents($sslfile, $string);

}

function getSslCert($ip)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$string = null;

	$ssl_cert = null;
	$ssl_cert = sslcert::getSslCertnameFromIP($ip['nname']);
	$certificatef = "$sgbl->__path_ssl_root/$ssl_cert.crt";
	$keyfile = "$sgbl->__path_ssl_root/$ssl_cert.key";
	$pemfile = "$sgbl->__path_ssl_root/$ssl_cert.pem";
	$cafile = "$sgbl->__path_ssl_root/$ssl_cert.ca";

	sslcert::checkAndThrow(lfile_get_contents($certificatef), lfile_get_contents($keyfile), $ssl_cert);

	if (!lxfile_exists($pemfile)) {
		$c = lfile_get_contents($certificatef);
		$k = lfile_get_contents($keyfile);
		lfile_put_contents($pemfile, "$c\n$k");
	}


	$string .= "ssl.engine = \"enable\"\n";
	$string .= "ssl.pemfile = \"$pemfile\"\n";
	$string .= "ssl.ca-file = \"$cafile\"\n";

	return $string;

}







function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$gen = $login->getObject('general')->generalmisc_b;
	//$alist[] = "a=show&k[class]=allinstallapp&k[nname]=installapp";

	return $alist;
}

function middlepart($domain, $dirp) {

	global $gbl, $sgbl, $login, $ghtml; 

	$string = null;


	if($this->main->isOn('status')) {
		foreach((array) $this->main->__var_railspp as $r) {
			if (!$r->isDeleted()) {
				$string .= $this->getRailsConf($r);
			}
		}
	}

	foreach($this->main->customerror_b as $k => $v) {
		if (csb($k, "url_") && $v) {
			$num = strfrom($k, "url_");
			if (csb($v, "http:/")) {
				$nv = $v;
			} else {
				$nv = remove_extra_slash("/$v");
			}
			if ($num !== "404") {
				continue;
			}
			if (!$this->isRailsDocroot()) {
				$string .= "server.error-handler-$num = \"$nv\"\n";
			}
		}
	}

	$string .= $this->enablePhp();

	/*
	if (isset($this->main->webmisc_b) && $this->main->webmisc_b->isOn('execcgi')) {
		$string .= "cgi.assign = ( \".cgi\" => \"/home/httpd/{$this->main->nname}/shsuexec.sh\" )\n";
	}
*/
	if (isset($this->main->webmisc_b)) {
		if ($this->main->webmisc_b->isOn('dirindex')) {
			$string .= "dir-listing.activate = \"enable\"\n";
		}
	}

	if ($this->main->stats_password) {
		$string .= $this->getDirprotectCore("stats", "/stats", "__stats");
	}
	$string .= $this->getDirIndexCore("/stats");
	$string .= $this->getDirprotect();

	return $string;
}

function getDirIndexCore($dir)
{
	$string = null;
	$dir = remove_extra_slash("/$dir");
	$string .= "\$HTTP[\"url\"] =~ \"^$dir\" {\n";
	$string .= "dir-listing.activate = \"enable\"\n}\n";
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
	$path = remove_extra_slash("/$path");
	$end = null;
	if ($path !== "/") { $end = "[/$]"; }
	$string = null;
	$string .= "\$HTTP[\"url\"] =~ \"^$path$end\" {\n";
	$string .= "auth.backend = \"htpasswd\"\n";
	$string .= "auth.backend.htpasswd.userfile = \"$sgbl->__path_httpd_root/{$this->main->nname}/__dirprotect/$file\"\n";
	$string .= "auth.require = ( \"$path\" => (\n";
	$string .= "\"method\" => \"basic\",\n";
	$string .= "\"realm\" => \"$authname\",\n";
	$string .= "\"require\" => \"valid-user\"\n";
	$string .= "))\n}\n";
	return $string;
}

function getSuexecString($username)
{
	$string = null;
	$string .= "<IfModule suexec.c>\n";
	$string .= "SuexecUserGroup     {$this->main->username}  {$this->main->username}\n";
	$string .= "</IfModule>\n";
	return $string;
}

function isRailsDocroot()
{
	$app = $this->main->__var_railspp;
	foreach((array)$app as $k) {
		if ($k->isOn('accessible_directly')) {
			return true;
		}
	}
	return false;
}

function getDocumentRoot($subweb)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$base_root = "$sgbl->__path_httpd_root";
	$web_home = "$sgbl->__path_httpd_root" ;


	$path = "{$this->main->getFullDocRoot()}/";

	$domname = $this->main->nname;

	$string = null;
	$string .= "alias.url    = (\"/__kloxo\" => \"/home/{$this->main->customer_name}/kloxoscript\")\n";
    $string .= "url.redirect = (\"/webmail\" => \"https://webmail.$domname\")\n";

	if ($this->main->nname !== 'lxlabs.com') {
		$string .= "url.redirect += (\"^kloxo$\" => \"https://cp.$domname:{$this->main->__var_sslport}\")\n";
		$string .= "url.redirect += (\"/kloxononssl\" => \"http://cp.$domname:{$this->main->__var_nonsslport}\")\n";
	}
	if ($this->main->__var_statsprog === 'awstats') {
		$string .= "url.redirect   += (\"/stats/\" => \"http://$domname/awstats/awstats.pl?config=$domname\")\n";
		$string .= "url.redirect   += (\"/stats\" => \"http://$domname/awstats/awstats.pl?config=$domname\")\n";
	} else {
		$string .= "alias.url   += (\"/stats\" => \"$sgbl->__path_httpd_root/$domname/webstats\")\n";
	}

	if($this->main->isOn('status')) {
		if (!$this->isRailsDocroot()) {
			$string .= "server.document-root =  \"$path\"\n";
		}
	} else {
		if ($this->main->__var_disable_url) {
			$url = add_http_if_not_exist($this->main->__var_disable_url);
			$string .= "url.redirect += ( \"/\" => \"$url\" )\n";
		} else {
			$disableurl = "/home/kloxo/httpd/disable/";
			$string .= "server.document-root = \"$disableurl\"\n";
		}
	}
	return $string;
}

function hotlink_protection()
{
	if (!$this->main->isOn('hotlink_flag')) {
		return null;
	}

	$string = null;
	$allowed_domain_string = $this->main->text_hotlink_allowed;
	$allowed_domain_string = trim($allowed_domain_string);
	$allowed_domain_string = str_replace("\r", "", $allowed_domain_string);
	$allowed_domain_string = str_replace("\n", "|", $allowed_domain_string);
	if ($allowed_domain_string) { $allowed_domain_string .= "|{$this->main->nname}";
	} else { $allowed_domain_string .= "{$this->main->nname}"; }
	$ht = trim($this->main->hotlink_redirect, "/");
	$ht = "/$ht";
	$string .= "\n\n";
	$string .= "\$HTTP[\"referer\"] !~ \"^($|https?://(.*\.|)($allowed_domain_string))\" {\n";
	$string .= "url.rewrite = (\"(?i)(/.*\.(jpe?g|png|gif|jpg|rar|pdf))$\" =>\n";
	$string .= "\"$ht\" )\n";
	$string .= "}\n\n";
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
	$string = implode("\", \"", $list);
	$string = "index-file.names =  (\"$string\")\n";
	return $string;
}

function syncToPort($port, $subweb, $frontpage = false)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$web_home = $sgbl->__path_httpd_root ;
	$base_root = $sgbl->__path_httpd_root;
	$domainname = $this->main->nname;
	$user_home = "{$this->main->getFullDocRoot()}/";
	$log_path   = "$web_home/{$this->main->nname}/stats"; 
	$cust_log 	= "$log_path/{$this->main->nname}-custom_log"; 
	$err_log 	= "$log_path/{$this->main->nname}-error_log";
	/*
	if (!$this->main->ipaddress) {
		throw new lxException("no_ipaddress", '');
	}
*/
	$string = null;

	$string .= $this->hotlink_protection();
	$string .= $this->getBlockIP();

	$string .= $this->main->text_lighty_rewrite;
	$string .= "\n";

	$domname = $this->main->nname;

	$string .= $this->getDocumentRoot($subweb);

	$string .= $this->getIndexFileOrder();


	// Hack.. This is done so that others can use '+' without any issue.

	$string .= "alias.url += ( \"/awstatsicons\" => \"/home/kloxo/httpd/awstats/wwwroot/icon/\" )\n";
	$string .= "alias.url += ( \"/awstatscss\" => \"/home/kloxo/httpd/awstats/wwwroot/css/\" )\n";

	$string .= $this->getAwstatsString();


	//$string .= $this->getSuexecString($this->main->username);




	if ($this->main->priv->isOn('cgi_flag')) {
		$string .= $this->getCgiString();
	}

	foreach((array) $this->main->redirect_a as $red) {
		$rednname = remove_extra_slash("/{$red->nname}");
		if ($red->ttype === 'local') {
			$string .= "alias.url += (\"$rednname\" => \"$user_home/$red->redirect\")\n";
		} else {
			if (!redirect_a::checkForPort($port, $red->httporssl)) { continue; }
			$string .= "url.redirect += (\".*$rednname\" => \"$red->redirect\")\n";
		}
	}

	$string .= "\n";

	$string .=  "accesslog.filename  =   \"$cust_log\"\n";
	$string .=   "server.errorlog    =  \"$err_log\"\n";


	return $string;
}


function getCgiString()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$web_home = $sgbl->__path_httpd_root ;
	$string = null;
	$string .= "alias.url += ( \"/cgi-bin\" => \"{$this->main->getFullDocRoot()}/cgi-bin/\")\n"; 
	$string .= "\$HTTP[\"url\"] =~ \"^/cgi-bin\" {\n";
	$string .= "    cgi.assign = ( \"\" => \"/$sgbl->__path_httpd_root/{$this->main->nname}/shsuexec.sh\" )\n}\n";
	return $string;

}

function getAwstatsString()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$web_home = $sgbl->__path_httpd_root ;
	$string = null;
	$string .= "alias.url     += (\"/awstats/\" => \"$sgbl->__path_kloxo_httpd_root/awstats/wwwroot/cgi-bin/\")\n";
	$string .= "\$HTTP[\"url\"] =~ \"^/awstats\" {\n";
	$string .= "    cgi.assign = ( \".pl\" => \"/$sgbl->__path_httpd_root/{$this->main->nname}/perlsuexec.sh\" )\n}\n";

	if ($this->main->stats_password) {
		$string .= $this->getDirprotectCore("Awstats", "/awstats", "__stats");
	}
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);
	return $string;

}






// The rest



function createSuexec()
{
	$string = null;
	$uid = os_get_uid_from_user($this->main->username);
	$gid = os_get_gid_from_user($this->main->username);

	$phprc = null;
	$phprc .= "       export PHPRC=/home/httpd/{$this->main->nname}\n";
	$string .= "#!/bin/sh\n";
	$string .= "### Username: {$this->main->username}\n";
	$string .= "export MUID=$uid\n";
	$string .= "export GID=$gid\n";
	$string .= $phprc;
	$string .= "export TARGET=<%program%>\n";
	$string .= "export NON_RESIDENT=1\n";
	$string .= "exec lxsuexec $*\n";
	$st = str_replace("<%program%>", "/usr/bin/php-cgi", $string);
	lfile_put_contents("__path_httpd_root/{$this->main->nname}/phpsuexec.sh", $st);
	$st = str_replace("<%program%>", "/usr/bin/lxexec", $string);
	lfile_put_contents("__path_httpd_root/{$this->main->nname}/shsuexec.sh", $st);

	$st = str_replace("<%program%>", "/usr/bin/perl", $string);
	lfile_put_contents("__path_httpd_root/{$this->main->nname}/perlsuexec.sh", $st);

	lxfile_unix_chmod("__path_httpd_root/{$this->main->nname}/shsuexec.sh", "0755");
	lxfile_unix_chmod("__path_httpd_root/{$this->main->nname}/phpsuexec.sh", "0755");
	lxfile_unix_chmod("__path_httpd_root/{$this->main->nname}/perlsuexec.sh", "0755");
}




function createServerAliasLine()
{

	$list = get_namelist_from_objectlist($this->main->server_alias_a);

	$iplist = null;
	foreach($this->main->__var_domainipaddress as $ip => $dom) {
		if ($dom === $this->main->nname) {
			$iplist[] = $ip;
		}
	}

	if (array_search_bool('*', $list)) {
		if ($iplist) {
			$ip = implode("|", $iplist);
			return "{$this->main->nname}|$ip";
		} else {
			return "{$this->main->nname}";
		}
	}


	if ($list) foreach($list as &$__l) {
		$__l = "$__l.{$this->main->nname}";
	}
	if ($this->main->isOn('force_www_redirect')) {
		$list = lx_merge_good(array("www.{$this->main->nname}"), $list);
	} else {
		$list = lx_merge_good(array("www.{$this->main->nname}", $this->main->nname), $list);
	}

	foreach((array) $this->main->__var_addonlist as $d) {
		$list[] = $d->nname;
		$list[] = "www.$d->nname";
	}

	$list = lx_array_merge(array($list, $iplist));

	$string = implode("|", $list);

	return "^($string)";
}


function addDomain()
{
	self::createWebmailConfig(null);
	$this->main->createDir();
	$this->createConffile();
	$this->updateMainConfFile();
	$this->createSuexec();
	$this->main->createPhpInfo();

}

static function createWebmailRedirect($list)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$webdata = null;
	foreach($list as $l) {
		$webdata  .= "\$HTTP[\"host\"] =~ \"^webmail.{$l['nname']}\" { \n";
		if ($l['remotelocalflag'] === 'remote') {
			$l['webmail_url'] = add_http_if_not_exist($l['webmail_url']);
			$webdata .= "url.redirect = ( \"/\" =>  \"{$l['webmail_url']}\")\n";
		} else {

			$prog = ($l['webmailprog'] == '--chooser--')? "": $l['webmailprog'];
			if (is_disabled($prog)) {
				$webdata .= "server.document-root = \"$sgbl->__path_kloxo_httpd_root/webmail/disabled/\"\n";
			} else {
				$webdata .= "server.document-root = \"$sgbl->__path_kloxo_httpd_root/webmail/\"\n";
			}

			if ($prog) {
				$webdata .= "index-file.names = ( \"redirect-to-$prog.php\", \"index.php\")\n";
			}
			//$webdata .= "cgi.assign = ( \".php\" => \"/home/httpd/{$l['nname']}/phpsuexec.sh\" )\n";
			$webdata .= "cgi.assign = ( \".php\" => \"/home/httpd/nobody.sh\" )\n";
		}
		$webdata .= "}\n\n\n";

	}

	lfile_put_contents("__path_lighty_path/conf/kloxo/webmail_redirect.conf", $webdata);
	createRestartFile("lighttpd");

}

static function createWebmailConfig($iplist)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$file = "__path_lighty_path/conf/kloxo/webmail.conf";

	$webdata = null;

	$webdata .= "\$HTTP[\"host\"] =~ \"^webmail.*\" { \n";
	$webdata .= "server.document-root = \"$sgbl->__path_kloxo_httpd_root/webmail/\"\n";
	$webdata .= "server.errorlog = \"/home/kloxo/httpd/lighttpd/error.log\"\n";
	$webdata .= "cgi.assign = ( \".php\" => \"/home/httpd/nobody.sh\" )\n";
	$webdata .= "}\n\n\n";  


	$total = "\n$webdata\n";

	lfile_put_contents($file, $total);

	createRestartFile("lighttpd");
	

}

static function fixErrorLog($name)
{
	$file = "/home/kloxo/httpd/lighttpd/error.log";
	$cmd = "grep -i $name $file > /home/httpd/$name/stats/$name-error_log";
	log_shell($cmd);
	system($cmd);

	$size = lxfile_size($file);

	if ($size > 50 * 1024 * 1024) {
		dprint("File size larger than 1MB\n");
		$nfile = getNotexistingFile(dirname($file), $file);
		lxfile_mv($file, $nfile);
		createRestartFile("lighttpd");
	}
}

static function fixErrorLogbad($list)
{
	$file = "/home/kloxo/httpd/lighttpd/error.log";
	$fp = lfopen($file);
	if (!$fp) {
		return;
	}
	while (!feof($fp)) {
		$s = fgets($fp);
		foreach($list as $l) {
			if (csa($s, $l)) {
				$out[$l] .= $s;
			}
		}
	}

	foreach($out as $k => $v) {
		lfile_put_contents("/home/httpd/$k/stats/$k-error_log", $v, FILE_APPEND);
	}

	fclose($fp);
	$fp = getNotexistingFile(dirname($file), $file);
	lxfile_mv($file, $nfile);

			


}

function dbactionAdd()
{
	$this->addDomain();
	$this->main->doStatsPageProtection();
	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
}

function dbactionDelete()
{
	lunlink("/etc/lighttpd/conf/kloxo/lighttpd.{$this->main->nname}");
	$this->delDomain();
}



function dosyncToSystemPost()
{
	global $gbl, $sgbl, $login, $ghtml; 
	createRestartFile("lighttpd");
}



function fullUpdate()
{
	$this->createConffile();
	$this->createSuexec();
	$this->updateMainConfFile();
	self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
	self::createWebmailConfig(null);
	web::createstatsConf($this->main->nname, $this->main->stats_username, $this->main->stats_password);
	$log_path = "/home/httpd/{$this->main->nname}/stats";
	lxfile_unix_chown_rec($log_path, "{$this->main->username}:apache");
	lxfile_unix_chmod_rec($log_path, "770");
	$this->main->createPhpInfo();
	lxfile_unix_chown("__path_httpd_root/{$this->main->nname}", "{$this->main->username}:apache");
	lxfile_unix_chmod("__path_httpd_root/{$this->main->nname}", "0755");
	lxfile_unix_chmod("{$this->main->getFullDocRoot()}", "0755");
	//lxfile_unix_chown_rec("{$this->main->getFullDocRoot()}", $this->main->customer_name);
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


		case "changeowner":
			$this->main->webChangeOwner();
			$this->createConffile();
			$this->createSuexec();
			break;

		case "enable_frontpage_flag":
			$this->frontPageEnable();
			//$this->createConffile();
			break;

		case "fixipdomain":
			$this->createConffile();
			$this->updateMainConfFile();
			self::createSSlConf($this->main->__var_ipssllist, $this->main->__var_domainipaddress);
			break;

		case "addondomain":
		case "phpconfig":
		case "add_delete_dirprotect":
		case "extra_tag" : 
		case "dirindex":
		case "add_dirprotect" : 
		case "custom_error":
		case "lighty_rewrite":
		case "blockip";
		case "docroot":
		case "ipaddress": 
		case "add_redirect_a":
		case "delete_redirect_a":
		case "delete_redirect_a":
		case "add_webindexdir_a":
		case "delete_webindexdir_a":
		case "add_server_alias_a" : 
		case "delete_server_alias_a" : 
		case "configure_misc":
		case "fcgi_config":
		case "railsconf":
			$this->createConffile();
			$this->createSuexec();
			break;


		case "toggle_status" : 
			$this->createConffile();
			break;

		case "enable_phpfcgi_flag":
		case "enable_php_flag":
		case "change_phpfcgiprocess_num":
		case "enable_cgi_flag":
		case "enable_inc_flag":
		case "enable_ssl_flag" : 
			$this->createConffile();
			//$this->updateMainConfFile();
			break;

		case "enable_php_manage_flag":
			$this->createConffile();
			$this->createSuexec();
			break;

		case "stats_protect":
			$this->main->doStatsPageProtection();
			$this->createConffile();
			break;

		case "hotlink_protection":
		case "permalink":
			$this->createConffile();
			break;

		case "graph_webtraffic":
			return rrd_graph_single("webtraffic", $this->main->nname, $this->main->rrdtime);
			break;

		case "run_stats":
			$this->main->runStats();
			break;
	}
}



function getDav()
{

	$string = null;
	$bdir = "/home/httpd/{$this->main->nname}/__webdav";
	lxfile_mkdir($bdir);
	foreach($this->main->__var_davuser as $k => $v) {
		$file = get_file_from_path($k);
		$dbf = "/tmp/$file.db";
		$file = "$bdir/$file";
		lxfile_touch($file);
		$string .= "\$HTTP[\"url\"] =~ \"^$k($|/)\" {\n";
		$string .= "webdav.activate = \"enable\"\n";
		$string .= "webdav.is-readonly = \"disable\"\n";
		$string .= "auth.backend = \"htpasswd\"\n";
		$string .= "auth.backend.htpasswd.userfile = \"$file\"\n";
		$string .= "webdav.sqlite-db-name = \"$dbf\"\n";
		$string .= "auth.require = ( \"\" => ( \"method\" => \"basic\",\n";
		$string .= "\"realm\" => \"webdav\",\n";
		$string .= "\"require\" => \"valid-user\" ) )\n";
		$string .= "}\n";
	}

	return $string;

}


function do_backup()
{
	return $this->main->do_backup();
}

function do_restore($docd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name = $this->main->nname;
	$fullpath = "$sgbl->__path_customer_root/{$this->main->customer_name}/$name/";

	$this->main->do_restore($docd);

	lxfile_unix_chown_rec($fullpath, $this->main->username);

}


}
