<?php 

function windowsOs() 
{
	if (getOs() == "windows") {
		return true;
	}
	return false;
}

function getOs()
{
	// A bug... Php crashes whern php_uname is called. Man!!!!!!
	return false;
	return (substr(php_uname(), 0, 7) == "Windows")? "windows": "linux";
}

if(!isset($_SERVER['DOCUMENT_ROOT']))
{
	$n = $_SERVER['SCRIPT_NAME'];
	$f = ereg_replace('\\\\', '/',$_SERVER['SCRIPT_FILENAME']);
	$f = str_replace('//','/',$f);
	$_SERVER['DOCUMENT_ROOT'] = eregi_replace($n, "", $f);
}

if (!$_SERVER['DOCUMENT_ROOT']) {
	$_SERVER['DOCUMENT_ROOT'] = getcwd();
}

if (WindowsOs()) {
	ini_set("include_path", ".;{$_SERVER['DOCUMENT_ROOT']}");
} else {
	ini_set("include_path", ".:{$_SERVER['DOCUMENT_ROOT']}");
}


//include_once "htmllib/phplib/common.inc";

//This is needed for the stupid zend debugger to work. the aim is to automatically reach the point below the common.inc; common.inc is where all the different classes are loaded, and without the class first getting loaded, the breakpoint set inside a class will not work. So I wrote a Vim function that will automatically send the set of functions that will take you to this point.

function dummy_debug_function()
{
	$var = 9;
}
dummy_debug_function();

function getreal($vpath)
{
     return  $_SERVER["DOCUMENT_ROOT"] . "/". $vpath; 
}

function readvirtual($vpath)
{
     readfile($_SERVER["DOCUMENT_ROOT"] . $vpath);
}

