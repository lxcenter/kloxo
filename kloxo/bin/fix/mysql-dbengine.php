<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$pass = slave_get_db_pass();

$dbengine = ($list['dbengine']) ? $list['dbengine'] : 'MyISAM';
$dbname = ($list['dbname']) ? $list['dbname'] : '*';
$dbtable = ($list['dbtable']) ? $list['dbtable'] : '*';

mysql_connect('localhost', 'root', $pass);

echo "Convert to {$dbengine} engine start...\n";

if ($dbname === '*') {

	$dbs = mysql_query('SHOW databases');

	while ($db = mysql_fetch_array($dbs)) {

		echo "  {$db[0]} database\n";

		mysql_select_db($db[0]);

		if ($dbtable === '*') {

			$tbls = mysql_query('SHOW tables');

			while ($tbl = mysql_fetch_array($tbls)) {
				echo "    {$tbl[0]} table\n";
				mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$dbengine}");
			}
		}
		else {
			mysql_query("ALTER TABLE {$dbtable} ENGINE={$dbengine}");
			echo "    {$dbtable} table\n";
		}

	}
}
else {
	mysql_select_db($dbname);

	echo "  {$dbname} database\n";

	if ($dbtable === '*') {
		$tbls = mysql_query('SHOW tables');

		while ($tbl = mysql_fetch_array($tbls)) {
			echo "    {$tbl[0]} table\n";
			mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$dbengine}");
		}
	}
	else {
		mysql_query("ALTER TABLE {$dbtable} ENGINE={$dbengine}");
		echo "    {$dbtable} table\n";
	}
}

echo "Convert to {$dbengine} engine finish...\n";
