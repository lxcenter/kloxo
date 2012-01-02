<?php 


function windows_add_iis_filter_registry()
{
}

function windows_add_iis_filter()
{
	$FilterName = "IISPasswordFilter"; 
	$FilterPath = "C:/Progam Files/lxlabs/ext/iispassword/iispassword.dll";
	$FilterDesc = "Directory Protect Filter";

	$FiltersObj = new COM("IIS://LocalHost/W3SVC/Filters"); 
	$LoadOrder = $FiltersObj->FilterLoadOrder; 
	if ($LoadOrder != "") { 
		$LoadOrder = $LoadOrder. ","; 
	}
	$LoadOrder = $LoadOrder . $FilterName; 
	$FiltersObj->FilterLoadOrder = $LoadOrder; 
	//$FiltersObj->SetInfo(); 

/*	$match = false;
	try {
		$NewFilterObj = new COM("IIS://LocalHost/W3SVC/Filters/$FilterName");
	} catch( exception $e) {
		$match = true;
	}
	if (!$match) {
		print("$FilterName Already There\n");
		exit;
	}*/
	$FilterObj = $FiltersObj->Create("IIsFilter", $FilterName); 
	$FilterObj->FilterPath = $FilterPath; 
	$FilterObj->FilterDescription = $FilterDesc;
	$FilterObj->FilterEnabled = true;
    $FilterObj->SetInfo();
}


function os_doUpdateExtraStuff()
{
}

function os_update_server()
{
}


function os_create_kloxo_service_once()
{
	windows_create_kloxo_service();
}


function windows_delete_service()
{
	$OWN_PROCESS = 16;
	$NOT_INTERACTIVE = False;
	$NORMAL_ERROR_CONTROL = 2;
	$objWMIService=new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$objService = $objWMIService->Get("Win32_BaseService");

	$colListOfServices = $objWMIService->ExecQuery("Select * from Win32_Service Where Name = 'LxaMultiplexer' or Name = 'LxaServer'");
	foreach( $colListOfServices as $objService) {
		$objService->StopService();
		$objService->Delete();
		print("\n Done \n");
	}
}


function windows_create_kloxo_service()
{
	$OWN_PROCESS = 16;
	$NOT_INTERACTIVE = False;
	$NORMAL_ERROR_CONTROL = 2;
	$objWMIService=new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$objService = $objWMIService->Get("Win32_BaseService");

	$user = new COM("WinNT://./lxlabs");
	$user->setPassword("lxlabspass");
	$user->setInfo();

	try{

		//$objService->Create("LxaServer" ,"LxaServer" ,'"C:/Program Files/lxlabs/ext/lxhttpd/Apache2/bin/Apache.exe" -k runservice -f "c:/Program Files/lxlabs/ext/lxhttpd/Apache2/conf/httpd.conf"', $OWN_PROCESS, $NORMAL_ERROR_CONTROL, "Automatic",  $NOT_INTERACTIVE, ".\lxlabs", "lxlabspass");


		$objService->Create("LxaMultiplexer" ,"LxaMultiplexer" ,'"C:/Program Files/lxlabs/ext/Multiplexer/MultiplexerSrvc.exe"', $OWN_PROCESS, $NORMAL_ERROR_CONTROL, "Automatic", $NOT_INTERACTIVE, ".\LocalSystem", "");

	} catch(Exception $e){
		Print(" ERROR: $e");
	}

	$colListOfServices = $objWMIService->ExecQuery("Select * from Win32_Service Where Name = 'LxaMultiplexer' or Name = 'LxaServer'");
	foreach($colListOfServices as $objService) {
		$objService->StartService();
		print("\n Done \n");
	}
}

function os_fix_some_permissions()
{
}


function os_set_iis_ftp_root_path()
{
	return;
	lxfile_mkdir("c:/webroot/ftproot");
	lxfile_adduser("c:/webroot/ftproot", "Everyone", "RL");
	$oMsW3svc = new COM("IIS://LocalHost/msftpsvc/1/root");
	$oMsW3svc->Path = "c:/webroot/ftproot";
	$oMsW3svc->AccessRead = true;
	$oMsW3svc->AccessWrite = true;
	$oMsW3svc->setInfo();
}
