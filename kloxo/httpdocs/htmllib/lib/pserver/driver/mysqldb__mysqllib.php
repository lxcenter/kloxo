<?php 

class Mysqldb__mysql extends lxDriverClass {


function lx_mysql_connect($server, $dbadmin, $dbpass) 
{
	$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
	if (!$rdb) {
		log_error(mysql_error());
		throw new lxException('could_not_connect_to_db', '', '');
	}
	return $rdb;
}

function createDatabase()
{
	dprint("here\n");
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("use mysql");
	$res = mysql_query("select * from user where User = '{$this->main->username}'");
	$ret = null;
	if ($res) {
		$ret = mysql_fetch_row($res);
	}


	if ($ret) {
		throw new lxException("database_user_already_exists__{$this->main->username}", 'username', '');
	}

	mysql_query("create database {$this->main->dbname};");
	$this->log_error_messages();

	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");

	if ($this->main->__var_primary_user) {
		$parentname = $this->main->__var_primary_user;
		mysql_query("grant all on {$this->main->dbname}.* to '{$parentname}'@'localhost';");
		mysql_query("grant all on {$this->main->dbname}.* to '{$parentname}'@'%';");
	}

	$this->log_error_messages(false);
	mysql_query("flush privileges;");
}

function extraGrant()
{
	//mysql_query("revoke show databases on *.* from '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
	$this->log_error_messages(false);
	//mysql_query("grant SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");
	$this->log_error_messages(false);
	//mysql_query("revoke show databases on *.* from '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");
	$this->log_error_messages(false);
}

function deleteDatabase()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("drop database {$this->main->dbname};");
	$this->log_error_messages(false);
	mysql_query("delete from mysql.user where user = '{$this->main->username}';");
	$this->log_error_messages(false);
	mysql_query("flush privileges;");
}

function updateDatabase()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("update mysql.user set password = PASSWORD('{$this->main->dbpassword}') where user = '{$this->main->username}';");
	$this->log_error_messages();
	mysql_query("flush privileges;");

}

function log_error_messages($throwflag = true)
{
	if (mysql_errno()) {
		dprint(mysql_error());
		log_error(mysql_error());
		if ($throwflag) {
			throw new lxException('mysql_error', '', mysql_error());
		}
	}
}

static function take_dump($dbname, $dbuser, $dbpass, $docf)
{
	// Issue #671 - Fixed backup-restore issue

	global $gbl, $sgbl, $login, $ghtml;

	$arg[0] = $sgbl->__path_mysqldump_path;
	$arg[1] = "--add-drop-table";
	$arg[2] = "-u";
	$arg[3] = $dbuser;
	$arg[4] = $dbname;

	if ($dbpass) {
		$arg[5] = "-p'{$dbpass}'";
	}
	else {
		$arg[5] = "";
	}

	$cmd = implode(" ", $arg);
/*
	$output = null;
	$ret = null;
	if (!windowsos()) {
		exec("exec $cmd > $docf", $output, $ret);
	} else {
		exec("$cmd", $output, $ret);
		file_put_contents($docf, $output);
	}
*/
	$link = mysql_connect('localhost', $dbadmin, $dbpass);
	$result = mysql_query("CREATE DATABASE IF NOT EXISTS {$dbname}", $link);

	try {
		system("{$cmd} > {$docf}");
	}
	catch (Exception $e) {
		throw new lxException('Error: ' . $e->getMessage(), $dbname);
	}

}


static function drop_all_table($dbname, $dbuser, $dbpass)
{
	$con = mysql_connect("localhost", $dbuser, $dbpass);
	mysql_select_db($dbname);
	$query = mysql_query("show tables");
	while($res = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$total[] = getFirstFromList($res);
	}
	foreach($total as $k => $v) {
		mysql_query("drop table $v");
	}
	mysql_close($con);
}

static function restore_dump($dbname, $dbuser, $dbpass, $docf)
{
	// Issue #671 - Fixed backup-restore issue

	global $gbl, $sgbl, $login, $ghtml; 

	self::drop_all_table($dbname, $dbuser, $dbpass);
/*
	// Issue #671 - how about for large data?
	$cont = lfile_get_contents($docf);

	if ($dbpass) {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $dbuser, "-p$dbpass", $dbname);
	} else {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $dbuser, $dbname);
	}
*/
	$arg[0] = $sgbl->__path_mysqlclient_path;
	$arg[1] = "-u";
	$arg[2] = $dbuser;

	if ($dbpass) {
		$arg[3] = "-p'{$dbpass}'";
	}
	else {
		$arg[3] = "";
	}

	$arg[4] = $dbname;

	try {
		system("{$cmd} < {$docf}");
	}
	catch (Exception $e) {
		throw new lxException('Error: ' . $e->getMessage(), $dbname);
	}
}

