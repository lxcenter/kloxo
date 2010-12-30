<?php 

class davuser__lighttpd extends Lxdriverclass {


function dbactionAdd()
{
	$this->createDiruserfile();
}


function createDiruserfile()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$result = $this->main->__var_davuser;

	$res = null;
	foreach($result as $r) {
		$cr = crypt($r['realpass']);
		//$cr = $r['realpass'];
		$res .= "{$r['username']}:$cr\n";
	}

	lxfile_mkdir("__path_httpd_root/{$this->main->getParentName()}/__davuser");
	lfile_put_contents("__path_httpd_root/{$this->main->getParentName()}/__davuser/davuser", $res);

	$this->createDavSuexec();

	$string = $this->createVirtualHost();

	lfile_put_contents("/home/kloxo/httpd/webdisk/virtualhost.conf", $string);
	createRestartFile("webdisk");
}

function createVirtualHost()
{
	$string = null;
	foreach($this->main->__var_domlist as $v) {
		$string .= "\$HTTP[\"host\"] =~ \"^$v\" {\n";
		$string .= "server.document-root =  \"/usr/local/lxlabs/kloxo/httpdocs/webdisk/\"\n";
		$string .= "cgi.assign = ( \".php\" => \"/home/httpd/$v/davsuexec.sh\" )\n";
		$string .= $this->getDirprotectCore($v);
		$string .= "}\n\n\n";
		lxfile_mkdir("__path_httpd_root/$v/__davuser/");
		lxfile_touch("__path_httpd_root/$v/__davuser/davuser");
	}
	return $string;
}

function getDirprotectCore($domain)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$string = null;
	$string .= "\$HTTP[\"url\"] =~ \"/\" {\n";
	$string .= "auth.backend = \"htpasswd\"\n";
	$string .= "auth.debug = 2\n";
	$string .= "auth.backend.htpasswd.userfile = \"$sgbl->__path_httpd_root/$domain/__davuser/davuser\"\n";
	$string .= "auth.require = ( \"/\" => (\n";
	$string .= "\"method\" => \"basic\",\n";
	$string .= "\"realm\" => \"$domain\",\n";
	$string .= "\"require\" => \"valid-user\"\n";
	$string .= "))\n}\n";
	return $string;
}

function createDavSuexec()
{
	$string = null;
	$uid = os_get_uid_from_user($this->main->__var_system_username);
	$gid = os_get_gid_from_user($this->main->__var_system_username);

	$string .= "#!/bin/sh\n";
	$string .= "export MUID=$uid\n";
	$string .= "export GID=$gid\n";
	$string .= " export PHPRC=/usr/local/lxlabs/ext/php/etc/php.ini\n";
	$string .= "export TARGET=<%program%>\n";
	$string .= "export NON_RESIDENT=1\n";
	$string .= "exec lxsuexec $*\n";
	$st = str_replace("<%program%>", "/usr/local/lxlabs/ext/php/bin/php_cgi", $string);
	lfile_put_contents("__path_httpd_root/{$this->main->getParentName()}/davsuexec.sh", $st);
	lxfile_unix_chmod("__path_httpd_root/{$this->main->getParentName()}/davsuexec.sh", "0755");
}

function dbactionUpdate($subaction)
{
	$this->createDiruserfile();
}

}
