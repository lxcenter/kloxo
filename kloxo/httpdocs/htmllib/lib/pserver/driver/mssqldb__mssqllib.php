<?php 

class Mssqldb__mssql extends LxDriverclass {


function dbactionAdd()
{
//<<<<<<< mssqldb__mssqllib.php
	print("starting\n");
//=======

	if (!windowsOs()) {
		throw new lxException('mssql_is_only_on_windows', '', '');
	}
	print("\n mantu\n");
//>>>>>>> 1.1.2.8
	$req = mssql_connect("localhost,1433");

	if (!$req) {

		throw new lxException('MsSql Connection is Failed', '', '');
		print("Could not Connect to Database on localhost using root user\n");
	}
	$loginname=$this->main->username;
	$dbname=$loginname;
	print("\n".$loginname);
	$pass=$this->main->dbpassword;
	print("\n". $pass);
	$result = mssql_query("select loginname from syslogins where loginname='$loginname'");
	$row = mssql_fetch_array( $result, MSSQL_ASSOC );
	if( !$row ){
		mssql_query("sp_addlogin '$loginname', '$pass'");
	} else {
		throw new Exception("couldn't create $loginname--already user exist\n");
		print("user already exist by this name\n");
	}	
 print("executing\n");
	try{
		mssql_query("create database $dbname");
	}catch(Exception $e)
	{
		print("\n ERROR: Create database");
	}
	mssql_query("use  $dbname");
	mssql_query("sp_adduser '$loginname', '$loginname', 'db_owner'");
	mssql_close();
	print("\ndone\n");

}

function dbactionDelete()
{
	$srv = new COM("SQLDMO.SQLServer");
	$srv->LoginSecure = true;
	$DB =new COM("SQLDMO.Database");

	try{
		$srv->Connect("localhost,1433");
		//$srv->Connect(".");
	}catch(Exception $e)
	{
		throw new lxException('MsSql Server Connection is Failed', '', '');
	//	exit();
	}
	$strDatabaseDelete=$this->main->dbname;
	$login=$this->main->username;
	$DB = $srv->Databases($strDatabaseDelete);
	if($DB->IsUser($login) )
		$DB->Users->Remove($login);


	$srv->Databases->Remove($strDatabaseDelete,"");
	//print("\nRestored\n");


	$srv->Logins->Remove($login);
	print("\n".$login."\n");

	$srv->DisConnect();


}

function updateDatabase()
{
	$rdb = mssql_connect("localhost,1433");
	if (!$rdb) {

		throw new lxException('MsSql Connection is Failed', '', '');
		print("Could not Connect to Database on localhost using root user\n");
		//exit();
	}

	mssql_query("sp_password @old = null, @new = 'complexpwd',  @loginame ='{$this->main->username}'");

	 mssql_query("ALTER LOGIN {$this->main->username} WITH PASSWORD = '{$this->main->dbpassword}';");
 
	 print("/n Done Alter");
}

function do_restore($docd)
{
		
	$srv = new COM("SQLDMO.SQLServer");
	$srv->LoginSecure = true;
	try{
		$srv->Connect("localhost,1433");
	} catch(Exception $e) {

		throw new lxException('MsSql Connection is Failed', '', '');
	}

   	$sBAKFilePath = "C:/SQL_Backup";
	if( !is_dir( $sBAKFilePath ) ) {
		 mkdir($sBAKFilePath,0777);
	}
	 $dir= "$sBAKFilePath/mssql";
	 $mode = 0700;
     $sBAKFilePath = $dir.mt_rand(0, 9999999);
	 mkdir($sBAKFilePath, $mode);

	$docf = "$sBAKFilePath/{$this->main->dbname}.bak";
	$ret = lxshell_unzip_with_throw( $sBAKFilePath, $docd );
	
	if (!lxfile_exists($docf)) {
	   throw new lxException('could_not_find_matching_dumpfile_for_db', '', '');
	}
	$bkp = new COM("SQLDMO.Restore");
	$dbname= $this->main->dbname;
	$bkp->Database =  $dbname;
	$bkp->Files = $docf ;
	$bkp->ReplaceDatabase = True;
	$bkp->SQLRestore($srv);
	print("\nRestored\n");
	$srv->DisConnect();
	lunlink( $docf );
	lxfile_tmp_rm_rec( $sBAKFilePath );

}

function do_backup( )
{  
	$sBAKFilePath = "C:/SQL_Backup";
	if( !is_dir( $sBAKFilePath ) ) {
		mkdir( $sBAKFilePath, 0777 ) ;
 	}
	$dir="$sBAKFilePath/mssql";
	$mode = 0700;
    $sBAKFilePath = $dir.mt_rand( 0, 9999999 ); //create unique directory everytime. 
	 mkdir($sBAKFilePath, $mode);
     
	$srv = new COM("SQLDMO.SQLServer");
	$srv->LoginSecure = true;
	try{
		$srv->Connect("localhost,1433");
	} catch( Exception $e ) {
		throw new lxException('MsSql Connection is Failed', '', '');
	}
	$bkp = new COM("SQLDMO.Backup") ;
	$dbname=$this->main->dbname;
	$bkp->Database =  $dbname;
	$docf = $sBAKFilePath . "/" . $dbname. ".bak" ;
	$bkp->Files = $docf ;
	$bkp->Action = 0; //FullBackup

	$bkp->SQLBackup( $srv );
	$srv->DisConnect();
	print("$sBAKFilePath/".$dbname); 
	return  array( $sBAKFilePath, array( basename( $docf ) ) );
}

function do_backup_cleanup($list)
{
	lxfile_tmp_rm_rec($list[0]);
}


function dbactionUpdate($subaction)
{
	$this->updateDatabase();
}








}
