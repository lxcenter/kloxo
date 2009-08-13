<?php 

include_once "htmllib/lib/include.php"; 




cpanel_main();

function cpanel_main()
{
	$a = get_mailaccounts("ligesh.com");
	print_r($a);
	$b = get_mailaccount_password($a[0]);
	print($b . "\n");
}

function get_addon_subdomain()
{
	$list = lfile("cpanel/backup-6.12.2006_10-08-09_kloxo/addons");
	foreach($list as $l) {
		$l = trim($l);
		$ll = explode("=", $l);
		$res[] = $ll[1];
	}
	return $res;
}

function get_domain_list($domtype)
{
	$list = lfile("cpanel/backup-6.12.2006_10-08-09_kloxo/cp/kloxo");

	$i = 0;
	if ($domtype === 'addon') {
		foreach($list as $l) {
			$l = trim($l);
			if (csb($l, "DNS=")) {
				$vv = explode("=", $l);
				$domlist[$i]['name'] = $vv[1];
				$domlist[$i]['path'] = "/";
		}
		}
		$i++;
	}

	$list = lfile("cpanel/backup-6.12.2006_10-08-09_kloxo/homedir/.cpanel-datastore/apache_LISTMULTIPARKED_0");

	foreach($list as $l) {
		$l = trim($l);
		if ($l[0] === chr(30)) {
			if ($domtype !== "addon") {
				continue;
			}
		} else if ($l[0] === chr(25)) {
			if ($domtype !== "parked") {
				continue;
			}
		} else {
			continue;
		}
		$l = preg_replace("/[^-_\/.a-z0-9A-Z]/", "\n", $l);
		$l = preg_replace("/\n+/", "\n", $l);
		$l = trim($l);
		$ll = explode("\n", $l);
		$domlist[$i]['name'] = $ll[1];
		$domlist[$i]['path'] = strfrom($ll[0], "public_html");
		$i++;
	}

		

	return $domlist;

}

function get_subdomain_list()
{
	$addon = get_addon_subdomain();
	$filename='cpanel/backup-6.15.2006_08-11-26_kloxo/homedir/.cpanel-datastore/apache_LISTSUBDOMAINS_0';
	$list = file($filename);
	//print_r($list); 
	$i = 0;
	foreach($list as $l) {
		if (csb($l, "pst")) {
			continue;
		}
		$l = preg_replace("/[^-_\/.a-z0-9A-Z]/", "\n", $l);
		$l = preg_replace("/\n+/", "\n", $l);
		$l = trim($l);
		$ll = explode("\n", $l);

		if (array_search_bool($ll[1], $addon)) {
			continue;
		}
		$subdom[$i]['name'] = $ll[1];
		$subdom[$i]['path'] = $ll[0];
		$i++;

	}		
	return $subdom;
}


function get_mailaccounts($domainname)
{
   $path='cpanel/backup-6.12.2006_10-08-09_kloxo/homedir/etc';
   $filename = "$path/$domainname/passwd";
   $list = file($filename);
   foreach($list as $l) {
	   $string = explode(':',$l);
	   $accname = $string[0];
	   $array[] = "$accname@$domainname";
   }
   return $array;
}

function get_mailaccount_password($mailaccount)
{
   $path = 'cpanel/backup-6.12.2006_10-08-09_kloxo/homedir/etc';
   $chr = explode('@', $mailaccount);
   $account = $chr[0];
   $domainname = $chr[1];
   $filename = "$path/$domainname/shadow";
   $list = file($filename);
   foreach($list as $l) {
	   if (!csb($l, "$account:")) {
		   continue;
	   }
	   $cont = explode(':', $l);
	   $pwd = $cont[1];
	   return $pwd;
   }
}


function cpanel_old_main()
{
	global $argc, $argv;

	initProgramlib('admin');
	$v = tempnam("/tmp", "cpanel-backup");
	unlink($v);
	mkdir($v);

	$clientname = $argv[2];

	system("tar -C $v -xzf {$argv[1]}");

	$dir = strtil(basename($argv[1]), ".tar.gz");

	dprint("$v/$dir");
	chdir("$v/$dir");
	

	$client = new Client(null, null, $clientname);
	$client->initThisDef();
	$client->ttype = 'customer';
	$client->cttype = 'customer';
	$client->password = file_get_contents('shadow');
	$client->parent_clname = createParentName("client", 'admin');


	get_account_limits($client);


	$list = lscandir_without_dot_or_underscore("homedir/mail");

	foreach($list as $k => $l) {
		if (!is_dir("homedir/mail/$l")) {
			unset($list[$k]);
		}
	}

	foreach($list as $l) {
		$dom = new domain(null, "localhost", $l);
		$mmail = new Mmail(null, 'localhost', $l);
		$mmail->initThisDef();
		$mailaccount = new Mailaccount($this->__masterserver, $this->__readserver, "postmaster@$l");
		$mailaccount->initThisDef();
		$mailaccount->__parent_o = $mmail;
		$mailaccount->postAdd();
		$mailaccount->syncserver = $mmail->syncserver;
		$mailaccount->password = $uuser->password;
		$mailaccount->realpass = $uuser->realpass;
		//$mailaccount->metadbaction = 'writeonly';
		$mailaccount->parent_clname = $mmail->getClName();
		$mmail->addToList('mailaccount', $mailaccount);
	}


	dprintr($client->priv);
	$client->was();
	print("\n\n");

	lxfile_tmp_rm_rec($v);


 



}

function get_account_limits($client)
{
	$array = array('MAXSQL' => 'mysqldb_num', 'MAXFTP' => 'ftpuser_num', 'MAXPOP' => 'mailaccount_num', "MAXPARK" => 'parked_domain', "MAXADDON" => 'domain_num', "MAXLST" => "mailinglist_num", "BWLIMIT" => 'traffic_usage', "STARTDATE", "start_date");

	$list = file("cp/{$client->nname}");

	foreach($list as $l) {
		$l = trim($l);
		if (!$l) {
			continue;
		}

		$ll = explode("=", $l);

		if (isset($array[$ll[0]])) {
			$client->priv->{$array[$ll[0]]} = $ll[1];
		}
	}

	if (!is_unlimited($client->priv->domain_num)) {
		$client->priv->domain_num += $client->priv->parked_domain;
	}
	$client->priv->traffic_usage /= 1000 * 1000;
	$client->ddate = $client->priv->start_date;
}
