<?php 

class web__iis extends lxDriverClass {



function createShowAlist(&$alist, $subaction = null)
{
	if ($this->main->priv->isOn('dotnet_flag')) {
		$alist[] = "a=updateform&sa=update&o=aspnet";
	}
	$alist[] = "a=list&c=odbc";

	return $alist;

}





function checkIISnFTP()
{
	$obj = new lxCOM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$serv = $obj->execQuery("select * from Win32_Service where Name = 'IISADMIN'");
	foreach ($serv as $objService) {
		if($objService->State == 'Running') {
			$serv = $obj->execQuery("select * from Win32_Service where Name = 'MSFtpsvc'");
			foreach ($serv as $objService) {
				if($objService->State=='Running') {
					return true;
				}
			}
		}
	}

	return false;
}



function dbactionAdd()
{

	//$this->checkDomainIIS($this->main->nname);
	
	
	$ret = $this->createdom();



	/*if ($this->main->priv->isOn('dotnet_flag')){
		$this->EnableDotNet();
	}*/


	return $ret;
    
}


function createdom()
{

	global $gbl, $sgbl, $login, $ghtml; 
	$chDomain = true;


	$base = "c:/webroot";

	$obj = new lxCOM("winmgmts://./root/WebAdministration");
	$iiso = $obj->com_get("Site.Name='$progname'");
	if (!$iiso) {
		$iisdfn = $obj->get("Site");
		$iisdfn->Create($progname, $serverbind, $homedir); 
		$iiso = $obj->com_get("Site.Name='$progname'");
	}

	try {
		$obj->Get("ApplicationPool")->Create($this->main->username);
	} catch (exception $e) {
	}


	$app = $obj->get("ApplicationPool.Name='{$this->main->username}'");
	$app->ProcessModel->IdentityType = 3;
	$app->ProcessModel->UserName = "lxlabs";
	$app->ProcessModel->Password = "hellfire";
	$app->Put_();
	$this->main->createDir();
    
	//$this->main->username;

	$homedir = $this->main->getFullDocRoot();
	$name = $this->main->nname;

	$homedir = remove_extra_slash($homedir);
	$homedir = convertTobackSlash($homedir);
	$serverbind = $this->getServerBindings("80");
	//$securebind = $this->getServerBindings("443");




	$iiso->ApplicationDefaults->ApplicationPool = $this->main->username;

	$exec = "c:/Program Files/lxlabs/ext/php/php.exe";
	$oHandler = $obj->Get("HandlerAction")->SpawnInstance_();
	$oHandler->Name = "php";
	$oHandler->Path = "*.php";
	$oHandler->Verb = "GET,POST";
	$oHandler->ScriptProcessor = "\"$exec\"";

	$handle = new Variant(NULL);
	$iiso->getSection("HandlersSection", $handle);
	try {
		$handle->Add("Handlers", $oHandler);
	} catch (exception $e) {
	}
	$handle->Refresh_();

	$iiso->LogFile->Directory = "c:/webdata/{$this->main->nname}/stats/";
	try {
		$iiso->Put_();
	} catch (exception $e) {
	}


	$site->Put("KeyType", "IIsWebServer");
	//$site->Put("logfile", "$base/{$this->main->nname}/stats/access.log");
	$site->Put("ServerState", 2);
	$site->Put("FrontPageWeb", 1);
	$site->DefaultDoc = implode(",", array("index.htm", "index.html", "Default.aspx", "Default.asp"));
	$site->Put("SecureBindings", $securebind);
	$site->Put("ServerAutoStart", 1);
	$site->Put("ServerSize", 1);

	$site->SetInfo();

	//setting Log Files





	// Create application virtual directory

	$siteVDir = new lxCOM("IIS://localhost/w3svc/$id/Root");
	$siteVDir->AppIsolated = array(2);
	$siteVDir->Path = array($homedir);
	$siteVDir->AccessFlags = array(513);
	$siteVDir->FrontPageWeb = array(1);
	$siteVDir->AppRoot = array("/LM/W3SVC/$id/Root");
	$siteVDir->AppFriendlyName = array("Root");


}


function dbactionDelete()
{
	$iis = new lxCOM("IIS://localhost/W3SVC");
	try {
		$s= $iis->delete("IISWebSite", $this->main->iisid);
		//function for removing the directory for "webroot"
		$this->main->deleteDir();
		$this->DeleteFtpVirDir();
	}catch (exception $e) {
		log_error("Delete failed... {$this->main->nname} {$this->main->iisid}\n");
	}
}

function getServerBindings($port)
{
	$obj = $this->__webadminstration;


	$oBinding = $obj->get("BindingElement")->SpawnInstance_();
	$oBinding->BindingInformation = "*:$port:{$this->main->nname}";
	$oBinding->Protocol = "http";
	$varBindings[] = $oBinding;
	$sBinding = $obj->get("BindingElement")->SpawnInstance_();
	$sBinding->BindingInformation = "*:$port:www.{$this->main->nname}";
	$sBinding->Protocol = "http";
	$varBindings[] = $sBinding;

	foreach($this->main->server_alias_a as $v) {
		$oBinding = $obj->get("BindingElement")->SpawnInstance_();
		$oBinding->BindingInformation = "*:$port:{$v->nname}.{$this->main->nname}";
		$oBinding->Protocol = "http";
		$varBindings[] = $oBinding;
	}

	return $varBindings;
}

function setServerBindings()
{
	$iis = $this->__tmp_iis;

	$varBindings = $iis->ServerBindings;
	

//Set IIsWebVirtualDirObj = GetObject("IIS://localhost/W3SVC/1/Root/Scripts")

	$varBindings = $this->getServerBindings();
	dprintr($varBindings);

	$iis->ServerBindings=$varBindings;
	$iis->SetInfo();

}

//AddUserToFolder($strUser, $strFolderPath)

function EnableDotNet()
{

	$iis = new lxCOM("IIS://localhost/W3SVC/{$this->main->iisid}");

	$list[]="";
    $aspiDllPath = $this->getAspNetDllPath();
	$list[] =".asax,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".ascx,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".ashx,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".asmx,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".aspx,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".axd,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".vsdisco,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".rem,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".soap,".$aspiDllPath.",1,GET,HEAD,POST,DEBUG";
	$list[]=".config,".$aspiDllPath .",5,GET,HEAD,POST,DEBUG";
	$list[]=".cs,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".csproj,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".vb,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".vbproj,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".webinfo,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".licx,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";
	$list[]=".resx,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";				      
	$list[]=".resources,".$aspiDllPath.",5,GET,HEAD,POST,DEBUG";

	$iis = new lxCOM("IIS://LocalHost/w3svc/{$this->main->iisid}/root");

	$ScriptMaps = convertCOMarray($iis->Scriptmaps);
	 
	$newmap = lx_array_merge(array($ScriptMaps, $list));
	$iis->ScriptMaps = $newmap;
	$iis->SetInfo();
}

function getAspNetDllPath()
{
	$keyPath = ("HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\ASP.NET\\");

	$objReg = new lxCOM("WScript.Shell");

	try {
		$aspnetVer  = $objReg->RegRead($keyPath."RootVer");
	} catch(exception $e) {
		throw new lxexception("asp_net_not_installed");
	}

	try {
		$aspiDllPath = $objReg->RegRead($keyPath.$aspnetVer."\DllFullPath");
	} catch(exception $e) {
		dprint($e->getMessage() ."\n");
	}

	return $aspiDllPath;
}

function DisableDotNet()
{
 
   $iis = new lxCOM("IIS://LocalHost/w3svc/{$this->main->iisid}/root");

   $aspiDllPath = $this->getAspNetDllPath();
   $ScriptMaps = convertCOMarray($iis->Scriptmaps);
   $l = sizeof($ScriptMaps);
   $j = 0;
   for ($i = 0; $i < $l; $i++) {
        $str = $ScriptMaps[$i];
		//print($str);
		$pos = stristr($str, $ASPVar);
		if($pos=== false) {
			print("not");
			$newmap[$j] = $str;
			print($str);
			$j++;
		} else {
			print("YES");
		}
   }
   $iis->ScriptMaps = $newmap;
   $iis->SetInfo();
   Print("Done");


}


function EnableCGI()
{
 	$this->__base = "c:/webroot";
	$base = $this->__base;
	$homedir = "$base/{$this->main->nname}/{$this->main->nname}/";

	if (!lxfile_exists("$homedir/cgi-bin")) {
		if(!lxfile_mkdir("$homedir/cgi-bin")) {
			return;
		}
	}


	$oMsW3svc = new lxCOM("IIS://LocalHost/w3svc/{$this->main->iisid}/root");
	$k=0;
	foreach($oMsW3svc as $i){
		if($i->name == "cgi-bin"){
			$k=1;
			break;
		}
	}

	if ($k==0){
		print("Mahantesh1\n");
		$oIIRED = $oMsW3svc->Create("IIsWebDirectory", "cgi-bin");
	} else {
		$oIIRED = new lxCOM("IIS://LocalHost/w3svc/{$this->main->iisid}/ROOT/cgi-bin");
		print("Mahantesh2\n");
	}

	if ($this->main->priv->isOn('enable_cgi')) {
		$oIIRED->AccessExecute = True;
		$oIIRED->Accesswrite = True;
		$oIIRED->AccessScript = False;
	} else {
		$oIIRED->AccessExecute = False;
		$oIIRED->Accesswrite = False;
		$oIIRED->AccessScript = True;
	}

	$oIIRED->SetInfo();
	print("done\n");
}




function frontPageEnable()
{

	$fpextpath = chr(34) . "C:/Program Files/Common Files/Microsoft Shared/web server extensions/50/bin/owsadm.exe" . chr(34);
	$iis = new lxCOM("IIS://localhost/W3SVC/{$this->main->iisid}");
	$password = $this->main->__var_sysuserpassword['realpass']? $this->main->__var_sysuserpassword['realpass']: 'something';         

	if ($this->main->priv->isOn('frontpage_flag')){

		$params = " -o install -u ".$this->main->username." -pw ".$password." -p 80 -m ".$this->main->nname;
	}
	else{
		$params = " -o uninstall  -u ".$this->main->username." -pw ".$password." -p 80 -m ".$this->main->nname; 
	}
	$command = $fpextpath.$params;
	print($command . "\n");
	$whs = new lxCOM("WScript.Shell");
	try{
		$whsRun = $whs->Run($command . " > tmp.txt", 0, True);
		print("FrontPage Done");
	}
	catch (exception $e){
		print($e->getMessage() . "\n");
	}
}

function AddRedirect()
{
	$vlist = $this->main->__t_new_redirect_a_list;
	foreach($vlist as $v) {
		$id = $this->main->iisid;
		$v->nname;
		$v->redirect;
		dprint_r($v->nname);
		$siteVRootDir = new lxCOM("IIS://localhost/w3svc/$id/Root");
		$vdir = $siteVRootDir->create("IIsWebVirtualDir", $v->nname);
		$vdir->AppIsolated = array(2);
		$vdir->Path = array("$homedir/$v->nname");
		$vdir->AccessFlags = array(513);
		$vdir->FrontPageWeb = array(1);
		$vdir->AppRoot = array("/LM/W3SVC/$id/Root/{$v->nname}");
		$vdir->AppFriendlyName = array("Script");
		$vdir->Put("HttpRedirect", $v->redirect);
		$vdir->SetInfo();
	}


			/*	$v = $this->main->__t_new_redirect_a;
			$id = $this->main->iisid;
			$v->nname;
			$v->redirect;
			dprint_r($v->nname);
			$siteVRootDir = new COM("IIS://localhost/w3svc/$id/Root");
			$vdir = $siteVRootDir->create("IIsWebVirtualDir", $v->nname);
			$vdir->AppIsolated = array(2);
			$vdir->Path = array("$homedir/$v->nname");
			$vdir->AccessFlags = array(513);
			$vdir->FrontPageWeb = array(1);
			$vdir->AppRoot = array("/LM/W3SVC/$id/Root/{$v->nname}");
			$vdir->AppFriendlyName = array("Script");
			$vdir->Put("HttpRedirect", $v->redirect);
			$vdir->SetInfo();

			/*	$providerObj = new COM("winmgmts://MyServer/root/MicrosoftIISv2"); 
			$IIsWebVirtualDirObj =$providerObj->get("IIsWebVirtualD	irSetting='W3SVC/1/Root/Scripts'"); */


}

function DeleteRedirect()
{
	// clear existing redirects.
	$id = $this->main->iisid;

	//  $IIsWebVirtualDirObj = new COM("IIS://localhost/W3SVC/$id/Root/{$v->nname}");
	//      $IIsWebVirtualDirObj->PutEx(ADS_PROPERTY_CLEAR, "HttpRedirect", null);
	//      $IIsWebVirtualDirObj->SetInfo();

	$siteVRootDir = new lxCOM("IIS://localhost/w3svc/$id/Root");
	foreach($this->main->__t_delete_redirect_a_list as $v) {
		print($v->nname);
		$vdir = $siteVRootDir->Delete("IIsWebVirtualDir", $v->nname);
	}
}

function setCustomError()
{
	$iis = new lxCOM("IIS://localhost/W3SVC/{$this->main->iisid}");
	print($this->main->iisid);
	
	//	print($iis);
	 
	$err = array("400", "401", "403", "404", "500");
	$objlist = $iis->Get('HttpErrors');
	$cm = $this->main->customerror_b;
	foreach($objlist as $o) {
		$arr[] = "$o";
	}
	foreach($err as $e) {
		$v = "url_$e";
		$match = false;
		foreach($arr as &$_a) {
			if (preg_match("/$e,([^,]*),/i", $_a, &$match)) {
				if ($cm->$v) {
					$_a = "$e,{$match[1]},URL,{$cm->$v}";
				}
				$match = true;
			}
		}
		if (!$match && $cm->$v) {
			$v = "url_$e";
			$arr[] = "$e,*,URL,{$cm->$v}";
		}
	}
	dprintr($arr);

	//$iis->Put("HttpErrors", $arr);
	 
	$iis->setInfo();
}

function subDominDelete($iisID)
{
	$iis = new lxCOM("IIS://localhost/W3SVC");
	$iis->delete("IISWebSite", $iisID);
}

function DeleteFtpVirDir() 
{


	try {
		$oftpsite = new lxCOM("IIS://LocalHost/Msftpsvc/1/root");
	} catch (exception $e) {
		throw new lxexception("no_default_ftp", "");
	}   

	try {
		$vdir = $oftpsite->delete("IIsftpVirtualDir", $this->main->username);
	} catch (exception $e) {
	}   

}


function frontPagePassword()
{
	$fpextpath = "C:/Program Files/Common Files/Microsoft Shared/web server extensions/50/bin/owsadm. exe";
	$iis = new lxCOM("IIS://localhost/W3SVC/{$this->main->iisid}");


	$password = $this->main->__var_sysuserpassword['realpass']? $this->main->__var_sysuserpassword['realpass']: 'something';

	$params = " -o users -u ".$this->main->changepassword." -u ".$this->main->username." -p 80 ";
	//owsadm -o users -c <add/del/changepassword> -u <username> [-p <port>]

	
	$command = $fpextpath.$params;


	$whs = new lxCOM("WScript.Shell");
	try
	{
		$whsRun = $whs->Run($command, 0, True);
		print(" ExtendFrontPage PASSWORD  Successed");
	} catch (exception $e) {
		print(" ExtendFrontPage PASSWORD NOT  Successed");
	}

}

static function getInstalledAspVersion()
{
	return array("1.4", "2.0");
}

function do_backup()
{
	return web::do_backup($this->main->nname);
}
function do_restore()
{
	return web::do_restore($this->main->nname, $this->main->__var_machine, $this->main->__var_backupfilepass);
}

/*static function IISCertDeploy($cert,$pfx,$pwd, $iisid)
{
   $stout = lxshell_output("cscript", "-b", "C:\Program Files\IIS Resources\IISCertDeploy\IISCertDeploy.vbs -new $cert  -c $pfx -p $pwd -i W3SVC/$iisid");

}
*/


function FullUPdate()
{
	if ($this->main->ttype === 'virtual') {
		$this->createConffile();
		$this->frontPageEnable();
	} else {
		$this->createForwardconf();
	}
}


function addSSl()
{
	$old = new lxCOM("IIS://localhost/W3SVC/{$this->main->old_ssl_website_id}");
	$iid->SecureBindings(":443:");
	break;
}

function dbactionUpdate($subaction)
{


	$this->__base = "c:/webroot";
	$base = $this->__base;


	$homedir = "$base/{$this->main->nname}/{$this->main->nname}/";

	$iis = new lxCOM("IIS://localhost/W3SVC/{$this->main->iisid}");
	$this->__tmp_iis = $iis;
	switch($subaction) {
		
		case "full_update":
			$this->FullUPdate();
			break;
		
		case "iis_enable_ssl_flag":
			$this->addSSl();

		case "add_redirect_a":
			$this->AddRedirect();
			break;

		case "delete_redirect_a":
			$this->DeleteRedirect(); 
			break;
				
		case "custom_error":
			$this->setCustomError();
			print("\n");
			break;

		case "add_server_alias_a":
		case "delete_server_alias_a":
		case "ipaddress":
			$this->setServerBindings();
			break;


		case "enable_dotnet_flag":
			if ($this->main->priv->isOn('dotnet_flag')){
				$this->EnableDotNet();
			} else{
				$this->DisableDotNet();
			}

		case "enable_frontpage_flag":
			$this->frontPageEnable();
			break;

		case "frontpage_password":
			//$this->frontPagePassword();
			break;

		case "aspnet_parameters":
			$this->AspNetConfigure();
			break;

	}
}


}


