<?php 

class serverweb extends lxdb {

static $__desc = array("", "", "webserver_config");
static $__desc_nname = array("", "", "webserver_config");
static $__desc_php_type = array("", "", "php_type");
static $__acdesc_update_edit = array("", "", "config");
static $__acdesc_show = array("", "", "webserver_config");

static $__desc_apache_optimize = array("", "", "apache_optimize");
static $__desc_mysql_convert = array("", "", "mysql_convert");
static $__desc_fix_chownchmod = array("", "", "fix_chownchmod");

function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{

	// issue #571 - add httpd-worker and httpd-event for suphp
	// issue #566 - Mod_ruid2 on Kloxo
	// issue #567 - httpd-itk for kloxo

	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'serverweb');
	if ($driverapp === 'lighttpd') {
		$vlist['php_type'] = array('M', "Cgi-fastcgi");
		$vlist['__v_button'] = array();
//		return $vlist;
	}
	else if ($driverapp === 'apache') {
//		$vlist['php_type'] = array('s', array('suphp', 'suphp_worker', 'suphp_event', 'suexec', 'suexec_worker', 'suexec_event', 'mod_php', 'mod_php_ruid2', 'mod_php_itk'));
		$vlist['php_type'] = array('s', array('suphp', 'suphp_worker', 'suphp_event', 'mod_php', 'mod_php_ruid2', 'mod_php_itk'));
		$this->setDefaultValue('php_type', 'mod_php');

		$vlist['apache_optimize'] = array('s', array('--- none ---', 'optimize'));
		$this->setDefaultValue('apache_optimize', '--- none ---');
		$vlist['mysql_convert'] = array('s', array('--- none ---', 'to-myisam', 'to-innodb'));
		$this->setDefaultValue('mysql_convert', '--- none ---');
		$vlist['fix_chownchmod'] = array('s', array('--- none ---', 'fix-ownership', 'fix-permissions', 'fix-ALL'));
		$this->setDefaultValue('fix_chownchmod', '--- none ---');
	
		$vlist['__m_message_pre'] = 'webserver_config';
	}
	return $vlist;
}

static function initThisObjectRule($parent, $class, $name = null) 
{ 
	return $parent->getClName();
}


}
