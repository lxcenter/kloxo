<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$login->loadAllObjects('dns');
$list = $login->getList('dns');

foreach($list as $l) {
	$l->setUpdateSubaction('update');
	$l->was();
}

