<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = $list['select'];

$dbname = ($list['database']) ? $list['database'] : "*";

$pass = slave_get_db_pass();

if ($select === 'repair') {
	echo "\nMySQL database repairing...\n\n";
	if ($dbname === '*') {
		system("mysqlcheck --user=root --password=\"{$pass}\" --repair --all-databases");
	}
	else {
		system("mysqlcheck --user=root --password=\"{$pass}\" --repair --databases {$dbname}");
	}
	echo "\nMySQL repairing finished...\n\n";
}
else if ($select === 'optimize') {
	echo "\nMySQL database compacting...\n\n";
	if ($dbname === '*') {
		system("mysqlcheck --user=root --password=\"{$pass}\" --optimize --all-databases");
	}
	else {
		system("mysqlcheck --user=root --password=\"{$pass}\" --optimize --databases {$dbname}");
	}
	echo "\nMySQL repairing finished...\n\n";
}
