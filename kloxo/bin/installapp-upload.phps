<?php 

include_once "htmllib/lib/include.php"; 

if (!isset($argv[1])) {
	print("Usage: installapp-upload acccount\n");
	exit;
}

$g_acc = $argv[1];

application_upload();
data_upload();


function i_download_file($dir, $file)
{
	global $g_acc;

	system("cd $dir ; rm -f $file ; scp $g_acc@download.lxlabs.com:installapp/$file .");
}


function upload_file($file)
{
	global $g_acc;
	system("scp $file $g_acc@download.lxlabs.com:installapp/");
}


function application_upload()
{

	$loc = get_local_application_version_list();
	//dprintr($loc);

	i_download_file("/tmp", "version.list");
	$rmt = lfile_get_unserialize("/tmp/version.list");
	lxfile_rm("/tmp/version.list");


	if (!$rmt) {
		print("Could not download version list\n");
		return;
	}


	chdir("/home/kloxo/httpd/installapp/");
	$uploadlist = null;
	foreach($loc->applist as $k => $v) {
		if (app_version_cmp($rmt->applist[$k], $v) === -1) {
			$uploadlist[$k] = true;
			continue;
		}
		$string = fill_string($k);
		print("$string is same version\n");
	}

	foreach((array) $uploadlist as $k => $v) {
		$string = fill_string($k);
		print("$string is newer here. uploading it\n");
		system("zip -r $k.zip $k >/dev/null");
		upload_file("$k.zip");
		lxfile_rm("$k.zip");
	}

	print("Uploading version list\n");
	lfile_put_serialize("/home/kloxo/version.list", $loc);
	upload_file("/home/kloxo/version.list");
	lxfile_rm("/home/kloxo/version.list");
}

function data_upload()
{
	print("Uploading data\n");
	system("rm -f /home/kloxo/httpd/installappdata.zip");
	system("cd /home/kloxo/httpd/installappdata/ ; zip -r ../installappdata.zip * > /dev/null;");
	upload_file("/home/kloxo/httpd/installappdata.zip");
	lxfile_rm("/home/kloxo/httpd/installappdata.zip");
}


