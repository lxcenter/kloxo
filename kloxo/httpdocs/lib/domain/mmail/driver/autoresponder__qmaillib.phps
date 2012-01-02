<?php  

class Autoresponder__Qmail extends lxDriverClass {


function createAutoResFile()
{
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];
	$sys_path = "$mailpath/$quser[0]";
	$sys_fpath = "$mailpath/$quser[0]"."/autorespond"."/message";

	lfile_write_content($sys_fpath, $this->main->text_message, mmail__qmail::getUserGroup($domain));
}


function dbActionAdd()
{
	//$this->createAutoResFile();
}

function dbActionDelete()
{
}

function dbactionUpdate($subaction)
{
	//$this->createAutoResFile();
}

}
