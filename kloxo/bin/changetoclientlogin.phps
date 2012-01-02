<?php 

include_once "htmllib/lib/include.php"; 
 
initprogram('admin');

$login->loadAllObjects('client');

$list = $login->getList('client');

foreach($list as $l) {
	$l->username = str_replace(" ", "", $l->nname);
	$l->setUpdateSubaction('createuser');
	$l->was();
}


foreach($list as $c) {

	$dlist = $c->getList('domain');

	foreach($dlist as $d) {

		$w = $d->getObject('web');
		if ($w->ftpusername) {
			continue;
		}

		if (is_link("/home/httpd/$w->nname/httpdocs")) {
			continue;
		}




		$uuser = $w->getObject('uuser');
		$w->ftpusername = $w->username;
		$flist = $w->getList('ftpuser');
		$ftpuser = new Ftpuser(null, $w->syncserver, $w->ftpusername);
		$ftpuser->initThisdef();
		$ftpuser->dbaction = 'add';
		$ftpuser->syncserver = $w->syncserver;
		$ftpuser->createSyncClass();
		$clientname = $w->getRealClientParentO()->getPathFromName('nname');
		$ftpuser->realpass = $uuser->realpass;
		$w->addObject('ftpuser', $ftpuser);
		$ftpuser->password = crypt($uuser->realpass);
		$w->username = $w->getRealClientParentO()->username;
		$w->setUpdateSubaction('full_update');

		lxfile_mkdir("__path_customer_root/$clientname/domain");
		lxfile_unix_chown("__path_customer_root/$clientname", "{$w->username}:apache");
		lxfile_unix_chmod("__path_customer_root/$clientname", "750");

		print("moving $w->nname to /home/$clientname/domain\n");
		$ret = lxshell_return("mv", "/home/httpd/$w->nname/httpdocs", "/home/$clientname/domain/$w->nname");
		if ($ret) {
			print("Couldnt move $w->nname to /home/$clientname\n");
			//continue;
		}
		lxshell_return("ln", "-sf", "/home/$clientname/domain/$w->nname", "/home/httpd/$w->nname/httpdocs");
		lxfile_unix_chown_rec("/home/$clientname/domain/$w->nname", "$w->username:$w->username");


		$dirp = $w->getList('dirprotect');
		foreach($dirp as $d) {
			$d->setUpdateSubaction('full_update');
			$d->was();
		}
		$w->was();
	}
}
 

$sq = new Sqlite(null, 'client');
$sq->rawQuery("update client set username = 'admin' where nname = 'admin';");
