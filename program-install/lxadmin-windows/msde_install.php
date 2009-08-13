<?php 
$realpath = dirname(__FILE__);
include_once "$realpath/windows_common.php";

install_msde();

function install_msde()
{
	$ext_file=dirname(__FILE__)."/MSDERelA.zip";
	download_file("MSDERelA.zip");
	$install_path=dirname(__FILE__);
	$curr_path=getcwd();
	print(getcwd() . "\n");
	$wsh=new COM("WScript.Shell");

	unzip_file($install_path, $ext_file);
	print($zptst);
	$msdein_path=$install_path."/MSDERelA";
	$wsh->run("cacls $msdein_path /c /e /g System:F",0,1);
	$wsh->run("cacls $msdein_path /c /e /g Administrators:F",0,1);
	$wsh->run("cacls $msdein_path /c /e /g lxlabs:F",0,1);
	$wsh=new COM("WScript.Shell");
	$wsh->CurrentDirectory=$msdein_path;
	$zptst=$wsh->run("setup.exe /q",0,1);
    print("setup is done..............\n");
	Reg();
	$wsh->CurrentDirectory=$curr_path;

}


function Reg()
{
	$HKEY_LOCAL_MACHINE = 0x80000002;

	$oReg=new COM("winmgmts:{impersonationLevel=impersonate}!//./root/default:StdRegProv");
	$strKeyPath = "SOFTWARE\Microsoft\Microsoft SQL Server\LXLABS\MSSQLServer\SuperSocketNetLib\Tcp";
	print("Hello\n");
	try{
		$oReg->CreateKey($HKEY_LOCAL_MACHINE, $strKeyPath);
	} catch(exception $e){
		print($e->getMessage() ."\n");
	}
	print("Done\n");
	$strValueName = "TcpPort";
	$strValue = "7773";
	$oReg->SetStringValue($HKEY_LOCAL_MACHINE,$strKeyPath,$strValueName,$strValue);
}
	
