<?php 

class Hostdeny__Linux extends lxDriverClass {


function dbactionAdd()
{
	global $gbl, $sgbl, $login; 



	if (if_demo()) {
		return;
	}
	$_filepath="__path_home_root/lxguard/hostdeny.info";
	$result =  $this->main->__var_hostlist;
	$result = merge_array_object_not_deleted($result, $this->main);

	$list = get_namelist_from_arraylist($result, 'hostname', 'hostname');
	dprintr($list);

	lfile_put_serialize($_filepath, $list);
	lxshell_return("__path_php_path", "../bin/common/lxguard.php");

}

function dbactionDelete()
{
	$this->dbactionAdd();
}

}
