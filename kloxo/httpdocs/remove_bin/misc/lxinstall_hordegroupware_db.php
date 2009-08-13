<?php
include_once "htmllib/lib/include.php"; 
$pass = slave_get_db_pass();

debug_for_backend();


$user = "root";
$host = "localhost";
$database = "horde_groupware";
	
$link = mysql_connect($host, $user,$pass);
if (!$link) {
	print("Mysql root password error\n");
	exit;
}

dprint("Granting all privileges\n");
mysql_query("create database horde_groupware");
$pass = randomString(8);
$content = lfile_get_contents("../file/horde.config.phps");
$content = str_replace("__lx_horde_pass", $pass, $content);
lfile_put_contents("/home/kloxo/httpd/webmail/horde/config/conf.php", $content);
mysql_query("grant all on horde_groupware.* to horde_groupware@localhost identified by '$pass'", $link);
mysql_query("flush privileges", $link);
		
horde_check_database($link, $database, $pass);

	
function horde_check_database($link, $database, $pass) 
{
	$found = false;
	$result = mysql_query('SHOW DATABASES;', $link);
	$pass = slave_get_db_pass();

	/*
	while( $data = mysql_fetch_row($result) ) {
		if(!strcmp($data[0],$database)) {
			print(" Database already exists\n");
			echo "exiting.........\n" ;
			$found = true;
			break;
		}
	}
*/

	dprint("We did not find our database $database\n");
	dprint("Creating a database $database\n");
	$pstring = null;
	if ($pass) { $pstring = "-p\"$pass\""; }
	system("mysql -u horde_groupware $pstring < /home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql 2>/dev/null");
	system("mysql -u horde_groupware $pstring horde_groupware < /home/kloxo/httpd/webmail/horde/turba/scripts/sql/turba.mysql.sql 2>/dev/null");

	@ mysql_select_db('horde_groupware');
	$query = "INSERT INTO horde_users (user_uid, user_pass) VALUES ('admin','21232f297a57a5a743894a0e4a801fc3')" ;
	$result = @ mysql_query($query);

}
