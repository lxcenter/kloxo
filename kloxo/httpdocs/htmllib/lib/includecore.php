<?php 
function print_time($var, $mess = null, $dbg = 2) 
{
	static $last;

	$now = microtime(true);
	if (!isset($last[$var])) {
		$last[$var] = $now;
		return;
	}
	$diff = round($now - $last[$var], 7);
	$now = round($now, 7);
	$last[$var] = $now;
	if (!$mess) {
		return;
	}
	$diff = round($diff, 2);

	if ($dbg <= -1) {
	} else {
		dprint("$mess: $diff <br> \n", $dbg);
	}

	return "$mess: $diff seconds";
}

print_time('full');

function windowsOs() 
{
	if (getOs() == "Windows") {
		return true;
	}
	return false;
}

function getOs()
{
	return (substr(php_uname(), 0, 7) == "Windows")? "Windows": "Linux";
}

if(!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (isset($_SERVER['SCRIPT_NAME'])) {
		$n = $_SERVER['SCRIPT_NAME'];
		$f = ereg_replace('\\\\', '/',$_SERVER['SCRIPT_FILENAME']);
		$f = str_replace('//','/',$f);
		$_SERVER['DOCUMENT_ROOT'] = eregi_replace($n, "", $f);
	}
}

if (!$_SERVER['DOCUMENT_ROOT']) {
	$_SERVER['DOCUMENT_ROOT'] = $dir;
}

if (WindowsOs()) {
	//ini_set("include_path", ".;{$_SERVER['DOCUMENT_ROOT']}");
} else {
	ini_set("include_path", "{$_SERVER['DOCUMENT_ROOT']}");
}

function getreal($vpath)
{
     return  $_SERVER["DOCUMENT_ROOT"] . "/". $vpath; 
}

function readvirtual($vpath)
{
     readfile($_SERVER["DOCUMENT_ROOT"] . $vpath);
}
