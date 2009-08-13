<?php 

class Dns__Bind  extends lxDriverClass {

// Core

static function installMe()
{
	$ret = lxshell_return("yum", "-y", "install", "bind", "bind-chroot");
	if ($ret) { throw new lxexception('install_bind_failed', 'parent'); }
	lxshell_return("chkconfig", "named", "on");
	$pattern='include "/etc/kloxo.named.conf";';
	$file = "/var/named/chroot/etc/named.conf";
	$comment = "//Kloxo";
	addLineIfNotExistInside($file, $pattern, $comment);
	touch("/var/named/chroot/etc/kloxo.named.conf");
	chown("/var/named/chroot/etc/kloxo.named.conf", "named");
	createRestartFile("named");
}

static function unInstallMe()
{
	lxshell_return("service",  "named", "stop");
	lxshell_return("rpm", "-e", "--nodeps", "bind");
	lxshell_return("rpm", "-e", "--nodeps", "bind-chroot");

}

function createConfFile()
{
	$this->syncAddFile($this->main->nname);

	foreach((array)$this->main->__var_addonlist as $d) {
		$this->syncAddFile($d->nname);
	}

}

function syncAddFile($domainname)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$dnsfile = "{$sgbl->__path_named_chroot}/{$sgbl->__path_named_path}/$domainname" ;

	$nameduser = $sgbl->__var_programuser_dns;
	$fdata = null;
	$fdata .= "\$TTL\t\t{$this->main->ttl}\n";

	$nameserver = null;
	$dnsdata = null;
	foreach($this->main->dns_record_a as $dns) {
		if ($dns->ttype === "ns") {
			$nameserver = $dns->param;
			$dnsdata .= "$domainname. IN  NS  $dns->param.  \n";
		}

		if ($dns->ttype === 'a') {
			$arecord[$dns->hostname] = $dns->param;
		}
	}
	/*
	// Ugly hack to fix it for testing...
	if (!$nameserver) {
		$nameserver = 'ns.lxlabs.com';
	}
*/

	if ($this->main->soanameserver) {
		$nameserver = $this->main->soanameserver;
	}

	
	$string = <<<STR

@   IN  SOA {$nameserver}. {$this->main->__var_email}. (
			{$this->main->__var_ddate}  ; Serial
            10800   ; Refresh
            3600    ; Retry
            604800  ; Expire
            86400 ) ; Minimum

STR;


	$fdata .= $string;
	$starvalue = null;

	$fdata .= $dnsdata ;
	


	foreach($this->main->dns_record_a as $k => $o) {

		switch($o->ttype) {

			case "ns":
				//$tmp = "{$domainname} IN  NS  $o->param.  \n";
				//$fdata .= $tmp;
				break;

			case "mx":
				$v = $o->priority;
				$tmp= "$domainname.       IN  MX $v {$o->param}. \n";
				$fdata .= $tmp;
				break;


			case "aaaa":
				$key = $o->hostname;
				$value = $o->param;

				if ($key === '*') {
					$starvalue .= "* IN AAAA $value\n";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.$domainname.";
				} else {
					$key = "$domainname.";
				}

				$tmp= "$key\t\t IN  AAAA   $value \n";
				$fdata .= $tmp;
				break;


			case "a":
				$key = $o->hostname;
				$value = $o->param;

				if ($key === '*') {
					$starvalue = "* IN A $value\n";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.$domainname.";
				} else {
					$key = "$domainname.";
				}

				$tmp= "$key\t\t IN  A   $value \n";
				$fdata .= $tmp;
				break;

			case "cn":
			case "cname":


				$key = $o->hostname;
				$value = $o->param;

				if (isset($arecord[$value])) {
					$rvalue = $arecord[$value];

					if ($key === '*') {
						$starvalue .= "*		IN A $rvalue\n";
						break;
					}
					$key .= ".$domainname.";

					$tmp= "$key\t\t IN  A   $rvalue \n";
					$fdata .= $tmp;
					break;
				}

				$key .= ".$domainname.";

				if ($value !== "__base__") {
					$value = "$value.$domainname.";
				} else {
					$value = "$domainname.";
				}

				if ($key === '*') {
					//$starvalue = "*		IN CNAME $value\n";
					break;
				}
				$tmp= "$key     IN  CNAME  $value\n" ;
				$fdata .= $tmp;
				break;

			case "fcname":
				$key = $o->hostname;
				$value = $o->param;
				$key .= ".$domainname.";

				if ($value !== "__base__") {
					if (!cse($value, ".")) {
						$value = "$value.";
					}
				} else {
					$value = "$domainname.";
				}

				$tmp= "$key     IN  CNAME  $value\n" ;
				$fdata .= $tmp;
				break;

			case "txt":
				$key = $o->hostname;
				$value = $o->param;
				if($o->param === null) continue;	

				if ($key !== "__base__") {
					$key = "$key.$domainname.";
				} else {
					$key = "$domainname.";
				}

				$value = str_replace("<%domain>", $domainname, $value);

				$tmp= "$key     IN  TXT  \"$value\"\n" ;
				$fdata .= $tmp;
				break;
		}
	}


