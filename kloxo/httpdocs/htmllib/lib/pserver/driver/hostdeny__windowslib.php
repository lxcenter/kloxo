<?php 

class Hostdeny__Windows extends lxDriverClass {


function dosyncToSystem()
{
	global $gbl, $sgbl, $login; 



	if (if_demo()) {
		return;
	}
	$_filepath="__path_real_etc_root/hosts.deny";
	$string = null;
	foreach((array) $this->main->__var_hostlist as $v) {
		$string .= "ALL: {$v['hostname']}\n";
	}

	if ($this->isDeleted() != "delete") {
		$string .= "ALL: {$this->main->hostname}\n";
	}

	lfile_put_contents($_filepath, $string);

}

}
