<?php 


php_main() ;


function php_main()
{

	global $gbl, $sgbl, $argv;


	$user = $argv[1];
	$pass = $argv[2];
	$res = preg_grep("/$user/", lfile("__path_real_etc_root/shadow"));

	foreach($res as $r) {
		$val = explode(":", $r);
	}


	print(crypt($pass, $val[1]) . " " . $val[1] . " \n");

}




