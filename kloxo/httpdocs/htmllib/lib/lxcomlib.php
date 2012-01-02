<?php 

class Lxcom {

public $__notreal;
public $__com;
public $__name;
public $__varlist;

function __construct($name, $throwflag = false)
{
	$this->__notreal = false;
	dprint("Lxcom construct with throw $name\n");
	$this->__name = $name;
	try {
		$this->__com = new COM($name);
	} catch (exception $e) {
		if ($throwflag) {
			throw $e;
		}
		$this->__notreal = true;
	}
		
}

function lxcom_getSection($sec)
{
	$osec = new Variant(NULL);
	$this->__com->getSection($sec, $osec);
	return create_lxcom($osec);
}

function object_set($obj, $var, $val)
{
	$app = $this->__com->$obj;
	$app->$var = $val;
	$this->__com->$obj = $app;
	$this->__varlist["$obj.$var"] = $val;
}

function __set($var, $val)
{
	if ($this->__notreal) {
		return;
	}
	$this->__varlist[$var] = $val;
	$this->__com->$var = $val;
}

function __get($var)
{
	if ($this->__notreal) {
		return null;
	}
	return $this->__com->$var;
}

function __call($m, $arg)
{

	$strarg = var_export($arg, true);
	$strarg = str_replace("\n", " ", $strarg);
	$existing = var_export($this->__varlist, true);
	$existing = str_replace("\n", " ", $existing);
	$call =  "$m $strarg on $this->__name (existing $existing)";
	if ($this->__notreal) {
		log_log("com_error", "unreal $call");
		return;
	}

	$comerr = false;
	$retcom = false;
	if (csb($m, "com_")) {
		$m = strfrom($m, "com_");
		$retcom = true;
	}
	try {
		//$ret = call_user_func_array(array($this->__com, $m), $arg);
		$string = null;
		for($i = 0; $i < count($arg); $i++) {
			$string[] = "\$arg[$i]";
		}
		if ($string) {
			$string = implode(", ", $string);
		}
		$func = "return \$this->__com->$m($string);";
		dprint("$func \n");
		$ret = eval($func);
	} catch (exception $e) {
		log_log("com_error", "Exception: {$e->getMessage()}: $call");
		$ret = null;
		$call = "Exception: $call";
		$comerr = true;
	}
	if (!$comerr) {
		$call = "Success..: $call";
	}
	log_log("com_call", $call);
	if ($retcom) {
		return create_lxcom($ret);
	}

	return $ret;
}

}


function create_lxcom($ob)
{
	$lxc = new Lxcom('newcom');
	$lxc->__com = $ob;
	//if (is_object($ob) && get_class($ob) === 'com') {
	if (is_object($ob)) {
		$lxc->__notreal = false;
	} else {
		$lxc->__notreal = true;
	}
	return $lxc;
}
