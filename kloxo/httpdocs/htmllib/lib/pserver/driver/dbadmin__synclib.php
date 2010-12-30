<?php 

class Dbadmin__sync extends lxDriverClass {



function dbactionUpdate($subaction)
{

	switch($this->main->dbtype) {
		case "mysql":
			$this->mysql_reset_pass();
			break;
	}

}

function dbactionAdd()
{
	$dbadmin = $this->main->dbadmin_name;
	$dbpass = $this->main->dbpassword;
	$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
	if (!$rdb) {
		log_error(mysql_error());
		throw new lxException('the_mysql_admin_password_is_not_correct', '', '');
	}
}

function dosyncToSystemPost()
{
	dprint("in synctosystem post\n");
	$a['mysql']['dbpassword'] = $this->main->dbpassword;
	slave_save_db("dbadmin", $a);
}

function mysql_reset_pass()
{
	$this->lx_mysql_connect("localhost", $this->main->dbadmin_name, $this->main->old_db_password);
	$res = mysql_query("set password=Password('{$this->main->dbpassword}');");
	if (!$res) {
		throw new lxException('mysql_password_reset_failed', '', '');
	}
}

function lx_mysql_connect($server, $dbadmin, $dbpass) 
{
	$rdb = mysql_connect('localhost', $dbadmin, $dbpass);
	if (!$rdb) {
		log_error(mysql_error());
		throw new lxException('could_not_connect_to_db_admin', '', '');
	}
	return $rdb;
}


}
