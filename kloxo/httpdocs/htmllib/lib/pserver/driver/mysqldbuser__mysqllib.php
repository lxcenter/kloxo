<?php 

class Mysqldbuser__mysql extends lxDriverClass {


function lx_mysql_connect($server, $dbadmin, $dbpass) 
{
	$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
	if (!$rdb) {
		log_error(mysql_error());
		throw new lxException('could_not_connect_to_db', '', '');
	}
	return $rdb;
}

function createUser()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("use mysql");
	$res = mysql_query("select * from user where User = '{$this->main->username}'");
	$ret = mysql_fetch_row($res);
	if ($ret) {
		throw new lxException('user_already_exists', '', '');
	}
	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'%' identified by '{$this->main->dbpassword}';");
	mysql_query("grant all on {$this->main->dbname}.* to '{$this->main->username}'@'localhost' identified by '{$this->main->dbpassword}';");
	$this->log_error_messages(false);
	mysql_query("flush privileges;");
}

function deleteUser()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	mysql_query("delete from mysql.user where user = '{$this->main->username}';");
	$this->log_error_messages(false);
	mysql_query("flush privileges;");
}

function updateDatabase()
{
	$rdb = $this->lx_mysql_connect('localhost', $this->main->__var_dbadmin, $this->main->__var_dbpassword);
	print("here\n");
	mysql_query("update mysql.user set password = PASSWORD('{$this->main->dbpassword}') where user = '{$this->main->username}';");
	$this->log_error_messages();
	mysql_query("flush privileges;");

}

function log_error_messages($throwflag = true)
{
	if (mysql_errno()) {
		dprint(mysql_error());
		if (mysql_errno() === 1007 && csa($this->main->dbname, "_")) {
			log_message("Mysql Db {$this->main->dbname} already exists. and also has an underscore... Will treat this as the main db..");
			log_error(mysql_error());
			return true;
		}

		if ($throwflag) {
			throw new lxException('mysql_error', '', mysql_error());
		}
	}
}




function doSyncToSystemPre()
{
	global $gbl, $sgbl, $login, $ghtml; 
	databasecore::loadExtension('mysql');
}

function dbactionAdd()
{
	$this->createUser();
}

function dbactionDelete()
{
	$this->deleteUser();
}

function dbactionUpdate($subaction)
{
	$this->updateDatabase();
}

}
