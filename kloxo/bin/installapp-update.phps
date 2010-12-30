<?php 

include_once "htmllib/lib/include.php"; 


installapp_update_main();


function installapp_update_main()
{
	// check/install/update installapp applications
	if (lxfile_exists("/home/kloxo/httpd/installapp") || lxfile_exists("/home/kloxo/httpd/remote-installapp")) {
		application_update();
	}

        // check/install/update installapp data
        installapp_data_update();

}


function application_update()
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

	print(fill_string("Fetch local InstallApp version", 50));
	$loc = get_local_application_version_list();
	$locver = $loc->applist['installapp'];
	print(" OK version is $locver\n");

	$updatelist = null;
	$notexisting = null;
	foreach($rmt->applist as $k => $v) {
		if ($k === 'installapp') { continue; }

		if (lxfile_exists("/home/kloxo/httpd/remote-installapp")) {
			if (!lxfile_exists("/home/kloxo/httpd/remote-installapp/$k.zip")) {
				$notexisting[$k] = true;
				continue;
			}
		} else {
			if (!lxfile_exists("/home/kloxo/httpd/installapp/$k")) {
				$notexisting[$k] = true;
				continue;
			}
		}
		if (app_version_cmp($loc->applist[$k], $v) === -1) {
			$updatelist[$k] = $v;
			continue;

		}

		$string = "Checking application $k";
		$string = fill_string($string, 50);
		$string .= " ";
		print($string);
		print("Is latest version $v\n");

	}

	foreach((array) $updatelist as $k => $v) {
		$string = "Updating application $k";
		$string = fill_string($string, 50);
		print("$string From {$loc->applist[$k]} to $v");
		update_application($k);
	}

	foreach((array) $notexisting as $k => $v) {
		$string = "Downloading new application $k";
		$string = fill_string($string, 50);
		print("$string "); 
		update_application($k);
	}

}



function update_application($appname)
{
	if (lxfile_exists("/home/kloxo/httpd/remote-installapp/")) {
		update_remote_application($appname);
	} else {
		do_update_application($appname);
	}
}


function do_update_application($appname)
{
	if (!$appname) { return; }
	if (lxfile_exists("/tmp/".$appname.".zip")) {
	lxfile_rm("/tmp/".$appname.".zip");
	}
	system("cd /tmp ;  wget -q http://download.lxcenter.org/download/installapp/".$appname.".zip");
	if (!lxfile_real("/tmp/".$appname.".zip")) { 
		print("Could not download $appname\n");
		return; 
	}
	lxfile_rm_rec("/home/kloxo/httpd/installapp/$appname");
	system("cd /home/kloxo/httpd/installapp ; unzip -qq /tmp/".$appname.".zip");
	lxfile_rm("/tmp/".$appname.".zip");
	print("Download Done\n");
}

function update_remote_application($appname)
{
	if (!$appname) { return; }
        if (lxfile_exists("/tmp/".$appname.".zip")) {
        lxfile_rm("/tmp/".$appname.".zip");
        }
	system("cd /tmp ; wget -q http://download.lxcenter.org/download/installapp/$appname.zip");
	if (!lxfile_real("/tmp/$appname.zip")) { 
		print("Could not download $appname\n");
		return; 
	}
	$app = "/home/kloxo/httpd/remote-installapp/$appname.zip";
	lxfile_rm($app);
	lxfile_mv("/tmp/$appname.zip", $app);
	print("Download Done\n");
}

