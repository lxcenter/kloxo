<?php 

lxpackage_main();

function lxpackage_main()
{
	global $argv;

	$list = lfile_get_unserialize("__path_package_root/pkglist.lst");

	$pkg = $list['pkg'];

	//$ver = $pkg[$argv[1]);

}
