<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domain');
	$d = getFirstFromList($dlist);
	lunlink("/home/$c->nname/www");
	lxfile_symlink("/home/$c->nname/domain/$d->nname/www", "/home/$c->nname/www");
	foreach($dlist as $l) {
		$web = $l->getObject('web');
		$web->setUpdateSubaction('full_update');
		$web->was();
	}
}
