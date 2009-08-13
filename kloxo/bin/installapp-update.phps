<?php 

include_once "htmllib/lib/include.php"; 


installapp_update_main();


function installapp_update_main()
{
	if (lxfile_exists("__path_kloxo_httpd_root/installsoft") || lxfile_exists("__path_kloxo_httpd_root/remote-installapp")) {
		application_update();
	}

	installapp_data_update();
}


function application_update()
{

	$string = file_get_contents("http://download.lxlabs.com/download/installapp/version.list");
	$rmt = unserialize($string);

	if (!$rmt) { 
		throw new lxexception("could_not_get_application_version_list", '', "");
	}

	$loc = get_local_application_version_list();

	$updatelist = null;
	$notexisting = null;
	foreach($rmt->applist as $k => $v) {
		if ($k === 'installapp') { continue; }

		if (lxfile_exists("__path_kloxo_httpd_root/remote-installapp")) {
			if (!lxfile_exists("__path_kloxo_httpd_root/remote-installapp/$k.zip")) {
				$notexisting[$k] = true;
				continue;
			}
		} else {
			if (!lxfile_exists("__path_kloxo_httpd_root/installsoft/$k")) {
				$notexisting[$k] = true;
				continue;
			}
		}
		if (app_version_cmp($loc->applist[$k], $v) === -1) {
			$updatelist[$k] = $v;
			continue;

		}

		$string = "Checking $k";
		$string = fill_string($string);
		$string .= " ";
		print($string);
		print("Latest version\n");

	}

	foreach((array) $updatelist as $k => $v) {
		$string = "Updating $k";
		$string = fill_string($string);
		print("$string From {$loc->applist[$k]} to $v... ");
		update_application($k);
	}

	foreach((array) $notexisting as $k => $v) {
		$string = "Downloading new $k";
		$string = fill_string($string);
		print("$string "); 
		update_application($k);
	}

}



function update_application($appname)
{
	if (lxfile_exists("__path_kloxo_httpd_root/remote-installapp/")) {
		update_remote_application($appname);
	} else {
		do_update_application($appname);
	}
}


function do_update_application($appname)
{
	if (!$appname) { return; }
	system("cd /tmp ; rm -f $appname.zip ; wget download.lxlabs.com/download/installapp/$appname.zip 2> /dev/null");
	if (!lxfile_real("/tmp/$appname.zip")) { 
		print("Could not download $appname\n");
		return; 
	}
	lxfile_rm_rec("__path_kloxo_httpd_root/installsoft/$appname");
	lxshell_unzip("__system__", "__path_kloxo_httpd_root/installsoft", "/tmp/$appname.zip");
	lxfile_rm("/tmp/$appname.zip");
	print("Download Done\n");
}

function update_remote_application($appname)
{
	if (!$appname) { return; }
	system("cd /tmp ; rm -f $appname.zip ; wget download.lxlabs.com/download/installapp/$appname.zip 2> /dev/null");
	if (!lxfile_real("/tmp/$appname.zip")) { 
		print("Could not download $appname\n");
		return; 
	}
	$app = "__path_kloxo_httpd_root/remote-installapp/$appname.zip";
	lxfile_rm($app);
	lxfile_mv("/tmp/$appname.zip", $app);
	print("Download Done\n");

}