function do_backup()
{
	// Issue #671 - Fixed backup-restore issue

	global $gbl, $sgbl, $login, $ghtml; 

	$dbadmin = $this->main->__var_dbadmin;
	$dbpass = $this->main->__var_dbpassword;
	$dbname = $this->main->dbname;

	$vd = tempnam("/tmp", "mysqldump");
	lunlink($vd);
	mkdir($vd);

	$docf = "$vd/mysql-{$dbname}.dump";

	$arg[0] = $sgbl->__path_mysqldump_path;
	$arg[1] = "--add-drop-table";
	$arg[2] = "-u";
	$arg[3] = $dbadmin;

	if ($dbpass) {
		$arg[4] = "-p'{$dbpass}'";
	}
	else {
		$arg[4] = "";
	}

	$arg[5] = $this->main->dbname;

	$cmd = implode(" ", $arg);
/*
	$output = null;
	$ret = null;
	if (!windowsos()) {
		exec("exec $cmd > $docf", $output, $ret);
	} else {
		exec("$cmd", $output, $ret);
		file_put_contents($docf, $output);
	}

	if ($ret) {
		lxfile_tmp_rm_rec($vd);
		throw new lxException('could_not_create_mysql_dump', 'nname', $this->main->dbname);
	}
*/
	$link = mysql_connect('localhost', $dbadmin, $dbpass);
	$result = mysql_query("CREATE DATABASE IF NOT EXISTS {$dbname}", $link);

	try {
		system("{$cmd} > {$docf}");
	}
	catch (Exception $e) {
		lxfile_tmp_rm_rec($vd);
		throw new lxException('Error: ' . $e->getMessage(), $dbname);
	}

	return array($vd, array(basename($docf)));

}

function do_backup_cleanup($list)
{
	lxfile_tmp_rm_rec($list[0]);
}


function fix_grant_all()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%'");
	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost'");
}

function do_restore($docd)
{
	// Issue #671 - Fixed backup-restore issue

	global $gbl, $sgbl, $login, $ghtml; 

	$dbadmin = $this->main->__var_dbadmin;
	$dbpass = $this->main->__var_dbpassword;
	$dbname = $this->main->dbname;

	$vd = tempnam("/tmp", "mysqldump");

	lunlink($vd);
	mkdir($vd);

//	$docf = "$vd/mysql-{$this->main->dbname}.dump";
	$docf = "$vd/mysql-{$dbname}.dump";

	$ret = lxshell_unzip_with_throw($vd, $docd);

	if (!lxfile_exists($docf)) {
		throw new lxException('could_not_find_matching_dumpfile_for_db', '', '');
	}
/*
	// Issue #671 - how about for large data?
	$cont = lfile_get_contents($docf);

	if ($this->main->dbpassword) {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $this->main->username, "-p{$this->main->dbpassword}", $this->main->dbname);
	} else {
		$ret = lxshell_input($cont, "__path_mysqlclient_path", "-u", $this->main->username, $this->main->dbname);
	}

	if ($ret) {
		log_restore("Mysql restore failed.... Copying the mysqldump file $docf to $sgbl->__path_kloxo_httpd_root...");
		lxfile_cp($docf, "__path_kloxo_httpd_root");
		throw new lxException('mysql_error_could_not_restore_data', '', '');
	}

	lunlink($docf);
	lxfile_tmp_rm_rec($vd);
*/
	$arg[0] = $sgbl->__path_mysqlclient_path;
	$arg[1] = "-u";
	$arg[2] = $dbadmin;

	if ($dbpass) {
		$arg[3] = "-p'{$dbpass}'";
	}
	else {
		$arg[3] = "";
	}

	$arg[4] = $dbname;

	$cmd = implode(" ", $arg);

	$link = mysql_connect('localhost', $dbadmin, $dbpass);
	$result = mysql_query("CREATE DATABASE IF NOT EXISTS {$dbname}", $link);

	try {
		system("{$cmd} < {$docf}");
		lunlink($docf);
		lxfile_tmp_rm_rec($vd);
	}
	catch (Exception $e) {
		throw new lxException('Error: ' . $e->getMessage(), $dbname);
	}
}


function doSyncToSystemPre()
{
	global $gbl, $sgbl, $login, $ghtml; 
	databasecore::loadExtension('mysql');
}

function dbactionAdd()
{
	$this->createDatabase();
}

function dbactionDelete()
{
	$this->deleteDatabase();
}

function dbactionUpdate($subaction)
{
	$this->fix_grant_all();
	$this->updateDatabase();
}

}
