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
	print("Downloading Installappdata\n");
	system("cd /tmp ; rm -f installappdata.zip ; wget download.lxlabs.com/download/installapp/installappdata.zip 2>/dev/null");

	if (!lxfile_exists("/tmp/installappdata.zip")) {
		print("could not download data\n");
		return;
	}
	lxfile_rm_rec("__path_kloxo_httpd_root/installappdata");
	lxfile_mkdir("__path_kloxo_httpd_root/installappdata");
	lxshell_unzip("__system__", "__path_kloxo_httpd_root/installappdata/", "/tmp/installappdata.zip");
	lxfile_rm("/tmp/installappdata.zip");
}
