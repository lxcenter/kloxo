<?php 

class globalization_b extends LxaClass {
}

class aspnetmisc_b extends LxaClass {
}

class aspnet extends Lxdb {

static $__desc = array("", "",  "aspnet_version");
static $__desc_nname = array("", "",  "aspnet_version");
static $__desc_version = array("", "",  "aspnet_version");
static $__desc_encoding = array("", "",  "aspnet_version");
static $__acdesc_update_update = array("", "",  "aspnet_configuration");

function updateform($subaction, $param)
{
	/*
	$sq = new Sqlite($this->__masterserver, 'aspnet');
	$rs = $sq->getRowsWhere("parent_clname = 'pserver_s_vv_p_{$this->syncserver}'");
	foreach($rs as $r) {
		$res[] = $r['version'];
	}*/

	$domain = $this->getParentO();
	$resout = rl_exec_get(null, $domain->syncserver, array('aspnet', 'getAspnetVersion'), null);
	$res = explode("*", $resout);
	//$res = array("1.1","1.4");
	//$res = array($res); 

	foreach($res as $r) {
		$r = trim($r);
		if (!$r) {
			continue;
		}

		if (strtolower($r) === 'machineaccounts') {
			continue;
		}
		$rr[] = $r;
	}

	$vlist['version'] =  array('s', $rr);
	//$vlist['encoding'] = null;
	return $vlist;
}
static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function getAspnetVersion()
{
	//print("\nI am here\n");
	//$r=exec("c:\regASPVer.vbs");
	//$cm="cmd /K CD C:\Dir";

	//print("\n INASP VER \n");
	$stout = lxshell_output("cscript", "-b", "C:\\Program Files\\lxlabs\\kloxo\\bin\\regASPVer.vbs");

	//print("\n".$strOut."\n");
	//print($r."\n");
	//$r = system('cscript.exe c:\regASPVer.vbs.vbs', $a);

	//cscript.exe regASPVer.vbs
	//regASPVer.vbs
	/*foreach($a as $b) {
		$res['version'] = "aab";
		$res['sss'] = 'ab';
		$ret[] = $res;
	}*/
	return $stout;

}
}
