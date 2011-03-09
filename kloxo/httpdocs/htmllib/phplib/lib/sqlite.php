<?php

class Sqlite
{
	private $__sqtable;
	private $__force;
	private $__column_type;
	static $__database = NULL;

	/**
	 * Creates a Sqlite instance representing a connection to a database
	 * @param string $readserver the database server hostname
	 * @param string $table table name
	 * @param bool $force
	 */
	public function __construct($readserver, $table, $force = false)
	{
		global $gbl;

		$this->__sqtable = $table;
		$this->__force = $force;
		$this->__readserver = empty($readserver) ? 'localhost' : $readserver;

		$this->connect();
	}

	/**
	 * Connects to the database
	 * @throws lxexception
	 * @return bool true if successful
	 */
	private function connect()
	{
		global $gbl, $sgbl;

		$fdbvar = "__fdb_{$this->__readserver}";

		if (! isset($gbl->$fdbvar) || $this->__force) {
			if (is_running_secondary()) {
				throw new lxexception("this_is_a_running_secondary_master", '', '');
			}
		}

		$db = $sgbl->__var_dbf;
		$user = $sgbl->__var_admin_user;
		$pass = getAdminDbPass();

		try {
			if ($sgbl->__var_database_type === 'mysql') {
				$gbl->$fdbvar = new PDO("mysql:host=$this->__readserver;dbname=$db", $user, $pass);
			} else if ($sgbl->__var_database_type === 'mssql') {
				$gbl->$fdbvar = new PDO("mssql:host=$this->__readserver,$sgbl->__var_mssqlport;dbname=$db", $user, $pass);
			} else {
				$gbl->$fdbvar = new PDO("sqlite:$db");
			}
		} catch (PDOException $e) {
			dprint("PDO Error: " . $e->getMessage() . "<br />");
			log_database("PDO Error: " . $e->getMessage());
		}

		if (!$gbl->$fdbvar) {
			die("Could not open the database connection.");
		}

		return true;
	}

	/**
	 * Reconnects to the database
	 * @return bool true if successful
	 */
	private function reconnect()
	{
		log_database("Reconnecting ...");
		return $this->connect();
	}

	/**
	 * Closes the database connection
	 * @return void
	 */
	private function close()
	{
		global $gbl;

		$fdbvar = "__fdb_" . $this->__readserver;
		$gbl->$fdbvar = null;
	}

	final function isLocalhost($var = "__readserver")
	{
		if (isset($this->$var) && $this->$var != 'localhost') {
			return false;
		}
		return true;
	}

	public function rawQuery($query, $params = null)
	{
		global $gbl;

		$fdbvar = "__fdb_{$this->__readserver}";

		$stat = $this->executeQuery($gbl->$fdbvar, $query, $params);

		if (!$stat || $stat == null) {
			return 0;
		}

		$fullresult = null;
		while ($result = $stat->fetch(PDO::FETCH_ASSOC)) {
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

			$fullresult[] = $result;
		}
		return $fullresult;
	}

	/**
	 * Sets new database password
	 * @param  $new_password
	 * @return int|null
	 */
	public function setPassword($new_password)
	{
		return $this->rawQuery("set password=Password(:password);", array(':password' => $new_password));
	}

	/**
	 * Executes given sql query
	 * @param PDO $res
	 * @param $query
	 * @param array $params the input paramaters [optional]
	 * @return PDOStatement|resource
	 */
	private function executeQuery($res, $query, $params = null)
	{
		$error_message = 'unknown';

		try {
			$stat = $res->prepare($query);
			if ($stat) {
				$result = $stat->execute($params);
			} else {
				$result = null;
				$pdo_error_info = $res->errorInfo();
				$error_message = $pdo_error_info[2];
			}

			if (!$result) {
				dprint("DbError: Query failed $query : $error_message<br />");
				log_database("Query failed: $query : $error_message");
				return null;
			}

			return $stat;

		} catch (PDOException $e) {
			dprint("PDO Error: " . $e->getMessage() ."<br />");
			log_database("PDO Error: " . $e->getMessage());
		}
	}

	public function getRowsGeneric($condition, $params = null, $columns = null)
	{
		if ($columns) {
			$select = implode(",", $columns);
		} else {
			$select = "*";
		}
		$query = "select $select from $this->__sqtable $condition;";
		$fulresult = $this->rawQuery($query, $params);

		return $fulresult;
	}

