<?php 

class davuser__apache extends Lxdriverclass {

function dbactionAdd()
{
	$this->createDiruserfile();
}

function createDiruserfile()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$result = $this->main->__var_davuser;
	$result = merge_array_object_not_deleted($result, $this->main);
	foreach($result as $r) {
		$nr[$r['username']] = $r['realpass'];
	}
	createHtpasswordFile($this, "__webdav", $nr);

}

function dbactionUpdate($subaction)
{
	$this->createDiruserfile();
}

}
