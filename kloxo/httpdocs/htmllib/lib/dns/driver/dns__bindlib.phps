<?php 

class Dns__Bind  extends lxDriverClass {

// Core

// 2010-06-08 LN: Added constants for default SOA values
const DEFAULT_REFRESH = 3600; // 1 hour
const DEFAULT_RETRY   = 1800; // 30 minutes
const DEFAULT_EXPIRE  = 604800; // 1 week
const DEFAULT_MINIMUM = 1800; // 30 minutes

static function installMe()
{
	$ret = lxshell_return("yum", "-y", "install", "bind", "bind-chroot");
	if ($ret) { throw new lxexception('install_bind_failed', 'parent'); }
	lxshell_return("chkconfig", "named", "on");
    $pattern = 'include "/etc/global.options.named.conf";';
    $file = "/var/named/chroot/etc/named.conf";
    $comment = "//Global_options_file";
    addLineIfNotExistInside($file, $pattern, $comment);
    $options_file = "/var/named/chroot/etc/global.options.named.conf";

    $example_options  = "acl \"lxcenter\" {\n";
    $example_options .= " localhost;\n";
    $example_options .= "};\n\n";
    $example_options .= "options {\n";
    $example_options .= " max-transfer-time-in 60;\n";
    $example_options .= " transfer-format many-answers;\n";
    $example_options .= " transfers-in 60;\n";
    $example_options .= " auth-nxdomain yes;\n";
    $example_options .= " allow-transfer { \"lxcenter\"; };\n";
    $example_options .= " allow-recursion { \"lxcenter\"; };\n";
    $example_options .= " recursion no;\n";
    $example_options .= " version \"LxCenter-1.0\";\n";
    $example_options .= "};\n\n";
    $example_options .= "# Remove # to see all DNS queries\n";
    $example_options .= "#logging {\n";
    $example_options .= "# channel query_logging {\n";
    $example_options .= "# file \"/var/log/named_query.log\";\n";
    $example_options .= "# versions 3 size 100M;\n";
    $example_options .= "# print-time yes;\n";
    $example_options .= "# };\n\n";
    $example_options .= "# category queries {\n";
    $example_options .= "# query_logging;\n";
    $example_options .= "# };\n";
    $example_options .= "#};\n";
    if (!lfile_exists($options_file)) {
        touch($options_file);
        chown($options_file, "named");
    }
    $cont = lfile_get_contents($options_file);
    $pattern = "options";
    if (!preg_match("+$pattern+i", $cont)) {
        file_put_contents($options_file, "$example_options\n");
    }
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
	
	$defaultTtl = $this->main->ttl;
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

	if ($this->main->soanameserver) {
		$nameserver = $this->main->soanameserver;
	}

	// 2010-06-25 LN: Added SOA values
	$email = isset($this->main->email) && strlen($this->main->email) > 0 ? str_replace("@", ".", $this->main->email) : $this->main->__var_email;
	$refresh = isset($this->main->refresh) && strlen($this->main->refresh) > 0 ? $this->main->refresh : self::DEFAULT_REFRESH;
	$retry = isset($this->main->retry) && strlen($this->main->retry) > 0 ? $this->main->retry : self::DEFAULT_RETRY;
	$expire = isset($this->main->expire) && strlen($this->main->expire) > 0 ? $this->main->expire : self::DEFAULT_EXPIRE;
	$minimum = isset($this->main->minimum) && strlen($this->main->minimum) > 0 ? $this->main->minimum : self::DEFAULT_MINIMUM;
	
	$string = <<<STR

@   IN  SOA {$nameserver}. {$email}. (
			{$this->main->__var_ddate}  ; Serial
            $refresh   ; Refresh
            $retry    ; Retry
            $expire  ; Expire
            $minimum ) ; Minimum

STR;


	$fdata .= $string;
	$starvalue = null;

	$fdata .= $dnsdata ;
	


	foreach($this->main->dns_record_a as $k => $o) {
		// 2010-06-25 LN: Get TTL from RR or set it to default
		// For all the rr's, the code is changed to set TTL
      $ttl = isset($o->ttl) && strlen($o->ttl) ? $o->ttl : $this->main->ttl;
		switch($o->ttype) {

			case "ns":
				//$tmp = "{$domainname} IN  NS  $o->param.  \n";
				//$fdata .= $tmp;
				break;

			case "mx":
				$v = $o->priority;
				$tmp= "$domainname.      $ttl IN  MX $v {$o->param}. \n";
				$fdata .= $tmp;
				break;


			case "aaaa":
				$key = $o->hostname;
				$value = $o->param;

				if ($key === '*') {
					$starvalue .= "* $ttl IN AAAA $value\n";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.$domainname.";
				} else {
					$key = "$domainname.";
				}

				$tmp= "$key\t\t IN $ttl AAAA   $value \n";
				$fdata .= $tmp;
				break;

			case "ddns":
				if ($o->offline === 'on')
					break;

			case "a":
				$key = $o->hostname;
				$value = $o->param;

				if ($key === '*') {
					$starvalue = "* $ttl IN A $value\n";
					break;
				}

				if ($key !== "__base__") {
					$key = "$key.$domainname.";
				} else {
					$key = "$domainname.";
				}

				$tmp= "$key\t\t $ttl IN  A   $value \n";
				$fdata .= $tmp;
				break;

			case "cn":
			case "cname":


				$key = $o->hostname;
				$value = $o->param;

				if (isset($arecord[$value])) {
					$rvalue = $arecord[$value];

					if ($key === '*') {
						$starvalue .= "*\t$ttl\tIN A $rvalue\n";
						break;
					}
					$key .= ".$domainname.";

					$tmp= "$key\t\t$ttl\tIN  A   $rvalue \n";
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
				$tmp= "$key\t$ttl\tIN  CNAME  $value\n" ;
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

				$tmp= "$key\t$ttl\tIN  CNAME  $value\n" ;
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

				$tmp= "$key\t$ttl\tIN TXT  \"$value\"\n" ;
				$fdata .= $tmp;
				break;
		case "srv":
			$key = $o->hostname;
			if($o->param === null) continue;	

			if ($key !== "__base__") {
				$key = "$key.$domainname";
			} else {
				$key = "$domainname";
			}
			$weight = ($o->weight == null || strlen($o->weight) == 0) ? 0 : $o->weight;
			$fdata .= "_{$o->service}._{$o->proto}.$key. $ttl IN SRV {$o->priority} $weight {$o->port} {$o->param}.\n";

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

	

	$dlistv = "__var_domainlist_{$this->main->__var_syncserver}";
	$result = $this->main->$dlistv;

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
