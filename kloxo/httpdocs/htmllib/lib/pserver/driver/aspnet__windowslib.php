<?php 

class aspnet__windows extends Lxdriverclass {

	function dbactionAdd()
	{

	}

	
	function dbactionDelete()
	{   

	}

	function dbactionUpdate($subaction)
	{
		print("\n #############\n");
	}

static function getAspnetVersion()
{
	//print("\nI am here\n");
	//$r=exec("c:\regASPVer.vbs");
	$cm="cmd /K CD C:\Dir";
	lxshell_return("cmd", "/K", "cd", "c:\dir");
	
	//print($r."\n");
	//$r = system('cscript.exe c:\regASPVer.vbs.vbs', $a);

	//cscript.exe regASPVer.vbs
	//regASPVer.vbs
	foreach($a as $b) {
		$res['version'] = "aab";
		$res['sss'] = 'ab';
		$ret[] = $res;
	}

}
}
