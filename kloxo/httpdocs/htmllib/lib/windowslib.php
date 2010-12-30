<?php 

include_once "htmllib/phplib/lib/windowscorelib.php";
include_once "htmllib/lib/windowsfslib.php";

function os_get_user_from_uid($uid)
{
	return "notyet";
}
function os_isSelfSystemUser()
{
	//return true;
	return false;
	$wsh = new COM("Wscript.Network");
	if ($wsh->UserName === 'SYSTEM') {
		return true;
	}
	return false;
}

function os_get_hostname()
{
	return "windows";
}

function os_create_program_service()
{
}

function os_create_system_user($basename, $password, $id, $shell, $dir = "/tmp")
{
	$uobj = new COM("WinNT://.");

	try {
		$user = new COM("WinNT://./$basename");
	} catch (exception $e) {
		$user = $uobj->create("user", $basename);
	}

	$user->setPassword($password);
	$user->setInfo();
}

function os_addto_iis()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$obj = new lxCOM("winmgmts://./root/WebAdministration");
	$iiso = $obj->com_get("Site.Name='$progname'");
	try {
		$obj->Get("ApplicationPool")->Create("kloxo");
	} catch (exception $e) {
	}

	$uobj = new lxCOM("WinNT://.");
	try {
		$user = new lxCOM("WinNT://./lxlabs");
	} catch (exception $e) {
		$user = $uobj->create("user", "lxlabs");
	}
	$user->setPassword("hellfire");
	$user->setInfo();

	$app = $obj->com_get("ApplicationPool.Name='kloxo'");
	$app->object_set("ProcessModel", "IdentityType", 3);
	$app->object_set("ProcessModel", "UserName", "lxlabs");
	$app->object_set("ProcessModel", "Password", "hellfire");
	$app->Put_();

	if ($iiso->__notreal) {
		$iisdfn = $obj->get("Site");
		$homedir = convertTobackSlash($sgbl->__path_program_htmlbase);
		$oBinding = $obj->get("BindingElement")->SpawnInstance_();
		$oBinding->BindingInformation = "*:7778:";
		$oBinding->Protocol = "http";
		$sBinding = $obj->get("BindingElement")->SpawnInstance_();
		$sBinding->BindingInformation = "*:7777:";
		$sBinding->Protocol = "https";
		$iisdfn->Create($progname, array($oBinding, $sBinding), $homedir); 
		$iiso = $obj->com_get("Site.Name='$progname'");
	}


	$iiso->object_set("ApplicationDefaults", "ApplicationPool", "kloxo");
	$iiso->Put_();

	$exec = "c:/Program Files/lxlabs/ext/php/php.exe";
	$exec = convertTobackSlash($exec);
	$oHandler = $obj->Get("HandlerAction")->SpawnInstance_();
	$oHandler->Name = "php";
	$oHandler->Path = "*.php";
	$oHandler->Verb = "GET,HEAD,POST,DEBUG";
	$oHandler->ScriptProcessor = "\"$exec\"";
	$oHandler->ResourceType = 0;
	$oHandler->Modules = "CgiModule";
	$oHandler->PreCondition = "*";

	$handle = $iiso->lxcom_getSection("HandlersSection");
	$handle->Add("Handlers", $oHandler);
	$handle->Refresh_();

	 
	//$newmap = lx_array_merge(array($ScriptMaps, $list));
	dprint("\n");
	foreach($handle->Handlers as $h) {
		dprint("$h->Name $h->Path $h->PreCondition $h->Verb $h->Modules type: $h->Type rtype: $h->ResourceType \n");
	}
}


function os_restart_program()
{
	return ;

	$objWMIService=new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$objService = $objWMIService->Get("Win32_BaseService");
	$colListOfServices = $objWMIService->ExecQuery("Select * from Win32_Service Where Name = 'LxaMultiplexer' or Name = 'LxaServer'");
	foreach( $colListOfServices as $objService) {
		$objService->stopService();
		sleep(1);
		$objService->startService();
		print("\n Done \n");
	}

}

function os_killpid_by_name($name)
{
}


function os_fix_lxlabs_permission()
{
}

function os_isSelfSystemOrLxlabsUser()
{
	$wsh = new COM("Wscript.Network");
	if ($wsh->UserName === 'SYSTEM') {
		return true;
	}
	if ($wsh->UserName === 'lxlabs') {
		return true;
	}
	return false;
}


function os_killpid()
{
}

function os_getpid()
{
}

//The first thing is open a command window and pointing out that I want to see what's happening. After |that I pipe the letter Y to the CACLS.exe by using the y| symbol. By piping Y, I avoid the "Are you  |Sure" question. Then I give the name of the folder (or file) I want to edit, followed by /E and /C.  |The /E says that I want to edit the user rights. Without the /E the rights will be overw


//- R (read only)
//- W (write only)
//- C (change (read/write))
//- F (Full Control)


function RemoveUserFromFolder($strUser, $strFolderPath)
{
	$Caclscommand = "cmd /c echo y| CACLS " . $strFolderPath;
    $Caclscommand = $Caclscommand .  " /E /C /R " . $strUser;
    $whs = new COM("WScript.Shell");
    $whsRun = $whs->Run($Caclscommand, 0, True);
}

function AddUserToFolder($strUser, $strFolderPath)
{
	$strPermission = "F";
	$Caclscommand = "cmd /c echo y| CACLS " . $strFolderPath;
    $Caclscommand = $Caclscommand . " /E /C /G " . $strUser . ":" . $strPermission;
    $whs = new COM("WScript.Shell");
    $whsRun = $whs->Run($Caclscommand, 0, True);
}

   

function convertTobackSlash($string)
{
	$newstring = preg_replace("/\//", "\\", $string);
	return $newstring;
}




function os_set_path()
{
}



