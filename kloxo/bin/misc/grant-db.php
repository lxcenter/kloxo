<?php 

include_once "lib/include.php";

error_reporting(E_ALL);
initProgram('admin');


$sq = new Sqlite(null, 'mysqldb');
$res = $sq->getTable();
if ($res) foreach($res as $r) {
	$db = new Mysqldb(null, $r['syncserver'], "aaa");
	$db->dbtype = 'mysql';
	$dbadmin = $db->getDbAdminPass();
	mysql_connect($r['syncserver'], $dbadmin['dbadmin'], $dbadmin['dbpassword']);
	mysql_query("grant all on {$r['dbname']}.* to {$r['username']}@localhost");
	mysql_query("grant all on {$r['dbname']}.* to {$r['username']}@'%'");
}

