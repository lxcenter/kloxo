<?php 

class reversedns__bind extends Lxdriverclass {


function doReverseDns()
{

	addLineIfNotExistInside("__path_named_chroot/etc/named.conf", "include \"/etc/lxreverse.conf\";", "//added by hypervm");
	list($base, $end) = reversedns::getBaseEnd($this->main->nname, $this->main->__var_rdnsrange);


	$ddate = date("Ymd");

	$v = rand(0, 99);
	if ($v < 10) {
		$v = "0$v";
	}
	$ddate = "$ddate$v";
	

	$string = null;
	$string .= "\$TTL 86400\n";
	$string .= "@ IN SOA {$this->main->__var_revdns1}. root.{$this->main->__var_revdns1}. (\n";
	$string .= "$ddate ; serial\n";
	$string .= "28800 ; refresh\n";
	$string .= "14400 ; retry\n";
	$string .= "1814400 ; expire\n";
	$string .= "86400 ; default_tt\n";
	$string .= ")\n";
	$string .= "   IN NS {$this->main->__var_revdns1}.\n";

	if ($this->main->__var_revdns2) {
		$string .= "   IN NS {$this->main->__var_revdns2}.\n";
	}

	$result = $this->main->__var_reverse_list ;
	$result = $result[$base];

	$this->main->end = $end;
	$result = merge_array_object_not_deleted($result, $this->main);

	foreach($result as $k => $v) {
		$v['reversename'] = trim($v['reversename']);

		if (!cse($v['reversename'], ".")) {
			$v['reversename'] .= ".";
		}
		$string .= "{$v['end']}\tIN PTR {$v['reversename']}\n";
	}


	lfile_put_contents("__path_named_realpath/$base.in-addr.arpa", $string);

	$this->createMainFile();
}

function createMainFile()
{
	global $gbl, $sgbl, $login, $ghtml; 
	list($base, $end) = reversedns::getBaseEnd($this->main->nname, $this->main->__var_rdnsrange);
	$string = null;

	 
	$transferstring = null;
	if ($this->main->__var_transferip) {
		$transferstring = "allow-transfer { {$this->main->__var_transferip}; };";
	}
	foreach($this->main->__var_reverse_list as $k => $v) {
		if ($k === $base) {
			continue;
		}
		$string .= "zone \"$k.in-addr.arpa\" {type master; file \"$sgbl->__path_named_path/$k.in-addr.arpa\"; $transferstring};\n\n";
	}
	$string .= "zone \"$base.in-addr.arpa\" {type master; file \"$sgbl->__path_named_path/$base.in-addr.arpa\"; $transferstring};\n\n";

	lfile_put_contents("__path_named_chroot/etc/lxreverse.conf", $string);
}


function dbactionAdd()
{

	$this->doReverseDns();
}

function dbactionUpdate($subaction)
{
	$this->doReverseDns();
}

function dbactionDelete()
{
	//$this->doReverseDns();
}

function dosyncToSystemPost()
{
	global $sgbl;
	$ret =  lxshell_return("rndc", "reload");
	if ($ret) {
		createRestartFile("bind");
	}
}


}
