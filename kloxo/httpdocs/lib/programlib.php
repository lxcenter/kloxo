<?php 

function get_local_application_version_list()
{
	$list = allinstallsoft__linux::getListofApps();
	$list = get_namelist_from_arraylist($list); 

	foreach($list as $k => $v) {
		if (csb($v, "__title")) {
			continue;
		}
		$info = allinstallsoft::getAllInformation($v);
		$ret[$v] = $info['pversion'];
	}

	$loc= new Remote();
	$loc->applist = $ret;
	return $loc;
}


function installapp_data_update()
{
	print("Checking for old installappdata.zip...");
	if (lxfile_exists("/tmp/installappdata.zip")) {
		lxfile_rm("/tmp/installappdata.zip");
	}
	print("OK\n");
	print("Downloading InstallApp data...");
	system("cd /tmp ; wget -q http://download.lxcenter.org/download/installapp/installappdata.zip");
	if (!lxfile_exists("/tmp/installappdata.zip")) {
		print("ERROR:\n");
		print("Could not download data from LxCenter.\nAborted.\n\n");
		return;
	}
	print("OK\n");
	print("Remove old InstallApp data\n");
	lxfile_rm_rec("__path_kloxo_httpd_root/installappdata");
	lxfile_mkdir("__path_kloxo_httpd_root/installappdata");
	print("Unpack new InstallApp data\n");
	lxshell_unzip("lxlabs", "__path_kloxo_httpd_root/installappdata/", "/tmp/installappdata.zip");
	print("Remove downloaded InstallApp data zip file\n");
	lxfile_rm("/tmp/installappdata.zip");
}
