<?php 

class Sgbllib {

function __construct()
{

	$this->arg_getting_string = '
	$arglist = array();
	for ($i = $start; $i < func_num_args(); $i++) {
		if (isset($transforming_func)) {
			$arglist[] = $transforming_func(func_get_arg($i));
		} else {
			$arglist[] = func_get_arg($i);
	}
}
		';


	if (windowsOs()) {
	 	$this->__path_tmp = "c:/tmp";
	 	$this->__path_slash = "c:/tmp";
		$this->__path_user_root = "c:/usr";
		$this->__path_var_root = "c:/var";
		$this->__path_log = "d:/var/log";
		$this->__path_root_base = "my_computer";
	} else {
	 	$this->__path_slash = "/";
	 	$this->__path_tmp = "/tmp";
		$this->__path_user_root = "/usr";
		$this->__path_var_root = "/var";
		$this->__path_real_etc_root = "/etc";
		$this->__path_log = "/var/log";
		$this->__path_root_base = "/";
	}

	$this->__var_lxlabs_marker = "__lxlabs_marker";

	$this->__var_lpanelwidth = "220";

	$this->__var_language['tr'] = 'Turkish';
	$this->__var_language['en'] = 'English';
	$this->__var_language['cen'] = 'Custom English';
	$this->__var_language['cn'] = 'Chinese';
	$this->__var_language['es'] = 'Spanish';
	$this->__var_language['de'] = 'German';
	$this->__var_language['it'] = 'Italian';
	$this->__var_language['fr'] = 'French';
	$this->__var_language['cz'] = 'Czech';
	$this->__var_language['nl'] = 'Dutch';
	$this->__var_language['pt'] = 'Portuguese';
	$this->__var_language['pl'] = 'Polish';
	$this->__var_language['lt'] = 'Lithuanian';
	$this->__var_language['bg'] = 'Bulgarian';
	$this->__var_language['jp'] = 'Japanese';
	$this->__var_language['kr'] = 'Korean';
	$this->__var_language['ru'] = 'Russian';
	$this->__var_language['se'] = 'Swedish';
	$this->__var_language['ro'] = 'Romanian';
	$this->__var_language['br'] = 'Brazilian Portuguese';
}


function isLxlabsClient()
{
	return ($this->__var_program_name === 'lxlabsclient');
}

function isBlackBackground()
{
	return false;
	return $this->isDebug();
}
function isKloxo()
{
	return ($this->__var_program_name === 'kloxo');
}

function isKloxoForRestore()
{
	return $this->isKloxo();
}

function isLive() { return false ; }

function isHyperVm()
{
	return ($this->__var_program_name === 'hypervm');
}

function is_this_master()
{
	return !$this->is_this_slave();
}

function is_this_slave()
{
	return lxfile_exists("__path_slave_db");

}

}


