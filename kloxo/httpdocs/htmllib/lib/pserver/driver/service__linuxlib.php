<?php 


class Service__Linux extends Lxlclass {




static function getServiceList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$val = lscandir_without_dot("__path_real_etc_root/init.d");
	$val = array_remove($val, $sgbl->__var_programname_web);
	$val = array_remove($val, $sgbl->__var_programname_dns);
	$val = array_remove($val, $sgbl->__var_programname_imap);
	$val = array_remove($val, $sgbl->__var_programname_mmail);
	$nval = self::getMainServiceList();
	$nval = lx_array_merge(array($nval, $val));
	return $nval;
}

static function getMainServiceList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$nval['httpd'] = '';
	$nval['named'] = 'named';
	$nval['qmail'] = 'qmail';
	$nval['courier-imap'] = 'courier';
	$nval['lighttpd'] = '';
	$nval['spamassassin'] = '';
	$nval['iptables'] = "";
	$nval['djbdns'] = "tinydns";
	return $nval;
}


static function checkService($name)
{
	$servicepath = "__path_real_etc_root/init.d";
	$ret = lxshell_return("$servicepath/$name", "status");
	$state =  ($ret) ? "off": "on";
	return $state;
}

static function getRunLevel()
{
	$v = trim(lxshell_output("runlevel"));
	$v = explode(" ", $v);
	return $v[1];
}

}
