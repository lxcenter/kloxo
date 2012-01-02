<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');


print("Fixing the admin\n");

$list = $login->getList('domain');

foreach($list as $l) {
	$web = $l->getObject('web');
	$web->username = 'admin';
	$web->setUpdateSubaction('full_update');
	$web->was();
}

lxfile_unix_chown_rec("/home/admin/domain/", "admin:admin");
lxshell_return("lphp.exe", "../bin/misc/fixftpuserclient.phps");

