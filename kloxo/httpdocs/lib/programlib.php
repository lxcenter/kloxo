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
        print(fill_string("Fetch current InstallApp version", 50));
        $string = file_get_contents("http://download.lxcenter.org/download/installapp/version.list");
        $rmt = unserialize($string);

        if (!$rmt) {
                throw new lxexception(" could_not_get_application_version_list", '', "");
        }
        print(" OK ");
        $remver = $rmt->applist['installapp'];
        print("version is $remver\n");

 if (lxfile_exists("/home/kloxo/httpd/installappdata")) {
        print(fill_string("Fetch local InstallApp version", 50));
        $loc = get_local_application_version_list();
        $locver = $loc->applist['installapp'];
        print(" OK version is $locver\n");

	if ($remver != $locver) {
	 print(fill_string("New installapp found", 50));
	 print(" OK\n");
	}
 }
	print(fill_string("Checking for old installappdata.zip", 50));
	if (lxfile_exists("/tmp/installappdata.zip")) {
		lxfile_rm("/tmp/installappdata.zip");
	}
	print(" OK\n");
	print(fill_string("Downloading InstallApp data...", 50));
	system("cd /tmp ; wget -q http://download.lxcenter.org/download/installapp/installappdata.zip");
	if (!lxfile_exists("/tmp/installappdata.zip")) {
		print(" ERROR\n");
		print("Could not download data from LxCenter.\nAborted.\n\n");
		return;
	}
	print(" OK\n");
	print(fill_string("Remove old InstallApp data", 50));

//      lxfile_rm_rec("__path_kloxo_httpd_root/installappdata");
//      lxfile_mkdir("__path_kloxo_httpd_root/installappdata");

        lxfile_rm_rec("/home/kloxo/httpd/installappdata");
        lxfile_mkdir("/home/kloxo/httpd/installapp");
	lxfile_mkdir("/home/kloxo/httpd/installappdata");
        print(" OK\n");

        print(fill_string("Unpack new InstallApp data",50));
//      lxshell_unzip("lxlabs", "__path_kloxo_httpd_root/installappdata/", "/tmp/installappdata.zip");
        system("cd /home/kloxo/httpd/installappdata ; unzip -qq /tmp/installappdata.zip");
        print(" OK\n");
 	print(fill_string("Remove downloaded InstallApp data zip file", 50));
	lxfile_rm("/tmp/installappdata.zip");
        print(" OK\n");
}
