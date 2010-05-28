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
	print("Downloading InstallApp data\n");
	if (lxfile_exists("/tmp/installappdata.zip")) {
		lxfile_rm("/tmp/installappdata.zip");
	}
	system("cd /tmp ; wget -q http://download.lxcenter.org/download/installapp/installappdata.zip");

	if (!lxfile_exists("/tmp/installappdata.zip")) {
		print("Could not download data from LxCenter.\nAborted.\n\n");
		return;
	}
	lxfile_rm_rec("__path_kloxo_httpd_root/installappdata");
	lxfile_mkdir("__path_kloxo_httpd_root/installappdata");
	lxshell_unzip("__system__", "__path_kloxo_httpd_root/installappdata/", "/tmp/installappdata.zip");
	lxfile_rm("/tmp/installappdata.zip");
}
