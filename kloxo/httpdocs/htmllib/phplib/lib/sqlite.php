<?php


class Sqlite {

private $__sqtable;
static $__database  = NULL;

private $__column_type;


function __construct($readserver, $table, $force = false)
{
	global $gbl, $sgbl, $ghtml;

	$name = $sgbl->__var_program_name;

	$this->__sqtable = $table;
	$this->__readserver = 'localhost';
	$readserver = $this->__readserver;

	$fdbvar = "__fdb_{$this->__readserver}";

	if (!isset($gbl->$fdbvar) || $force) {
		if (is_running_secondary()) {
			throw new lxexception("this_is_a_running_secondary_master", '', "");
		}

		$user = $sgbl->__var_admin_user;
		$db = $sgbl->__var_dbf;
		$pass = getAdminDbPass();

		if ($sgbl->__var_database_type === 'mysql') {
			$gbl->$fdbvar = mysql_pconnect($readserver, $user, $pass);
			mysql_select_db($db);
			self::$__database = 'mysql';
		} else if ($sgbl->__var_database_type === 'mssql') {
			//print("$user, $pass <br> \n");
			//$gbl->$fdbvar = mssql_connect('\\.\pipe\MSSQL$LXLABS\sql\query');
			$gbl->$fdbvar = mssql_pconnect("$readserver,$sgbl->__var_mssqlport");
			mssql_select_db($db);
			self::$__database = 'mssql';
		} else {
			$gbl->$fdbvar = new PDO("sqlite:$db");
			self::$__database = 'sqlite';
		}

	} 



	if (!$gbl->$fdbvar) {
		print("Could not connect to Mysql server... <br> ");
		//rl_exec_get("localhost", "localhost", "restart_mysql", null);
		//sleep(8);
		//print("<script> window.location.reload(false); </script>");
		exit;

	}



	if (!$gbl->$fdbvar) {
		die("could not Open Database Connection.... Exiting... <br> \n\n");
	}

}


function reconnect()
{

	global $gbl, $sgbl, $login, $ghtml; 

	$this->__readserver = 'localhost';
	$user = $sgbl->__var_admin_user;
	$db = $sgbl->__var_dbf;
	$pass = getAdminDbPass();

	$readserver = $this->__readserver;

	$fdbvar = "__fdb_" . $this->__readserver;

	log_log("database_reconnect", "Reconnecting again");

	if ($sgbl->__var_database_type === 'mysql') {
		$gbl->$fdbvar = mysql_pconnect($readserver, $user, $pass);
		mysql_select_db($db);
		self::$__database = 'mysql';
	} else if ($sgbl->__var_database_type === 'mssql') {
		//print("$user, $pass <br> \n");
		//$gbl->$fdbvar = mssql_connect('\\.\pipe\MSSQL$LXLABS\sql\query');
		$gbl->$fdbvar = mssql_pconnect("$readserver,$sgbl->__var_mssqlport");
		mssql_select_db($db);
		self::$__database = 'mssql';
	} else {
		$gbl->$fdbvar = new PDO("sqlite:$db");
		self::$__database = 'sqlite';
	}
}


final function isLocalhost($var = "__readserver")
{

	global $gbl, $sgbl, $login, $ghtml; 
	if (isset($this->$var) && $this->$var && $this->$var != "localhost") {
		return false;
	}
	return true;
}


function rawQuery($string)
{

	$ret = $this->rl_query($string);
	return $ret;
}

function setPassword($newp)
{
	return $this->rawQuery("set password=Password('$newp');");
}


function database_query($res, $string)
{
	//log_log("dbquery", $string);
	if (self::$__database == 'mysql') {
		//print($string . "\n");
		$res = mysql_query($string, $res);
		if (!$res) {
			dprint("Mysql connection broken. Reconnecting..\n");
			debugBacktrace();
			$this->reconnect();
			$res = mysql_query($string, $res);
		}
		dprint(mysql_error());
		return $res;
	} else if (self::$__database == "mssql") {
		return mssql_query($string, $res);
	} else {
		//return $res->query($string);
		$st = $res->prepare($string);
		if ($st) {
			$v = $st->execute();
		} else {
			dprint($string);
			dprintr($res->errorInfo());
		}
		return $st;
	}

}

function database_fetch_array($query)
{
	if (self::$__database == 'mysql') {
		return mysql_fetch_array($query, MYSQL_ASSOC);
	} else if (self::$__database === 'mssql') {
		return mssql_fetch_array($query, MSSQL_ASSOC);
	} else {
		return $query->fetch(PDO::FETCH_ASSOC);
	}


}

function rl_query($string)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_{$this->__readserver}";

	$query = $this->database_query($gbl->$fdbvar, $string);

	if (!$query) {
		return 0;
	}

	if (!(is_resource($query) || is_object($query))) {
		return 0;
	}

	//dprintr($select . ' ' . $string);
	$fulresult = null;
	while ($result = $this->database_fetch_array($query)) {
		if (isset($result['nname']) && $result['nname'] === '__dummy__dummy__') {
			continue;
		}

		if (isset($result['realpass'])) {
			$value = $result['realpass'];
			if (csb($value, '__lxen:')) {
				$value = base64_decode(strfrom($value, "__lxen:"));
			}
			$result['realpass'] = $value;
		}

		$fulresult[] = $result;
	}
	return $fulresult;



}

