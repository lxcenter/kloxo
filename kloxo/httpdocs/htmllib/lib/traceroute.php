<?php 

class traceroute extends lxclass {

static $__desc = array("n", "",  "traceroute");
static $__desc_nname	 = array("n", "",  "");
static $__desc_ip	 = array("n", "",  "IP");
static $__desc_responsetimes	 = array("n", "",  "response_time");
static $__acdesc_list = array("n", "",  "traceroute");

function write() {}
function get() {}
static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}
static function createListNlist($parent, $view)
{
	$nlist['ip'] = '10%';
	$nlist['responsetimes'] = '100%';
	return $nlist;
}

function isSelect() { return false; }

static function perPage() { return 5000; }
static function initThisListRule($parent, $class) { return null; }
static function initThisList($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$_sysname = "linux";

	$host = $_SERVER['REMOTE_ADDR'];

	if (!$host || $sgbl->isDebug()) {
		$host = "google.com";
	}

	if($parent->isClass('client')) {
		$server = $parent->websyncserver;
	} else {
		$server = $parent->syncserver;
	}

	$cmd = "traceroute -q 1 -n $host ";

	$_result = rl_exec_get(null, "localhost", array("traceroute", "exec_traceroute"), array($cmd));

	if (!is_array($_result)) {
		throw new lxexception("traceroute_failed", '', "");
	}

	if (count($_result) == 0) {
		throw new lxexception("traceroute_failed", '', "");
	}
	$object = new Traceroute(null, null, '__name__');
	return $object->Net_Traceroute_Result($_result, $_sysname);
}

static function exec_traceroute($cmd)
{
	exec($cmd, $result);
	return $result;
}

function Net_Traceroute_Result($result, $sysname)
{
	$this->_raw_data = $result;
	$this->_sysname  = $sysname;

	$this->_parseResultLinux();
	return $this->_hops;
}

function _initArgRelation()
{
	$this->_argRelation = array(
		"linux" => array (
			"numeric"   => "-n",
			"ttl"       => "-m",
			"deadline"  => "-w"
		),
		"windows" => array (
			"numeric"   => "-d",
			"ttl"       => "-h",
			"deadline"  => "-w"
		)
	);
}

function _parseResultlinux()
{
	$raw_data_len = count($this->_raw_data);
	$dataRow = 0;

	while (empty($this->_raw_data[$dataRow]) && ($dataRow<$raw_data_len)) {
		$dataRow++;
	}

	$tempparts        = explode(' ', $this->_raw_data[$dataRow]);
	$this->_target_ip = trim($tempparts[3], ' (),');
	$this->_ttl       = (int) $tempparts[4];
	$dataRow++;

	while (empty($this->_raw_data[$dataRow]) && ($dataRow<$raw_data_len)) {
		$dataRow++;
	}

	$hops = array();
	while (($dataRow < $raw_data_len) && !empty($this->_raw_data[$dataRow])) {
		$hop = array();
		$parts = explode('  ', substr($this->_raw_data[$dataRow], 4));

		/* if we can find a next hop it's name/ip will be here */
		if (count($parts) > 0) {
			/* get machine/ip */
			$machineparts = explode(' ', $parts[0]);
			if (count($machineparts) > 1) {
				$hop['machine'] = $machineparts[0];
				$hop['ip']      = trim($machineparts[1], ' ()');
			} else {
				$hop['ip'] = $machineparts[0];
			}
			array_shift($parts);
		}

		$responsetimes = array();
		for($timeidx = 0; $timeidx < count($parts); $timeidx++) {
			$temppart=explode(' ', $parts[$timeidx]);
			if ($temppart[0] == "*") {
				$responsetimes[] = -1; // unreachable
			} else {
				$responsetimes[] = (float) $temppart[0];
			}
		}
		$hop['responsetimes'] = $responsetimes[0];
		$dataRow++;
		$hop['nname'] = $dataRow;
		$hops[$dataRow] = $hop;
	}
	$this->_hops = $hops;
}

