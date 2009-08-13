<?php 

function sql_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	/*
	self::$__fdb = mysql_connect($db_server, 'kloxo', getAdminPass());
	mysql_select_db($sgbl->__var_dbf);
	self::$__database = 'mysql';
	*/

	create_database();
	create_general();

}