	function getClass()
	{
		return 'sqlite';
	}

	/**
	 * Checks whether the row exists in the table based on the given column name and value
	 * @param  $column
	 * @param  $value
	 * @return bool true if exists, otherwise false
	 */
	public function existInTable($column, $value)
	{
		$result = $this->getRowsWhere("$column = :value", array(':value' => $value));
		if ($result) {
			return true;
		}
		return false;
	}

	public function getRowsWhere($string, $params = null, $list = null)
	{
		return $this->getRowsGeneric("where $string", $params, $list);
	}

	public function getRowsOr($field1, $value1, $field2, $value2)
	{
		$params = array(':value1' => $value1, ':value2' => $value2);
		return $this->getRowsWhere("$field1 = :value or $field2 = :value2", $params);
	}

	public function getRowAnd($field1, $value1, $field2, $value2)
	{
  		$params = array(':value1' => $value1, ':value2' => $value2);
		return $this->getRowsWhere("$field1 = :value1 and $field2 = :value2", $params);
	}

	public function getRowsNot($field, $notval)
	{
		$params = array(':notval' => $notval);
		return $this->getRowsWhere("$field != :notval", $params);
	}

	public function getRows($field, $value)
	{
		$params = array(':value' => $value);
		return $this->getRowsWhere("$field = :value", $params);
	}

	public function getTable($list = null)
	{
		return $this->getRowsGeneric('', null, $list);
	}

	/**
	 * Gets column names
	 * @return named array or null if fails
	 */
	public function getColumnTypes()
	{
		global $gbl, $sgbl;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (!$this->__column_type) {
			$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.Columns where TABLE_NAME = :table_name";
			$params = array(':table_name' => $this->__sqtable);
			
			$result = $this->executeQuery($gbl->$fdbvar, $query, $params);

			if (!$result) {
				return null;
			}

			$res = null;

			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows as $row) {
				$res[$row['COLUMN_NAME']] = $row['COLUMN_NAME'];
			}

			$this->__column_type = $res;
		}

