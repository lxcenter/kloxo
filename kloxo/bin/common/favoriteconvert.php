<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$login->loadAllObjects('client');

$list = $login->getList('client');

foreach($list as $k => $c) {
	$dsklist = $c->dskshortcut_a;
	foreach($dsklist as $ds) {
		$nds = new ndskShortCut(null, null, "{$ds->nname}___{$c->getClName()}");
		$nds->create($ds);
		$nds->url = $ds->nname;
		$nds->parent_clname = $c->getClName();
		$nds->vpsparent_clname = $ds->vpsparent_clname;
		$nds->external = $ds->external;
		$nds->write();
	}
}


