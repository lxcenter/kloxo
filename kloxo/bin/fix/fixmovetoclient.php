<?php 
include_once "htmllib/lib/include.php"; 

initProgram('admin');

moveToClient();
function moveToClient()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$login->loadAllObjects('ftpuser');
	$l = $login->getList('ftpuser');
	foreach($l as $b) {
		if (csb($b->parent_clname, 'web-')) {
			list($parentclass, $parentname) = getParentNameAndClass($b->parent_clname);
			$d = new Domain(null, null, $parentname);
			$d->get();
			$b->parent_clname = $d->parent_clname;
			$w = $d->getObject('web');
			$b->directory = "{$w->docroot}/{$b->directory}";
			$b->directory = remove_extra_slash($b->directory);
			$b->setUpdateSubaction();
			$b->write();
		}
	}

	$login->loadAllObjects('mysqldb');
	$l = $login->getList('mysqldb');
	foreach($l as $b) {
		if (csb($b->parent_clname, 'domain-')) {
			list($parentclass, $parentname) = getParentNameAndClass($b->parent_clname);
			$d = new Domain(null, null, $parentname);
			$d->get();
			$b->parent_clname = $d->parent_clname;
			$b->setUpdateSubaction();
			$b->write();
		}
	}
}
