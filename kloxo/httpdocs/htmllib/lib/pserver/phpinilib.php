<?php 

class phpini_flag_b extends lxaclass {
static $__desc_display_error_flag = array("f", "",  "display_errors");
static $__desc_register_global_flag = array("f", "",  "register_globals");
static $__desc_enable_zend_flag = array("f", "",  "enable_zend");
static $__desc_enable_xcache_flag = array("f", "",  "enable_xcache");
static $__desc_enable_ioncube_flag = array("f", "",  "enable_ioncube");
static $__desc_upload_max_filesize = array("", "",  "upload_file_max_size");
static $__desc_log_errors_flag = array("f", "",  "log_errors");
static $__desc_file_uploads_flag = array("f", "",  "file_uploads");
static $__desc_upload_tmp_dir_flag = array("", "",  "upload_tmp_dir");
static $__desc_output_buffering_flag = array("f", "",  "output_buffering");
static $__desc_register_argc_argv_flag = array("f", "" , "register_argc_argv");
static $__desc_magic_quotes_gpc_flag = array("f", "" , "magic_quotes_gpc");
static $__desc_register_long_arrays_flag = array("f", "" , "register_long_arrays");
static $__desc_variables_order_flag = array("", "" , "variables_order");
static $__desc_output_compression_flag = array("f", "" , "output_compression");
static $__desc_post_max_size_flag = array("", "" , "post_max_size");
static $__desc_magic_quotes_runtime_flag = array("f", "" , "magic_quotes_runtime");
static $__desc_magic_quotes_sybase_flag = array("f", "" , "magic_quotes_sybase");
static $__desc_gpc_order_flag = array("", "" , "gpc_order");
static $__desc_extension_dir_flag = array("", "" , "extension_dir");
static $__desc_enable_dl_flag = array("f", "" , "enable_dl");
static $__desc_sendmail_from = array("", "" , "sendmail_from");
static $__desc_cgi_force_redirect_flag = array("f", "" , "cgi_force_redirect");
static $__desc_mysql_allow_persistent_flag = array("f", "" , "mysql_allow_persistent_flag");
static $__desc_disable_functions_flag = array("", "" , "disable_functions" , "Mail Account");
static $__desc_max_execution_time_flag = array("", "" , "max_execution_time");
static $__desc_max_input_time_flag = array("", "" , "max_input_time");
static $__desc_memory_limit_flag = array("", "" , "memory_limit");
static $__desc_allow_url_fopen_flag = array("f", "" , "allow_url_fopen");
static $__desc_allow_url_include_flag = array("f", "" , "allow_url_include");
static $__desc_session_save_path_flag = array("", "" , "session_save_path");
static $__desc_session_autostart_flag = array("f", "" , "session_autostart");
static $__desc_safe_mode_flag = array("f", "" , "safe_mode");

}
class phpini extends lxdb {

static $__desc = array("", "",  "php_configuration");
static $__desc_nname = array("", "",  "php_configuration");
static $__desc_enable_zend_flag = array("f", "",  "enable_zend");
static $__desc_enable_ioncube_flag = array("f", "",  "enable_ioncube");
static $__desc_register_global_flag = array("f", "",  "register_globals");
static $__desc_display_error_flag = array("f", "",  "display_errors");
static $__desc_php_manage_flag = array("", "",  "manage_php_configuration");
static $__acdesc_update_edit = array("", "",  "PHP_config");
static $__acdesc_update_extraedit = array("", "",  "advanced_PHP_config");
static $__acdesc_show = array("", "",  "PHP_config");

static function initThisObjectRule($parent, $class, $name = null) 
{
	return $parent->getClName();
}

function getInheritedList()
{
	$list[] = 'enable_xcache_flag';
	$list[] = 'enable_zend_flag';
	$list[] = "enable_ioncube_flag";
	$list[] = 'safe_mode_flag';
	$list[] = 'output_compression_flag';
	$list[] = 'session_save_path_flag';

	return $list;
}

function getLocalList()
{
	$list[] = 'display_error_flag';
	$list[] = 'register_global_flag';
	$list[] = 'log_errors_flag' ;
	$list[] = 'output_compression_flag';
	$list[] = 'enable_xcache_flag';
	$list[] = 'enable_zend_flag';
	$list[] = "enable_ioncube_flag";

	return $list;
}

function getExtraList()
{
	$list[] = 'sendmail_from';
	$list[] = 'enable_dl_flag' ;
	$list[] = 'output_buffering_flag' ;
	$list[] = 'register_long_arrays_flag' ;
	$list[] = 'allow_url_fopen_flag'; 
	$list[] = 'allow_url_include_flag'; 
	$list[] = 'register_argc_argv_flag' ;
	$list[] = 'magic_quotes_gpc_flag' ;
	$list[] = 'mysql_allow_persistent_flag' ;
	//$list[] = 'disable_functions_flag'; 
	$list[] = 'max_execution_time_flag'; 
	$list[] = 'max_input_time_flag'; 
	$list[] = 'memory_limit_flag'; 
	$list[] = 'post_max_size_flag'; 
	$list[] = "upload_max_filesize";
	$list[] = 'file_uploads_flag' ;
	$list[] = 'magic_quotes_runtime_flag' ;
	$list[] = 'magic_quotes_sybase_flag' ;
	$list[] = 'cgi_force_redirect_flag' ;
	$list[] = 'safe_mode_flag' ;
	//$list[] = 'session_autostart_flag' ;
	$list[] = 'session_save_path_flag' ;

	return $list;
}



function fixphpIniFlag()
{
	if (!isset($this->phpini_flag_b) || get_class($this->phpini_flag_b) !== 'phpini_flag_b') {
		$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
		$this->setUpINitialValues();
	}
}

function createExtraVariables()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->fixphpIniFlag();
	$gen = $login->getObject('general')->generalmisc_b;

