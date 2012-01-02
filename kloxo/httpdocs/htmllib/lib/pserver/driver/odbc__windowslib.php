<?php 

class odbc__windows extends LxDriverclass {
//for MSSql server
function DSNSQLServer()
{
	$HKEY_CLASSES_ROOT  = 0x80000000;
	$HKEY_CURRENT_USER  = 0x80000001;
	$HKEY_LOCAL_MACHINE = 0x80000002;
	$HKEY_USERS         = 0x80000003;
	$HKEY_CURRENT_CONFIG= 0x80000005;

	$detail = $this->main->odbcdetails_b;

	$DataSourceName = $this->main->odbcname;
	$DatabaseName = $detail->mssql_database;
	$DriverPath = "C:\WINDOWS\System32\sqlsrv32.dll";
	$LastUser= $detail->mssql_loginid;
	//$Server="(local)";
	$Trusted_connection="Yes";
	$Description=$detail->description.$DatabaseName;
	$DriverName="SQL Server";

//static $__desc_odbcname  = array("n","",  "odbc_name", URL_SHOW);

	$sPath = ("SOFTWARE\ODBC\ODBC.INI\\".$DataSourceName);

	//$sComputer  ="MERCURY";

	if(0 == $this->CreateRegKey($HKEY_LOCAL_MACHINE, $sPath)) {
		$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "Database", $DatabaseName);
	    $this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "Description", $Description);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "Driver", $DriverPath);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "LastUser", $LastUser);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "Server",$Server);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "Trusted_Connection",$Trusted_connection);
	
	}
	//Write in "ODBC Data Sources" Key to allow ODBC Manager list & manage the new DSN

	$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,  "SOFTWARE\ODBC\ODBC.INI\ODBC Data Sources", $DataSourceName , $DriverName);
	print("\nDone\n");
}

//Create RegKey Function

function CreateRegKey ($hTree, $sKey)
{
	//$oRegistry  = new COM("winmgmts:{impersonationLevel=impersonate}//./root/default:StdRegProv");
	$oRegistry  = new COM("winmgmts:{impersonationLevel=impersonate}//./root/default:StdRegProv");

//	!//./root/default:StdRegProv
	try {
		$lResult = $oRegistry->CreateKey($hTree, $sKey);
	} catch(exception $e) {
		print(" Key not created".$e);
	}
}


//set RegKey Function   
function RegKeyStrValue ($hTree, $sKey, $sValueName, $sValue)
{
	//$oRegistry  = 
    $oRegistry=new COM("winmgmts:{impersonationLevel=impersonate}//./root/default:StdRegProv");
	try{
		$lResult = $oRegistry->SetStringValue($hTree, $sKey,  $sValueName,  $sValue);
	}catch(exception $e){
		print( "Set Value for  sKey  Failed". $e);
	}
}


function RegKeyDWORD ($hTree, $sKey, $sValueName, $sValue)
{
	//$oRegistry  = 
	$oRegistry=new COM("winmgmts:{impersonationLevel=impersonate}//./root/default:StdRegProv");
	try{
		$lResult = $oRegistry->SetDWORDValue($hTree, $sKey,  $sValueName,  $sValue);
	}catch(exception $e){
		print( "Set Value for  sKey  Failed". $e);
	}
}


//For MSAccess database
function DSNAccess()
{
	$HKEY_CLASSES_ROOT  = 0x80000000;
	$HKEY_CLASSES_ROOT  = 0x80000000;
	$HKEY_CURRENT_USER  = 0x80000001;
	$HKEY_LOCAL_MACHINE = 0x80000002;
	$HKEY_USERS         = 0x80000003;
	$HKEY_CURRENT_CONFIG= 0x80000005;
	/*$vlist['odbcdetails_b_s_msaccess_file'] = null;
	$vlist['odbcdetails_b_s_msaccess_loginid'] = null;
	$vlist['odbcdetails_b_s_msaccess_password'] = null;
	$vlist['odbcdetails_b_s_msaccess_pagetimeout'] = null;
	$vlist['odbcdetails_b_s_msaccess_maxbuffersize'] = null;
	$vlist['odbcdetails_b_s_msaccess_readonly'] = null;
	$vlist['odbcdetails_b_s_msaccess_exclusive'] = null;
	$vlist['odbcdetails_b_s_msaccess
	$vlist['odbcdetails_b_s_msaccessimplicommitsync'] = null;
	$vlist['odbcdetails_b_s_msaccessusercommitsync'] = null;

	
	*/

	$detail = $this->main->odbcdetails_b;

	$DataSourceName = $this->main->odbcname;
	$DatabaseName = "C:\mahantesh\db1.mdb";
	$DriverPath = "C:\WINNT\System32\odbcjt32.dll";
	$Driverid=25;
	$Userid= $detail->msaccess_loginid;
	$Implicit_commit_sync= " ";//$detail->msaccessimplicommitsync;
	$Max_buffer_size=2048;//$detail->maxbuffersize;
	$Page_timeout=5;//$detail->pagetimeout;
	$Threads=3;
	$Server="(local)";
	$Safe_transactions=0;
	//$Trusted_connection="Yes";
	$User_commit_sync="Yes";//$detail->msaccessusercommitsync;
	$Description=$detail->description.$DatabaseName;
	$DriverName="Microsoft Access Driver (*.mdb)";

	//static $__desc_odbcname  = array("n","",  "odbc_name", URL_SHOW);

	$sPath = ("SOFTWARE\ODBC\ODBC.INI\\".$DataSourceName);
	$sPathEngine=("SOFTWARE\ODBC\ODBC.INI\\".$DataSourceName."\Engines\Jet");
	
	
	
	//$sComputer  ="MERCURY";



	if(0 == $this->CreateRegKey($HKEY_LOCAL_MACHINE, $sPath)) {
		//$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "Database", $DatabaseName);
		$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "DBQ", $DatabaseName);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "Driver",$DriverPath);
		$this->RegKeyDWORD ($HKEY_LOCAL_MACHINE,$sPath, "DriverId", $Userid);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "FIL",$DriverName);
		$this->RegKeyDWORD ($HKEY_LOCAL_MACHINE, $sPath, "SafeTransactions",$Safe_transactions);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "UID",$Userid);


	}
	if(0 == $this->CreateRegKey($HKEY_LOCAL_MACHINE, $sPathEngine)) {
		$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPathEngine, "ImplicitCommitSync", $Implicit_commit_sync);
		$this->RegKeyDWORD($HKEY_LOCAL_MACHINE, $sPathEngine, "MaxBufferSize",$Max_buffer_size );
		$this->RegKeyDWORD ($HKEY_LOCAL_MACHINE,$sPathEngine, "PageTimeout",$Page_timeout);
		//$this->RegKeyDWORD($HKEY_LOCAL_MACHINE,$sPathEngine, "Threads", $LastUser);
		$this->RegKeyDWORD($HKEY_LOCAL_MACHINE,$sPathEngine, "Threads", $Threads);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPathEngine, "UserCommitSync",$User_commit_sync);
		//$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "SafeTransactions",$Tconnection);
		//$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "UID",$DatabaseName);


	}


	//Write in "ODBC Data Sources" Key to allow ODBC Manager list & manage the new DSN

	$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,  "SOFTWARE\ODBC\ODBC.INI\ODBC Data Sources",$DataSourceName , $DriverName);
	print("\nDone\n");
}



