<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

$database = ($list['database']) ? $list['database'] : "_all_";

$pass = slave_get_db_pass();

if ($select === 'repair') {
	echo "\nMySQL database repairing...\n\n";

	if ($database === '_all_') {
		passthru("mysqlcheck --user=root --password=\"{$pass}\" --repair --all-databases");
	}
	else {
		passthru("mysqlcheck --user=root --password=\"{$pass}\" --repair --databases {$dbname}");
	}

	echo "\nMySQL repairing finished...\n\n";
}
else if ($select === 'optimize') {
	echo "\nMySQL database compacting...\n\n";

	if ($database === '_all_') {
		passthru("mysqlcheck --user=root --password=\"{$pass}\" --optimize --all-databases");
	}
	else {
		passthru("mysqlcheck --user=root --password=\"{$pass}\" --optimize --databases {$dbname}");
	}

	echo "\nMySQL compacting finished...\n\n";
}

echo shell_exec("/etc/init.d/mysqld restart")."\n";
