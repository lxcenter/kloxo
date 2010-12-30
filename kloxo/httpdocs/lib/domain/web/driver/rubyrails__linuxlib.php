<?php 

class rubyrails__linux extends lxDriverClass {


function dbActionAdd()
{

	$apppath = "/home/{$this->main->customer_name}/ror/{$this->main->getParentName()}/{$this->main->appname}/";
	$bpath = dirname($apppath);
	lxshell_directory($bpath, "rails", $this->main->appname);
	lxfile_unix_chown_rec($apppath, $this->main->__var_username);

}

function dbActiondelete()
{

	$apppath = "/home/{$this->main->customer_name}/ror/{$this->main->getParentName()}/{$this->main->appname}/";
	lxfile_rm_rec($apppath);

}
}


