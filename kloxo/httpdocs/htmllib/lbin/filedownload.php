<?php 

chdir("../../");
include_once "htmllib/lib/displayinclude.php";

$info = unserialize(base64_decode($ghtml->frm_info));

if (!$info) {
	print("No info");
	exit;
}

$filepass = $info->filepass;


/*
$ip = $_SERVER['REMOTE_ADDR'];

if ($res['ip'] !== $ip) {
	print("You are trying to access this file from a different Ip, than the one you accessed the master with, which is prohibited <br> Possibly an attempt to hack. \n");
	exit;
}
*/

$size = $filepass['size'];

while (@ob_end_clean());                                 
header("Content-Disposition: attachment; filename={$filepass['realname']}");
header('Content-Type: application/octet-stream');
header("Content-Length: $size");
printFromFileServ('localhost', $filepass);