	$fdata .= $starvalue;

	$fdata .= "\n";

	dprint($dnsfile);

	$tmpfile = lx_tmp_file($dnsfile);
	lfile_put_contents($tmpfile, $fdata);

	$ret = lxshell_return("named-checkzone",  $domainname, $tmpfile);
	//$ret = 0;

	if ($ret) {
		$out = lxshell_output("named-checkzone",  $domainname, $tmpfile);
		log_log("error", $out);
		$out = str_replace($tmpfile, "", $out);
		//unlink($tmpfile);
		throw new lxException("dns_conflict", 'dns', $out);
		return;
	}
	unlink($tmpfile);

	lfile_put_contents($dnsfile, $fdata);
	lxfile_unix_chown($dnsfile, $nameduser);
}


function syncCreateConf()
{

	global $gbl, $sgbl, $login, $ghtml; 

	
//	$host = `hostname`;

	$dlistv = "__var_domainlist_{$this->main->__var_syncserver}";
	$result = $this->main->$dlistv;

	//dprintr($result);
	$fdata = null;
	$dnsfile = "{$sgbl->__path_named_chroot}/{$sgbl->__path_named_conf}";
	$namedpath = $sgbl->__path_named_path;
	$nameduser = $sgbl->__var_programuser_dns;


	$result = merge_array_object_not_deleted($result, $this->main);

	if (!$this->main->isDeleted()) {
		foreach((array) $this->main->__var_addonlist as $d) {
			$result = merge_array_object_not_deleted($result, $d);
		}
	}

	foreach((array) $result as $value){
		$value['nname'] = trim($value['nname']);
		if ($value['nname'] && lxfile_exists("/var/named/chroot/$namedpath/{$value['nname']}")) {
			$fdata .= "\nzone  \"{$value['nname']}\" { type master; file \"$namedpath/{$value['nname']}\";};\n";
		}
	}



	lfile_put_contents($dnsfile, $fdata);
	lxfile_unix_chown($dnsfile, $nameduser);

	$pattern='include "/etc/kloxo.named.conf";';
	$file = "/var/named/chroot/etc/named.conf";
	$comment = "//Kloxo";
	@ addLineIfNotExistInside($file, $pattern, $comment);
	lxfile_touch("/var/named/chroot/etc/kloxo.named.conf");


}


function dbactionAdd()
{
	$this->createConfFile();
	$this->syncCreateConf();
}


function dbactionUpdate($subaction)
{

	$this->createConfFile();
	$this->syncCreateConf();

}

function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$dnsfile = "{$sgbl->__path_named_chroot}/{$sgbl->__path_named_path}/{$this->main->nname}" ;
	lxfile_rm($dnsfile);
	foreach((array) $this->main->__var_addonlist as $d) {
		$dnsfile = "{$sgbl->__path_named_chroot}/{$sgbl->__path_named_path}/{$d->nname}" ;
		lxfile_rm($dnsfile);
	}
	$this->syncCreateConf();

}

function dosyncToSystemPost()
{
	global $sgbl;

	if ($this->main->isDeleted()) {
		createRestartFile("bind");
		return;
	}

	$total = false;
	$ret =  lxshell_return("rndc", "reload", $this->main->nname);
	if ($ret) { $total = true; }
	
	foreach((array) $this->main->__var_addonlist as $d) {
		$ret =  lxshell_return("rndc", "reload", $d->nname);
		if ($ret) { $total = true; }
	}

	if ($total) {
		$ret = lxshell_return("rndc", "reload");
		if ($ret) {
			createRestartFile("bind");
		}
	}
}

}
