<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

if (isset($list['server'])) { $server = $list['server']; }
else { $server = 'localhost'; }

$list = parse_opt($argv);

$select = strtolower($list['select']);

$database = (isset($list['database'])) ? $list['database'] : null;

setMysqlOptimize($select, $database);

/* ****** BEGIN - setMysqlOptimize ***** */

function setMysqlOptimize($select, $database = null)
{
	global $gbl, $sgbl, $login, $ghtml;
/*
	initProgram('admin');

	if (isset($list['server'])) { $server = $list['server']; }
	else { $server = 'localhost'; }
*/
	log_cleanup("Mysql Optimize");

	$database = ($database) ? $database : "_all_";

	$pass = slave_get_db_pass();

	if ($select === 'repair') {
		log_cleanup("- Database repairing");

		if ($database === '_all_') {
			passthru("mysqlcheck --user=root --password=\"{$pass}\" --repair --all-databases");
		}
		else {
			passthru("mysqlcheck --user=root --password=\"{$pass}\" --repair --databases {$dbname}");
		}
	}
	else if ($select === 'optimize') {
		log_cleanup("- Database compacting");

		if ($database === '_all_') {
			passthru("mysqlcheck --user=root --password=\"{$pass}\" --optimize --all-databases");
		}
		else {
			passthru("mysqlcheck --user=root --password=\"{$pass}\" --optimize --databases {$dbname}");
		}
	}

	log_cleanup("- Service restart");
	$ret = lxshell_return("service", "mysqld", "restart");
	if ($ret) { throw new lxexception('mysqld_restart_failed', 'parent'); }
}

/* ****** END - setMysqlOptimize ***** */
