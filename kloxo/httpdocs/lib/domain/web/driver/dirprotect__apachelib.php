<?php 

class dirprotect__apache extends lxDriverClass {


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
		//lxfile_unix_chown($dir, $this->main->__var_username);
	}
	$fstr = null;
	foreach($this->main->diruser_a as $v) {
		$fstr .= $v->nname . ':' . crypt($v->param) . "\n";
	}
	lxuser_put_contents($this->main->__var_username, $dirfile,  $fstr);
	lxuser_chmod($this->main->__var_username, $dirfile, "0755");

	// http://project.lxcenter.org/issues/74
	lfile_put_contents($dirfile,$fstr);
}


function dbactionUpdate($subaction)
{
	$this->createDiruserfile();
}

}


