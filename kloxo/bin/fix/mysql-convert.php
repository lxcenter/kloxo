<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$pass = slave_get_db_pass();

$engine = ($list['engine']) ? $list['engine'] : 'MyISAM';
$database = ($list['database']) ? $list['database'] : '_all_';
$table = ($list['table']) ? $list['table'] : '_all_';
$config = ($list['config']) ? $list['config'] : 'yes';

mysql_connect('localhost', 'root', $pass);

echo "Convert to {$engine} engine start...\n";

if ($database === '_all_') {
	$dbs = mysql_query('SHOW databases');

	while ($db = mysql_fetch_array($dbs)) {
		echo "  {$db[0]} database\n";

		mysql_select_db($db[0]);

		if ($table === '_all_') {
			$tbls = mysql_query('SHOW tables');

			while ($tbl = mysql_fetch_array($tbls)) {
				if ($tbl === 'mysql') { continue; }
				if ($tbl === 'information_schema') { continue; }
				echo "    {$tbl[0]} table\n";
				mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$engine}");
			}
		}
		else {
			mysql_query("ALTER TABLE {$table} ENGINE={$engine}");
			echo "    {$table} table\n";
		}
	}
}
else {
	mysql_select_db($database);

	echo "  {$database} database\n";

	if ($table === '_all_') {
		$tbls = mysql_query('SHOW tables');

		while ($tbl = mysql_fetch_array($tbls)) {
			echo "    {$tbl[0]} table\n";
			mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$engine}");
		}
	}
	else {
		mysql_query("ALTER TABLE {$table} ENGINE={$engine}");
		echo "    {$table} table\n";
	}
}

if ($config === 'yes') {
	if ($database === '_all_') {
		$string = implode("", file("/etc/my.cnf"));
		$file = fopen("/etc/my.cnf", "w");

		$string_array = explode("\n", $string);

		$string_collect = null;

		foreach($string_array as $sa) {
			if (stristr($sa, 'skip-innodb') !== FALSE) {
				$string_collect .= "";
				continue;
			}
			if (stristr($sa, 'default-storage-engine') !== FALSE) {
				$string_collect .= "";
				continue;
			}
			$string_collect .= $sa."\n";
		}
		
		if ($engine === 'myisam') {
			$string_source = "[mysqld]\n";
			$string_replace = "[mysqld]\nskip-innodb\ndefault-storage-engine=myisam\n";
			echo "\nAdd skip-innodb and default-storage-engine={$engine} in /etc/my.cnf\n\n";
		}
		else {
			$string_source = "[mysqld]\n";
			$string_replace = "[mysqld]\ndefault-storage-engine={$engine}\n";
			echo "\nAdd default-storage-engine={$engine} in /etc/my.cnf\n\n";
		}

		$string_collect = str_replace($string_source, $string_replace, $string_collect);

		fwrite($file, $string_collect, strlen($string_collect));
	}
}

echo "Convert to {$engine} engine finish...\n\n";

// echo lxshell_return("service", "mysqld", "restart");
echo shell_exec("/etc/init.d/mysqld restart")."\n";