	if (!$this->getParentO()->is__table('pserver')) {
		$ob = new phpini(null, 'localhost', createParentName('pserver', 'localhost'));
		$ob->get();
		$ob->fixphpIniFlag();

		$this->__var_docrootpath = $this->getParentO()->getFullDocRoot();
		$list = $this->getInheritedList();
		foreach($list as $l) {
			$this->phpini_flag_b->$l = $ob->phpini_flag_b->$l;
		}
		$this->__var_web_user = $this->getParentO()->username;
		$this->__var_customer_name = $this->getParentO()->customer_name;
		$this->__var_disable_openbasedir = $this->getParentO()->webmisc_b->disable_openbasedir;
	}



	$this->__var_extrabasedir = $gen->extrabasedir;
	$driverapp = $gbl->getSyncClass(null, $this->syncserver, 'web');
	$this->__var_webdriver = $driverapp;
}




function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=updateform&sa=extraedit';

}


function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function postUpdate()
{

	$this->setUpINitialValues();
	// We need to write because the fixphpini reads everything from the database.
	$this->write();
	if ($this->getParentO()->is__table('pserver')) {
		lxshell_return("__path_php_path", "../bin/fix/fixphpini.php", "--server={$this->getParentO()->nname}");
	}
}

function initPhpIni()
{
	if (!isset($this->phpini_flag_b) || get_class($this->phpini_flag_b) !== 'phpini_flag_b') {
		$this->phpini_flag_b = new phpini_flag_b(null, null, $this->nname);
	}

	$this->setUpINitialValues();
}

function updateform($subaction, $param)
{
	$this->initPhpIni();

	if ($subaction === 'extraedit') {
		$totallist = $this->getExtraList();
	} else {
		$totallist = $this->getLocalList();
	}

	$inheritedlist = $this->getInheritedList();
	foreach($totallist as $l) {
		if (!$this->getParentO()->is__table('pserver') && array_search_bool($l, $inheritedlist)) {
			$vlist["phpini_flag_b-$l"] = array('M', null);
		} else {
			$vlist["phpini_flag_b-$l"] = null;
		}
	}


	return $vlist;
}


function setUpINitialValues()
{
	$this->initialValue('enable_xcache_flag','off');
	$this->initialValue('output_compression_flag','off');
	$this->initialValue('enable_zend_flag','on');
	$this->initialValue('enable_ioncube_flag', 'on');
	$this->initialValue('upload_max_filesize', '2M');
	$this->initialValue('register_global_flag', 'off');
	$this->initialValue('mysql_allow_persistent_flag', 'off');
	$this->initialValue('session_save_path_flag', '/var/lib/php/session');
	$this->initialValue('disable_functions_flag', '');
	$this->initialValue('max_execution_time_flag', '30');
	$this->initialValue('max_input_time_flag', '60');
	$this->initialValue('memory_limit_flag', '32M');
	$this->initialValue('allow_url_fopen_flag', 'on');
	$this->initialValue('allow_url_include_flag', 'on');
	$this->initialValue('display_error_flag', 'off');
	$this->initialValue('log_errors_flag', 'off');
	$this->initialValue('session_autostart_flag', 'off');
	$this->initialValue('file_uploads_flag', 'on');
	$this->initialValue('output_buffering_flag', 'off');
	$this->initialValue('register_argc_argv_flag', 'on');
	$this->initialValue('register_long_arrays_flag', 'on');
	$this->initialValue('magic_quotes_gpc_flag', 'off');
	$this->initialValue('gpc_order_flag', 'GPC');
	$this->initialValue('variables_order_flag', 'EGPCS');
	$this->initialValue('post_max_size_flag', '8M');
	$this->initialValue('magic_quotes_runtime_flag', 'off');
	$this->initialValue('magic_quotes_sybase_flag', 'off');
	$this->initialValue('enable_dl_flag', 'on');
	$this->initialValue('cgi_force_redirect_flag', 'on');
	$this->initialValue('extension_dir_flag', '/usr/lib/php/modules');
	$this->initialValue('upload_tmp_dir_flag', '/tmp');
	$this->initialValue('safe_mode_flag', 'off');

}

function initialValue($var, $val)
{
	if (!isset($this->phpini_flag_b->$var) || !$this->phpini_flag_b->$var) {
		$this->phpini_flag_b->$var = $val;
	}

}


}
