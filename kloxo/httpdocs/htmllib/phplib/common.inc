<?php


define('URL_SHOW', 'a=show');
define('URL_TOGGLE_STATUS', 'a=update&sa=toggle_status');
define('URL_TOGGLE_STATE', 'a=update&sa=toggle_state');
define('URL_UPDATE', 'a=updateform&sa=update');


//$local = $gl_class_array;

/*
foreach($local as $v) {
	include_once $v;
}
*/


//include_once "module/define.inc";

function __autoload($class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $gl_class_array;

	//print("Searching for class: $class  <br> \n");
	$class = strtolower($class);

	/*
	if ($class === 'domain') {
		debugBacktrace();
	}
*/

	if (csb($class, "all_")) { $class = strfrom($class, "all_"); }
	if (file_exists(getreal($gl_class_array[$class]))) {
		include_once $gl_class_array[$class];
		return;
	}
	dprint("Class Not found $class <br> \n");
}


