<?php 

class Dns__djbdns  extends lxDriverClass {

// Core


static function installMe()
{
	lxshell_return("yum", "-y", "install", "djbdns", "daemontools");
	if ($ret) { throw new lxexception('install_djbdns_failed', 'parent'); }
	lxfile_rm_rec("/var/tinydns");
	lxfile_rm_rec("/var/axfrdns");
	lxshell_return("__path_php_path", "../bin/misc/djbdnsstart.php");
	lxfile_cp("../file/djbdns.init", "/etc/init.d/djbdns");
	lxfile_unix_chmod("/etc/init.d/djbdns", "0755");
	lxshell_return("chkconfig", "djbdns", "on");
	createRestartFile("djbdns");
}

static function unInstallMe()
{
	lxshell_return("service", "djbdns", "stop");
	lxshell_return("rpm", "-e", "djbdns");
	lunlink("/etc/init.d/djbdns");
}

function createConfFile()
{
	$fdata = null;
	$fdata .= $this->syncAddFile($this->main->nname);
	foreach((array) $this->main->__var_addonlist as $add) {
		$fdata .= $this->syncAddFile($add->nname);
	}

}


function syncAddFile($domainname)
{
	global $gbl, $sgbl, $login, $ghtml;


	$nameduser = "tinydns";
	$fdata = null;

	// #772 - Add TTL Support
	$ttl=$this->main->ttl;

	$dnsrec = $this->main->dns_record_a;
	$arec = null;
	$fdata = null;
	$starvalue = null;
	$dnsdata = null;
	$nameserver = null;
	foreach($dnsrec as $dns) {
		if ($dns->ttype === "ns") {
			if (!$nameserver) {
				$nameserver = $dns->param;
			}
		}

		if ($dns->ttype === 'a') {
			$arecord[$dns->hostname] = $dns->param;
		}
	}

	if ($this->main->soanameserver) {
		$nameserver = $this->main->soanameserver;
	}

	$dnsdata .= "Z{$domainname}:$nameserver:{$this->main->__var_email}:{$this->main->__var_ddate}:::::$ttl\n";
	$dnsdata .= ".{$domainname}::$nameserver:$ttl\n";


	$starvalue = null;

	$fdata .= $dnsdata ;


	foreach($dnsrec as $k => $o) {

		switch($o->ttype) {

			case "ns":
				if ($o->param !== $nameserver) {
					$fdata .= "&{$domainname}::$o->param:$ttl\n";
				}
				break;

			case "mx":
				$v = $o->priority;
				$tmp= "@$domainname::{$o->param}:$v:$ttl\n";
				$fdata .= $tmp;
				break;



			case "a":
				$key = $o->hostname;
				$value = $o->param;
				if ($key === '*') {
					$starvalue = "+*.$domainname:$value:$ttl";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.$domainname";
				} else {
					$key = "$domainname";
				}

				$tmp= "+$key:$value\n";
				$fdata .= $tmp;
				break;


			case "cn":
			case "cname":
				$key = $o->hostname;
				$value = $o->param;

				if (isset($arecord[$value])) {
					$rvalue = $arecord[$value];

					if ($key === '*') {
						$starvalue = "+*.$domainname:$rvalue:$ttl\n";
						break;
					}
					$key .= ".$domainname";
					$fdata .= "+$key:$rvalue:$ttl\n" ;
					break;
				}

				if ($value !== "__base__") {
					$value = "$value.$domainname";
				} else {
					$value = "$domainname";
				}


				if ($key === '*') {
					$starvalue = "C*.$domainname:$value:$ttl\n";
					break;
				}

				$key .= ".{$domainname}";
				$fdata .= "C$key:$value:$ttl\n" ;
				break;

			case "fcname":
				$key = $o->hostname;
				$value = $o->param;


				if ($value !== "__base__") {
					$value = $value;
				} else {
					$value = "$domainname";
				}

				$key .= ".{$domainname}";
				$fdata .= "C$key:$value:$ttl\n" ;
				break;

			case "txt":
				$key = $o->hostname;
				$value = $o->param;
				if($o->param === null) continue;

				if ($key !== "__base__") {
					$key = "$key.$domainname";
				} else {
					$key = "$domainname";
				}

				$value = str_replace("<%domain>", $domainname, $value);
				$value = str_replace(":", "\\072", $value);

				$tmp= "'$key:$value:$ttl\n" ;
				$fdata .= $tmp;
				break;
		}
	}

	$fdata .= "$starvalue\n";
	lxfile_mkdir("/var/tinydns/root/kloxo");
	lfile_put_contents("/var/tinydns/root/kloxo/$domainname.data", $fdata);

}


function syncCreateConf()
{

	global $gbl, $sgbl, $login, $ghtml;


//	$host = `hostname`;
	$dlistv = "__var_domainlist_{$this->main->__var_syncserver}";
	$result = $this->main->$dlistv;
	$nameduser = "tinydns";

	$dnsfile = "/var/tinydns/root/data";

	//dprintr($result);
	$result = merge_array_object_not_deleted($result, $this->main);

	if (!$this->main->isDeleted()) {
		foreach((array) $this->main->__var_addonlist as $d) {
			$result = merge_array_object_not_deleted($result, $d);
		}
	}

	$cdata = null;

	foreach((array) $result as $value){
		$cdata .= " {$value['nname']}.data ";
	}

	$cdata = trim($cdata);
	if ($cdata) {
		$cmd = "cd /var/tinydns/root/kloxo/ ; cat $cdata > ../data";
		log_log("dns_log", $cmd);
		system($cmd);
	} else {
		system("rm /var/tinydns/root/data");
	}

	lxfile_unix_chown($dnsfile, $nameduser);
	lxshell_directory("/var/tinydns/root/", "make");
	lxshell_directory("/var/tinydns/root/", "tinydns-data");

}



function dbactionAdd()
{
	$this->createConfFile();
	$this->syncCreateConf();

	$this->fixRelay();


}


function fixRelay()
{
	$list = os_get_allips();
	$out = implode("\n", $list);
	$out = "$out\n";
	lfile_put_contents("/var/dnscache/root/servers/{$this->main->nname}", $out);
	foreach((array) $this->main->__var_addonlist as $d) {
		$dnsfile = "/var/dnscache/root/servers/{$d->nname}" ;
		lfile_put_contents($dnsfile, $out);
	}
}

function dbactionUpdate($subaction)
{

	$this->createConfFile();
	$this->syncCreateConf();
	$this->fixRelay();
	if ($subaction === 'full_update') {
		//$this->fixRelay();
	}
}

function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml;

	$dnsfile = "/var/dnscache/root/servers/{$this->main->nname}" ;
	$tinyfile = "/var/tinydns/root/kloxo/{$this->main->nname}.data";
	lxfile_rm($dnsfile);
	lxfile_rm($tinyfile);
	foreach((array) $this->main->__var_addonlist as $d) {
		$dnsfile = "/var/dnscache/root/servers/{$d->nname}" ;
		$tinyfile = "/var/tinydns/root/kloxo/{$d->nname}.data";
		lxfile_rm($dnsfile);
		lxfile_rm($tinyfile);
	}
	$this->syncCreateConf();

}

function dosyncToSystemPost()
{
	global $sgbl;

	createRestartFile("djbdns");
}

}
