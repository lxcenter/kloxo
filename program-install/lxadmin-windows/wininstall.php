<?php

error_reporting(E_ALL);
$realpath = dirname(__FILE__);
include_once "$realpath/windows_common.php";

lxadmin_install();

function install_msicomp()
{
	print("Installing the msi components.............\n");
	$msi_path = dirname(__FILE__);
	$msi_path = "C:/lxainstall";
	mkdir($msi_path);
	chdir("C:/lxainstall");
	$curdir = getcwd();
	$wsh = new COM("WScript.Shell");
	$wsh->CurrentDirectory = $msi_path;
	$msi_array=array("perl","python");
	foreach($msi_array as $array_file) {
		$down_file = $array_file.".msi";
		print("downloading... $array_file \n");
		download_file($down_file);
		print("downloading.complete..\n");
		if($array_file == "perl"){
			$mstst = $wsh->run("msiexec /i perl.msi /qn+ TARGETDIR=C:/hhh/perl",1,True);
			print("$mstst\n");
			print("Installing perl..................\n");
			flush();
		}
		if($array_file == "python") {
			if(!is_dir("python")){
				mkdir("python");
			}
			$mstst=$wsh->run("msiexec /i python.msi /qn+ INSTALLDIR=C:/hhh/python",1,True);
			print($mstst);
			print("installing python........................\n");
			flush();
			continue;
		}
	}
	$wsh->CurrentDirectory = $curdir;
	print("Installing is done..............\n");
}


function install_dotnet()
{
	print("downloading dotnet...\n");
	download_file("dotnetfx.exe");
	$wsh = new COM("WScript.Shell");
	print("Installing dot net..................\n");
	print("installing Dotnet");
	try {
		$mstst= $wsh->Run("dotnetfx.exe /q:a /c:\"install.exe /q\"");
	} catch(exception $e){
		print("Dotnet install Failed\n");
	}
	// print($mstst); 
	print("Dot net has installed\n");
}



function add_components()
{
	$package_name=array("lxphp","lxhttpd","Multiplexer", "lxzend", "iispassword");
	$file_dir=dirname(__FILE__);
	$file_dir = "c:/lxainstall";
	foreach($package_name as $pack_insname) {
		$zip_name=$pack_insname.".zip";
		print("downloading component $pack_insname..................\n");
		download_file($zip_name);
		//$dir_path=path_info($pack_insname);
		
		$dir_path="C:/Program Files/lxlabs/ext";
		install_package($dir_path,"$file_dir/$zip_name");
		print("component $pack_insname installed...\n");
	}

	print("downloading base...\n");
	$zip_name = "base.zip";
	download_file($zip_name);
	$dir_path="C:/Program Files/lxlabs";
	install_package($dir_path, "$file_dir/$zip_name");
	print("base installed...\n");
}



function install_package($dir,$file)
{
	@ mkdir($dir);
	$wsh=new COM("WScript.Shell");
	//$wsh->run("cacls $dir /c /e /r Everyone");
	$wsh->run("cacls $dir /c /e /g Administrators:F",0,1);
	$wsh->run("cacls $dir /c /e /g System:F",0,1);
	$wsh->run("cacls $dir /c /e /g lxlabs:F",0,1);
	unzip_file($dir,$file);
	print("unzipping the file\n");
}


function AddLxadminUser()
{
	print("creatinguser \n");

	$obj = new COM("WinNT://.");
	try {
		$user = $obj->create("user", "lxlabs");
		$user->Description = "Lxadmin Admin User";
		$user->setinfo();
	} catch (exception $e) {
		print("Lxlabs Already in the system....\n");
		$user = new COM("WinNT://./lxlabs");
	}

			
}



function lxadmin_install()
{

	$in = fopen('php://stdin', 'r');
	AddLxadminUser();
	install_msicomp();
	install_dotnet();
	$dir = "C:/Program Files/lxlabs";
	//mkdir($dir);
	$wsh = new COM("WScript.Shell");
	$wsh->run("cacls $dir /c /e /g Administrators:F",0,1);
	$wsh->run("cacls $dir /c /e /g System:F",0,1);
	$wsh->run("cacls $dir /c /e /g lxlabs:F",0,1);
	add_components();

	//Downloading Lxadmin
	print("Downloading Lxadmin\n");
	$curr_dir = dirname(__FILE__);
	$curr_dir = "c:/lxainstall";
	$lxfile_path = "$curr_dir\lxadmin-current.zip";
	do_download_file("download.lxlabs.com/download/lxadmin/production/lxadmin", "lxadmin-current.zip");
	$lxinstall_path = "C:/Program Files/lxlabs/lxadmin";
	install_package($lxinstall_path, $lxfile_path);
	$wsh->currentDirectory = "C:/Program Files/lxlabs/lxadmin/httpdocs";
	$wsh->Run('"c:/Program Files/lxlabs/ext/php/php.exe"  ../bin/install/create.php --install-type=slave');
	print("Press Enter to Continue...\n");
	flush();
	fread($in, 8092);

}









