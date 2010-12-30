<?php 

include "htmllib/lib/include.php";

$db = new Sqlite(null, $argv[1]);

$res = $db->getTable();
$class = $argv[1];

foreach($res as $r) {
	if (isset($stored[$r['nname']])) {
		print("duplicate found {$r['nname']}\n");
		$db->rawQuery("delete from $class where nname = '{$r['nname']}'");
		$ob = new $class(null, null, $r['nname']);
		$ob->create($r);
		$ob->write();
		continue;
	}
	$stored[$r['nname']] = $r;
}

