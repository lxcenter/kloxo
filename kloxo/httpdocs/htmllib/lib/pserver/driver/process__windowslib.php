<?php 

class Process__Windows extends lxDriverClass {


static function readProcessList()
{
	
	try {
		$obj = new COM("Winmgmts:{impersonationLevel=impersonate}!//./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	try {
		$list = $obj->execQuery("select * from Win32_Process");
	} catch (exception $e) {
	}


	$i = 0;
	$v = new Variant(42);
	foreach($list as $l) {
		try {
			$result[$i]['nname'] = $l->ProcessId;
			$result[$i]['command'] = $l->Caption;
			$ret = $l->getOwner($v);
			if ($ret) {
			} else {
				$result[$i]['username'] = "$v";
			}
			$result[$i]['state'] = "ZZ";
			$i++;
		} catch (exception $e) {
			$result[$i]['state'] = "ZZ";
			$result[$i]['nname'] = "Error";
			$result[$i]['command'] = $e->getMessage();
			$result[$i]['username'] = $e->getCode();
		}
	}

	return $result;
}

function dbactionUpdate($subaction)
{
	if ($this->main->signal === "KILL") {
		// forcibly Kill
	} else if ($this->main->signal === "TERM") {
		// Send Term
	}


}


}
