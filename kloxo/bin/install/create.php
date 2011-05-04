<?php 
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/initlib.php";
include_once "../bin/install/sql.php";
include_once "../bin/install/init.php";


create_main();

function create_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 
	$opt = parse_opt($argv);

	lxfile_mkdir("{$sgbl->__path_program_etc}/conf");
	lxfile_mkdir("{$sgbl->__path_program_root}/pid");
	lxfile_mkdir("{$sgbl->__path_program_root}/log");
	lxfile_mkdir("{$sgbl->__path_httpd_root}");

	os_fix_lxlabs_permission();
	os_create_program_service();

	if (isset($opt['admin-password'])) {
		$admin_pass = $opt['admin-password'];
	} else {
		$admin_pass = 'admin';
	}


	if ($opt['install-type'] == 'master') {
		create_mysql_db('master', $opt, $admin_pass);
		create_database();
		create_general();
		init_main($admin_pass);
		lxshell_return("__path_php_path", "../bin/collectquota.php");
		print("This will take a long time... Please wait...\n");
		system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php --type=master");
	} else if ($opt['install-type'] == 'slave') {
		init_slave($admin_pass);
		print("This will take a long time... Please wait...\n");
		system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php --type=slave");
	} else if ($opt['install-type'] == 'supernode'){
		$sgbl->__path_sql_file = $sgbl->__path_sql_file_supernode;
		$sgbl->__var_dbf = $sgbl->__path_supernode_db;
		$sgbl->__path_admin_pass = $sgbl->__path_super_pass;
		$sgbl->__var_admin_user = $sgbl->__var_super_user;

		create_mysql_db('super', $opt, $admin_pass);
		init_supernode($admin_pass);
		print("\n");
	} else {
		print("Unknown Install type\n");
		flush();
	}

	os_create_default_slave_driver_db();
}






