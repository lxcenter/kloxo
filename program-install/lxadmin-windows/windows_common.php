<?php 

function do_download_file($server, $file, $localfile = null)
{
	$ch =curl_init("$server/$file");
	if (!$localfile) {
		$localfile = basename($file);
	}
	$fp = fopen($localfile, "wb");
	if (!$fp) {
		print("error\n");
		return;
	}
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}

function download_file($file, $localfile = null)
{
	$server ="download.lxlabs.com/download/windows/";
	do_download_file($server, $file);
}


function path_info($package)
{
	$package_path=array('php'=>'C:\hhh\php','lxhttpd'=>'C:\hhh','MSDERelA'=>'C:\hhh');
	return($package_path[$package]);
}


function unzip_file($dir, $file)
{
	$curr_dir=getcwd();
//	chdir($dir) ||die("Can't change location to :$dir.");
	print($dir."\n");
	print($file."\n");
	$wsh=new COM("WScript.Shell");
	$wsh->CurrentDirectory=$dir;
	$wsh->run("C:/Progra~1/7-zip/7z.exe x -y $file",0,1);
	$wsh->CurrentDirectory=$curr_dir;
}