function getRowsGeneric($string, $list = null)
{
	$ret = null;

	if ($list) {
		$select = implode(",", $list);
	} else {
		$select = "*";
	}
	$query = "select $select from $this->__sqtable $string;";
	$fulresult = $this->rl_query($query);


	/// The ser varialbles are now handled in the setfromarraa, and this saves us a lot time.

	return $fulresult;



	foreach((array) $fulresult as $result) {
		foreach($result as $key => $value) {

			if (!strncmp($key, "ser_", 4)) {
				$key = substr($key, 4);
				if ($value) {
					$res[$key] = unserialize(base64_decode($value));
					//$res[$key] = unserialize($value);
					if (!$res[$key] && strlen($value) > 30) {
						log_database("Unserialize failed for $value");
					}
				} else {
					//dprint("Serialized Value for $key  Null <br> ", 2);
					$res[$key] = NULL;
				}
			} else {
				$res[$key] = $value;
			}
		}
		$ret[] = $res;
	}

	return $ret;
}



function getClass()
{
	return 'sqlite';
}


function existInTable($var, $value)
{
	$result = $this->getRowsWhere("$var = '$value'");
	if ($result) {
		return true;
	}
	return false;
}



function getRowsWhere($string, $list = null)
{

	return $this->getRowsGeneric("where " . $string, $list);
}

function getRowsOr($field1, $value1, $field2, $value2)
{
	return $this->getRowsWhere("$field1 = '$value1' or $field2 = '$value2'");
}


function getRowAnd($field1, $value1,$field2,$value2)
{
  return $this->getRowsWhere("$field1 = '$value1' and  $field2='$value2'");
}


function getRowsNot($field, $notval) 
{
	return $this->getRowsWhere("$field != '$notval'");
}


function getRows($field, $value)
{
	return $this->getRowsWhere("$field = '$value'");
}


function getTable($list = null)
{

	return $this->getRowsGeneric("", $list); 

}


function getColumnTypes()
{

	//return getDbvariable("fieldvar", $this->__sqtable);
	global $gbl, $sgbl, $login, $ghtml; 
	$fdbvar = "__fdb_" . $this->__readserver;


	if (!$this->__column_type) {
		if ($sgbl->__var_database_type === 'mysql') {
			$result = mysql_query("SHOW COLUMNS FROM $this->__sqtable", $gbl->$fdbvar);
			if (!$result) {
				dprint("Mysql connection broken. Reconnecting..\n");
				$this->reconnect();
				$result = mysql_query("SHOW COLUMNS FROM $this->__sqtable", $gbl->$fdbvar);
			}
		} else if ($sgbl->__var_database_type === 'mssql') {
			$result = mssql_query("sp_columns $this->__sqtable", $gbl->$fdbvar);
		} else {
			$f = $gbl->$fdbvar;
			$result = $f->prepare("select * from $this->__sqtable where nname = '__dummy__dummy__' ");
			if ($result) {
				$result->execute();
			}
		}


		if (!$result) {
			return null;
		}
		
		if ($sgbl->__var_database_type === 'mysql') {
			while(($row = mysql_fetch_assoc($result))) {
				$res[$row['Field']] = $row['Field'];
			}
		} else if ($sgbl->__var_database_type === 'mssql') {
			while(($row = mssql_fetch_assoc($result))) {
				$res[$row['COLUMN_NAME']] = $row['COLUMN_NAME'];
			}
		} else {
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if (!$row) {
				return null;
			}
			foreach($row as $k => $v) {
				$res[$k] = $k;
			}
		}


		$this->__column_type = $res;
	
		//Fucking Buggy.
		 //$this->__column_type = mssql_fetch_column_types($this->__sqtable, self::$__fdb);
	}

	return $this->__column_type;
}


function escapeBack($key, $string)
{
	if (!csb($key, "text_")) {
		return $string;
	}

	return $string;
}

function createQueryStringAdd($array)
{
	$string = " ( ";
	$result = $this->getColumnTypes();

	foreach($result as $key => $val) {
		$string = $string . " $key,";
	}
	$string = preg_replace("/,$/i", "", $string);
	$string = $string . ") values(" ;

	foreach($result as $key => $val) {

		if ($key === 'realpass') {
			$rp = $array[$key];
			$rp = base64_encode($rp);
			$rp = "__lxen:$rp";
			$string = "$string '$rp',";
			continue;
		}

		$string = "$string '{$this->escapeBack($key, $array[$key])}',";

	}
	$string = preg_replace("/,$/i", "", $string);
	$string = $string . " )";

	return $string;
}

