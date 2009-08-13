<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');
$sq = new Sqlite(null, 'ftpuser');

$list = $sq->getRowsWhere("realpass = ''");

if (!$list) { exit; }

foreach($list as $l) {
	if ($l['realpass']) { continue; }
	print("setting pass for {$l['nname']}\n");
	$name = $l['nname'];
	$pass = randomString(8);
	$sq->rawQuery("update ftpuser set realpass = '$pass' where nname = '$name'");
	lxshell_input("$pass\n$pass\n", "pure-pw", "passwd", $name, "-m");
}