		return $this->__column_type;
	}

	public function createQueryStringAdd($array, &$params)
	{
		$params = array();
		$param_names = array();
		$column_names = $this->getColumnTypes();

		$string = " ( ";
		$string .= implode(', ', $column_names);
		$string .= " ) values( ";

		$index = 1;
		foreach ($column_names as $col) {

			$param_name = ":param_$index";
			$param = $array[$col];

			if ($col === 'realpass') {
				$param = base64_encode($param);
				$param = "__lxen:$param";
			}

			$param_names[] = $param_name;
			$params[$param_name] = $param;

			$index++;
		}

		$string .= implode(', ', $param_names);
		$string .= " ) ";

		return $string;
	}

	public function createQueryStringUpdate($array, &$params)
	{
		global $sgbl;

		$params = array();
		$string = '';
		$result = $this->getColumnTypes();
		$index = 1;
		foreach ($result as $col) {
			if (isset($sgbl->__var_collectquota_run) && $sgbl->__var_collectquota_run) {
				if (!csb($col, 'priv_') && !csb($col, 'used_') && !csb($col, 'nname') && !csb($col, 'status') && !csb($col, 'state') && !csb($col, 'cpstatus')) {
					continue;
				}
			}

			$param_name = ":param_$index";
			$param = $array[$col];

			if ($col === 'realpass' && !csb($param, "__lxen:")) {
				$param = base64_encode($param);
				$param = "__lxen:$param";
			}

			$string[] = "$col = $param_name";
			$params[$param_name] = $param;

			$index++;
		}

		$string = implode(', ', $string);

		return $string;
	}

	/**
	 * Returns the number of the rows
	 * @param  string $condition where condition
	 * @param  array|null $params
	 * @return int the number of the rows
	 */
	public function getCountWhere($condition, $params = null)
	{
		global $sgbl;

		$res = $this->rawQuery("select count(*) from $this->__sqtable where $condition", $params);
		if ($sgbl->__var_database_type === 'mysql') {
			return $res[0]['count(*)'];
		} else if ($sgbl->__var_database_type === 'mssql') {
			return $res[0]['computed'];
		} else {
			return $res[0]['count(*)'];
		}
	}

	private function getToArray($object)
	{
		$columns = $this->getColumnTypes();

		$ret = array();

		foreach($columns as $key) {
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
					$ret[$key] = ",$ret[$key],";
				} else {
					$ret[$key] = '';
				}
			} else if (csb($key, "ser_")) {
				$cvar = substr($key, 4);
				if (isset($object->$cvar)) {
					$value = $object->$cvar;
				} else {
					$value = null;
				}

				if ($value && isset($value->driverApp)) {
					unset($value->driverApp);
				}

				if ($value && cse($key, "_a")) {
					foreach($value as $kk => $vv) {
						unset($value[$kk]->__parent_o);
					}
				}

				if (isset($object->$cvar)) {
					$ret[$key] = base64_encode(serialize($object->$cvar));
				} else {
					$ret[$key] = base64_encode(serialize(null));
				}

			} else if (csb($key, "priv_q_") || csb($key, "used_q_")) {
				$qob = strtil($key, "_q_");
				$qkey = strfrom($key, "_q_");

				if (isset($object->$qob->$qkey)) {
					$ret[$key] = $object->$qob->$qkey;
				} else {
					$ret[$key] = null;
				}

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
			}
		}
		return $ret;
	}

	/**
	 * Updates the database row based on the given object
	 * @param  string $nname column name
	 * @param  string $value column value
	 * @param  $object object to be stored as a row
	 * @return bool true if successful, otherwise false
	 */
	public function setRowObject($nname, $value, $object)
	{
		$array = $this->getToArray($object);
		return $this->setRow($nname, $value, $array);
	}

	/**
	 * Stores the object to the database
	 * @param  mixed $object object to be stored as a row
	 * @return bool true if successful, otherwise false
	 */
	public function addRowObject($object)
	{
		$array = $this->getToArray($object);
		return $this->addRow($array);
	}

	/**
	 * Updates the row based on column name and value
	 * @param  string $nname column name
	 * @param  string $value column value
	 * @param  $array named array where the key is column name and value is the new column value
	 * @return bool true if successful, otherwise false
	 */
	public function setRow($nname, $value, $array)
	{
		global $gbl;

		$fdbvar = "__fdb_" . $this->__readserver;

		if (!$this->isLocalhost()) {
			print("Major Error\n");
			exit;
		}

		$string = $this->createQueryStringUpdate($array, $params);
		$params = array_merge($params, array(':value' => $value));
		$query = "update $this->__sqtable set $string where $nname = :value";

		$res = $this->executeQuery($gbl->$fdbvar, $query, $params);

		if (!$res) {
			dprint("DbError: Update Failed for $query<br />");
			log_database("Update Failed for $query");
			return true;
		}

		if ($this->__sqtable !== 'utmp') {
			dprint("Success: updated " .$this->__sqtable . " for " .  $array['nname'] . "\n", 1);
		}

		return true;
	}

	/**
	 * Adds new row to the table
	 * @param  array $array named array where the key is column name and value is the new column value
	 * @return bool true if successful, otherwise false
	 */
	public function addRow($array)
	{
		global $gbl, $sgbl;

		$fdbvar = "__fdb_" . $this->__readserver;
		if (!$this->isLocalhost()) {
			print("Major Error\n");
			exit;
		}

		$string = $this->createQueryStringAdd($array, $params);

		$query = "insert into $this->__sqtable $string ;";

		$res = $this->executeQuery($gbl->$fdbvar, $query, $params);

		if (!$res) {
			dprint("DbError: Insert Failed for $query<br />");
			log_database("Insert Failed for $query");
			return false;
		}

		dprint('Record inserted in the table \''.$this->__sqtable.'\' for '.$array['nname'].'<br />');
		return true;
	}

	/**
	 * Deletes the row from the table based on column name and value
	 * @param  string $nname column name
	 * @param  string $value column value
	 * @return bool true if successful, otherwise false
	 */
	public function delRow($nname, $value)
	{
		global $gbl;

		$fdbvar = "__fdb_" . $this->__readserver;
		$query = "delete from $this->__sqtable where $nname = :value";
		$params = array(':value' => $value);

		$res = $this->executeQuery($gbl->$fdbvar, $query, $params);

		if (!$res) {
			dprint("DbError: Insert Failed for $query<br />");
			log_database("Delete Failed for $query");
			return false;
		}

		dprint("Record deleted from $this->__sqtable for $nname <br />.");
		return true;
	}
}