function DSNMySQL()
{   
	$HKEY_CLASSES_ROOT  = 0x80000000;
	$HKEY_CURRENT_USER  = 0x80000001;
	$HKEY_LOCAL_MACHINE = 0x80000002;
	$HKEY_USERS         = 0x80000003;
	$HKEY_CURRENT_CONFIG= 0x80000005;

	$detail = $this->main->odbcdetails_b;

	$DataSourceName = $this->main->odbcname;
	$DatabaseName =  $detail->mysql_database;
	$DriverPath = "C:\WINDOWS\System32\myodbc3.dll";
	//$LastUser= $detail->mssql_loginid;
	//$Server="(local)";
	$Server= $detail->mysql_server;
	$UserID= $detail->mysql_loginid;
	$Description="{$this->main->description}.$DatabaseName";
	$UserPassword=$detail->mysql_password;

	$DriverName="MySQL ODBC 3.51 Driver";
	//static $__desc_odbcname  = array("n","",  "odbc_name", URL_SHOW);

	$sPath = ("SOFTWARE\ODBC\ODBC.INI\\".$DataSourceName);

	//th = ("SOFTWARE\ODBC\ODBC.INI\\".$DataSourceName);

								  //$sComputer  ="MERCURY";
	print("\n--DataSourceName:$DataSourceName - DatabaseName: $DatabaseName- Description:$Description- Server:$Server-- UserID:$UserID--  UserPassword:$UserPassword---------------\n");

	
	if(0 == $this->CreateRegKey($HKEY_LOCAL_MACHINE, $sPath)) {
		$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "DATABASE", $DatabaseName);
		$this->RegKeyStrValue($HKEY_LOCAL_MACHINE, $sPath, "DESCRIPTION", $Description);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "Driver", $DriverPath);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,$sPath, "PWD", $UserPassword);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "SERVER",$Server);
		$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE, $sPath, "UID",$UserID);
	}

	$this->RegKeyStrValue ($HKEY_LOCAL_MACHINE,  "SOFTWARE\ODBC\ODBC.INI\ODBC Data Sources",         $DataSourceName , $DriverName);
	print("\nDone\n");
}



function dbactionAdd()
{
	print("*************\n");
	switch($this->main->driver) {
		case "SQL Server":
			{
				$SQLServer=$this->DSNSQLServer();
				break;
			}
         case "Microsoft Access Driver":
			 {
				 $SQLServer=$this->DSNAccess();
				 break;
			 }
		 case "MySQL Server":
			 {
				 $SQLServer=$this->DSNMySQL();
				 break;
			 }

	}
}


function dbactionDelete()
{
	$HKEY_LOCAL_MACHINE = 0x80000002;

	//strComputer = "."
	 
	$objReg=new COM ("winmgmts:{impersonationLevel=impersonate}//./root/default:StdRegProv");
		 
	$strKeyPath = "SOFTWARE\ODBC\ODBC.INI\{$this->main->odbcname}";
	$objReg->DeleteKey($HKEY_LOCAL_MACHINE, $strKeyPath);

	$strKeyPath = "SOFTWARE\ODBC\ODBC.INI\ODBC Data Sources";
	$strValueName = $this->main->odbcname;
	$objReg->DeleteValue($HKEY_LOCAL_MACHINE,$strKeyPath,$strValueName);
	print("Deleted");
}

function dbactionUpdate($subaction)
{
}


}