function createQueryStringUpdate($array)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$string = "";
	$result = $this->getColumnTypes();

	foreach($result as $key => $val) {
		if (isset($sgbl->__var_collectquota_run) && $sgbl->__var_collectquota_run) {
			if (!csb($key, "priv_") && !csb($key,'used_') && !csb($key, "nname") && !csb($key, "status") && !csb($key, "state") && !csb($key, "cpstatus")) {
				continue;
			}
		}

		if ($key === 'realpass') {
			$rp = $array[$key];
			if (!csb($rp, "__lxen:")) {
				$rp = base64_encode($rp);
				$rp = "__lxen:$rp";
			}
			$string[] = "$key = '$rp'";
			continue;
		}

		$string[] = "$key = '{$this->escapeBack($key, $array[$key])}'";
	}


	$string = implode(",", $string);

	return $string;
}

function getCountWhere($query)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$countres = $this->rawquery("select count(*) from $this->__sqtable where $query");
	if ($sgbl->__var_database_type === 'mysql') {
		$countres = $countres[0]['count(*)'];
	} else if ($sgbl->__var_database_type === 'mssql') {
		$countres = $countres[0]['computed'];
	} else {
		$countres = $countres[0]['count(*)'];
	}

	return $countres;
}


function getToArray($object) 
{
	$col = $this->getColumnTypes();

	foreach($col as $key => $val) {
		if (csb($key, "coma_")) {
			$cvar = substr($key, 5);
			$value = $object->$cvar;
			if ($value) {
				if (cse($key, "_list")) {
					$namelist = $value;
				} else {
					$namelist = get_namelist_from_objectlist($value);
				}
				$ret[$key] = implode(",", $namelist);
				dprint("in COma $key {$ret[$key]}<br> ");
				$ret[$key] = ",$ret[$key],";
			}
			else {
				$ret[$key] = '';
			}
		} else if (csb($key, "ser_")) {
			$cvar = substr($key, 4);
			$value = $object->$cvar;
			if ($value && isset($value->driverApp)) {
				unset($value->driverApp);
			}

			if (cse($key, "_a")) {
				if ($value) foreach($value as $kk => $vv) {
					unset($value[$kk]->__parent_o);
				}
			}

			$ret[$key] = base64_encode(serialize($object->$cvar));
			//$ret[$key] = serialize($object->$cvar);
		} else if (csb($key, "priv_q_") || csb($key, "used_q_")) {
			$qob = strtil($key, "_q_");
			$qkey = strfrom($key, "_q_");
			if ($object->get__table() === 'uuser') {
			}
			$ret[$key] = $object->$qob->$qkey;
		} else {
			if (!isset($object->$key)) {
				$object->$key = null;
			}
			if (csb($key, "text_")) {
				$string = str_replace("\\", '\\\\', $object->$key);
			} else {
				$string = $object->$key;
			}
			$ret[$key] = str_replace("'", "\'", $string);
			//$ret[$key] = $object->$key;
		}
	}
	return $ret;
}


function setRowObject($nname, $value, $object)
{

	$array = $this->getToArray($object);

	$this->setRow($nname, $value, $array);

}


function addRowObject($object)
{

	$array = $this->getToArray($object);

	$this->addRow($array);

}


function setRow($nname, $value, $array)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_" . $this->__readserver;

	if (!$this->isLocalhost()) {
		print("Major Error\n");
		exit;
	}


	$string = $this->createQueryStringUpdate($array);

	$update = "update $this->__sqtable set $string where $nname= '$value'";

	if ($array['nname'] === 'boxtrapper.com') {
		//dprint($update);
		//exit;
	}
	if (!($upd = $this->database_query($gbl->$fdbvar, $update)))
		log_database("DbError: Update Failed for $update");
	else  {
		if ($this->__sqtable !== 'utmp') {
			dprint("Success: updated " .$this->__sqtable . " for " .  $array['nname'] . "\n", 1);
		}
	}

}



function addRow($array)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_" . $this->__readserver;
	if (!$this->isLocalhost()) {
		print("Major Error\n");
		exit;
	}


	$string = $this->createQueryStringAdd($array);

	$insert = "insert into $this->__sqtable $string ;";

	//dprint($insert, 2);

	if ($ins = $this->database_query($gbl->$fdbvar, $insert)) {
		dprint("Record inserted in $this->__sqtable for {$array['nname']}\n", 1);
		//log_message("insert into {$this->__sqtable}:{$array['nname']} :::: $insert");
	} else {
		log_database("DbError: Insert Failed for {$this->__sqtable}:{$array['nname']}");
		log_bdatabase("DbError: Insert Failed for {$this->__sqtable}:{$array['nname']} $insert");
		// Not imporant... I think.. This happens mostly when they try add something twice. Let us just ignore the second time, but log it properly.
		if ($sgbl->dbg > 0) {
			//throw new lxException("db_add_failed", "{$this->__sqtable}:{$array['nname']}");
		}
		return true;
	}


}

function delRow($nname, $value)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$fdbvar = "__fdb_" . $this->__readserver;

	$delete = "delete from $this->__sqtable where $nname = '$value'";

	$delresult = $this->database_query($gbl->$fdbvar, $delete);


	 if(!$delresult) {
		 log_database("DbError: delete Failed for $delete");
	 } else {
		 dprint("Record deleted from $this->__sqtable for $nname <br>.");
	 }
 
}


}
