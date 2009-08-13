<?php 

class serverweb extends lxdb {

static $__desc = array("", "",  "webserver_config");
static $__desc_nname = array("", "",  "webserver_config");
static $__desc_php_type = array("", "",  "php_type");
static $__acdesc_update_edit = array("", "",  "config");
static $__acdesc_show = array("", "",  "webserver_config");


function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'serverweb');
	if ($driverapp === 'lighttpd') {
		$vlist['php_type'] = array('M', "Cgi-fastcgi");
		$vlist['__v_button'] = array();
		return $vlist;
	}
	$vlist['php_type'] = array('s', array('suphp', 'mod_php'));
	return $vlist;
}

static function initThisObjectRule($parent, $class, $name = null) 
{ 
	return $parent->getClName();
}


}
