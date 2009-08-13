<?php 
main();

function main()
{
$v = $_REQUEST['session'];

$v = unserialize(base64_decode($v));

//print_r($v);
$r = unserialize(file_get_contents("/home/kloxo/httpd/script/sess_{$v['session']}"));
//print_r($r);

if ($r['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
	print("No Session. You can access this only through kloxo and needs proper authentication. If you are indeed accessing from Inside Kloxo, then please logout and login again, so that a new session is created properly.\n");
	exit;
} 

phpinfo();

}
