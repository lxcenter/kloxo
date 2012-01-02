<?php 

class Dns__Maradns  extends lxDriverClass {

// Core

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

	$dnsfile = "{$sgbl->__path_mara_chroot}/{$sgbl->__path_mara_path}/{$domainname}" ;

	$nameduser = $sgbl->__var_programuser_dns;
	$fdata = null;
	//$fdata .= "\$TTL\t\t{$this->main->ttl}\n";

	$nameserver = null;
	$dnsdata = null;
	foreach($this->main->dns_record_a as $dns) {
		if ($dns->ttype === "ns") {
			$nameserver = $dns->param;
			$dnsdata .= "{$domainname}. NS  $dns->param.  \n";
		}
	}
	/*
	// Ugly hack to fix it for testing...
	if (!$nameserver) {
		$nameserver = 'ns.lxlabs.com';
	}
*/

	$ddate = date("Ymd");

	$v = rand(0, 99);
	if ($v < 10) {
		$v = "0$v";
	}
	$ddate = "$ddate$v";
	
	//$string .= "% SOA {$nameserver}. {$this->main->__var_email}@% 1  10800   3600    604800  86400 \n";
	$string = null;
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
				$tmp= "{$domainname}.       MX $v {$o->param}. \n";
				$fdata .= $tmp;
				break;

			case "a":
				$key = $o->hostname;
				$value = $o->param;

				if ($key === '*') {
					$starvalue = "* A $value";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.{$domainname}.";
				} else {
					$key = "{$domainname}.";
				}

				$tmp= "$key\t\t A   $value \n";
				$fdata .= $tmp;
				break;

			case "cn":
			case "cname":
				$key = $o->hostname;
				$value = $o->param;
				$key .= ".{$domainname}.";

				if ($value !== "__base__") {
					$value = "$value.{$domainname}.";
				} else {
					$value = "{$domainname}.";
				}

				if ($key === '*') {
					$starvalue = "*		CNAME $value\n";
					break;
				}
				$tmp= "$key     CNAME  $value\n" ;
				$fdata .= $tmp;
				break;

			case "txt":
				$key = $o->hostname;
				$value = $o->param;
				if($o->param === null) continue;	

				if ($key !== "__base__") {
					$key = "$key.{$domainname}.";
				} else {
					$key = "{$domainname}.";
				}

				$value = str_replace("<%domain>", $domainname, $value);

				$tmp= "$key     TXT  \"$value\"\n" ;
				$fdata .= $tmp;
				break;
		}
	}


	//$fdata .= $starvalue;

	dprint($dnsfile);

	$tmpfile = lx_tmp_file($dnsfile);
	lfile_put_contents($tmpfile, $fdata);

	$ret = 0;

	if ($ret) {
		$out = lxshell_output("named-checkzone",  $domainname, $tmpfile);
		log_log("error", $out);
		$out = str_replace($tmpfile, "", $out);
		unlink($tmpfile);
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
	$result = $this->main->__var_domainlist;

	$fdata = null;
	$dnsfile = "{$sgbl->__path_mara_conf}";
	$nameduser = $sgbl->__var_programuser_dns;

	$iplist = os_get_allips();
	$iplist[] = "127.0.0.1";
	$iplist = implode(",", $iplist);

	$fdata = "csv2 = {}\n";
	$fdata .= "chroot_dir = \"/etc/maradns\"\n";
	$fdata .= "ipv4_bind_addresses=\"$iplist\"\n";
	$fdata .= "recursive_acl=\"0.0.0.0/0\"\n";


	$result = merge_array_object_not_deleted($result, $this->main);

	if (!$this->main->isDeleted()) {
		foreach((array) $this->main->__var_addonlist as $d) {
			$result = merge_array_object_not_deleted($result, $d);
		}
	}

	foreach((array) $result as $value){
		$tmp= "csv2[\"{$value['nname']}.\"] = \"{$value['nname']}\"\n";
		$fdata .= $tmp;
	}





	lfile_put_contents($dnsfile, $fdata);
	lxfile_unix_chown($dnsfile, $nameduser);

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
	$dnsfile = "{$sgbl->__path_mara_chroot}/{$sgbl->__path_mara_path}/{$this->main->nname}" ;
	lxfile_rm($dnsfile);
	foreach((array) $this->main->__var_addonlist as $d) {
		$dnsfile = "{$sgbl->__path_mara_chroot}/{$sgbl->__path_mara_path}/{$d->nname}" ;
		lxfile_rm($dnsfile);
	}
	$this->syncCreateConf();

}

function dosyncToSystemPost()
{
	global $sgbl;
	//$ret =  lxshell_return("rndc", "reload", $this->main->nname);
	$ret = 1;
	if ($ret) {
		createRestartFile("maradns");
	}
}

}