/**
* Parses the output of Windows' traceroute command
*
* @see    _parseResult()
* @access private
*/
function _parseResultwindows()
{
	$raw_data_len = count($this->_raw_data);
	$dataRow = 0;

	while (empty($this->_raw_data[$dataRow]) && ($dataRow<$raw_data_len)) {
		$dataRow++;
	}

	$tempparts = explode(' ', $this->_raw_data[$dataRow]);
	$searchIdx = 0;
	while (($searchIdx < count($tempparts)) && (substr($tempparts[$searchIdx], 0, 1) != '[')) {
		$searchIdx++;
	}
	$this->_target_ip = trim($tempparts[$searchIdx], ' [],');
	while (($searchIdx < count($tempparts)) && ((int) $tempparts[$searchIdx] <= 0)) {
		$searchIdx++;
	}
	if ((int) $tempparts[$searchIdx] > 0) {
		$this->_ttl = (int) $tempparts[$searchIdx]; // TTL might be written in next line; e.g. on Windows 98
	} elseif (!empty($this->_raw_data[$dataRow+1])) {
		$dataRow++;
		$tempparts  = explode(' ', $this->_raw_data[$dataRow]);
		$searchIdx = 0;
		while (($searchIdx < count($tempparts)) && ((int) $tempparts[$searchIdx] <= 0)) {
			$searchIdx++;
		}
		if ((int) $tempparts[$searchIdx] > 0) {
			$this->_ttl       = (int) $tempparts[$searchIdx]; // TTL might be written in next line; e.g. on Windows 98
		}
	}

	while (!empty($this->_raw_data[$dataRow]) && ($dataRow<$raw_data_len)) {
		$dataRow++;
	}
	while (empty($this->_raw_data[$dataRow]) && ($dataRow<$raw_data_len)) {
		$dataRow++;
	}

	$hops=array();
	/* loop from second elment to the fifths last */
	while (($dataRow < $raw_data_len) && !empty($this->_raw_data[$dataRow])) {
		$hop=array();

		$responsetimes = array();
		for($timeidx = 0; $timeidx < 3; $timeidx++) {
			$temppart=trim(str_replace(' ms','',substr($this->_raw_data[$dataRow], 3+($timeidx*9), 9)));
			if ($temppart == '*') {
				$responsetimes[] = -1; // unreachable
			} else {
				$responsetimes[] = (float) str_replace('<', '', $temppart);
			}
		}
		$hop['responsetimes'] = $responsetimes;

		$machineparts = explode(' ', rtrim(substr($this->_raw_data[$dataRow], 32)));
		// if we can find a next hop it's name/ip will be here
		if (count($machineparts) == 1) {
			$hop['ip'] = trim($machineparts[0], ' ()[]');
		} elseif (count($machineparts) == 2) {
			$hop['machine'] = $machineparts[0];
			$hop['ip']      = trim($machineparts[1], ' ()[]');
		}
		// otherwise we've got an errormessage or something here ... like "time limit exceeded"

		$hops[$host['ip']] = $hop;
		$dataRow++;
	}
	$this->_hops = $hops;
}

/**
* Returns a Traceroute_Result property
*
* @param  string $name    property name
* @return mixed           property value
* @access public
*/
function getValue($name)
{
	return isset($this->$name) ? $this->$name : '';
}

/**
* Returns the target IP from parsed result
*
* @return string          IP address
* @see    _target_ip
* @access public
*/
function getTargetIp()
{
	return $this->_target_ip;
}

/**
* Returns hops from parsed result
*
* @return array           Hops
* @see    _hops
* @access public
*/
function getHops()
{
	return $this->_hops;
}

/**
* Returns TTL from parsed result
*
* @return int             TTL
* @see    _ttl
* @access public
*/
function getTTL()
{
	return $this->_ttl;
}

/**
* Returns raw data that was returned by traceroute
*
* @return array           raw data
* @see    _raw_data
* @access public
*/
function getRawData()
{
	return $this->_raw_data;
}

/**
* Returns sysname that was "guessed" (OS on which class is running)
*
* @return string          OS_Guess::sysname
* @see    _sysname
* @access public
*/
function getSystemName()
{
	return $this->_sysname;
}

}

