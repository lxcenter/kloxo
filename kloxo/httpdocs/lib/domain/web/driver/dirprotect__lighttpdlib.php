<?php 
class dirprotect__lighttpd extends lxDriverClass {


function dbactionAdd()
{
	$this->createDiruserfile();
}


function createDiruserfile()
{
	global $gbl, $sgbl, $login, $ghtml; 
	
	$dir = "__path_httpd_root/{$this->main->getParentName()}/__dirprotect/";
	$dirfile = $dir . "/" . $this->main->getFileName();
	if (!lxfile_exists($dir)) {
		lxuser_mkdir($this->main->__var_username, $dir);
	}
	$fstr = null;
	foreach($this->main->diruser_a as $v) {
		$fstr .= $v->nname . ':' . crypt($v->param) . "\n";
	}
	lxuser_put_contents($this->main->__var_username, $dirfile,  $fstr);
	lxuser_chmod($this->main->__var_username, $dirfile, "0755");
}


function dbactionUpdate($subaction)
{
	$this->createDiruserfile();
}

}


