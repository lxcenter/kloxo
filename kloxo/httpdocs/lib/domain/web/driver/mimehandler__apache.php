<?php 

class mimehandler___apache extends Lxdriverclass {

function dbactionAdd()
{
	$this->mupdate();
}

function dbactionDelete()
{
	$this->mupdate();
}

function mupdate()
{
	if ($this->main->is__table('webmimetype')) {
		$keystring = "Addtype";
	} else {
		$keystring = "AddHandler";
	}


	$string = null;
	foreach($this->main->__var_mimehandler as $k => $v) {
		$string .= "$keystring $k $v\n";
	}

	$stlist[] = "###Start Kloxo $keystring config Area";
	$stlist[] = "###Start Lxadmin $keystring config Area";
	$endlist[] = "###End Kloxo $keystring config Area";
	$endlist[] = "###End Lxadmin $keystring config Area";

	$startstring = $stlist[0];
	$endstring = $endlist[0];

	file_put_between_comments($this->main->__var_username, $stlist, $endlist, $startstring, $endstring, $this->main->__var_htp, $string);
}

}

class webhandler__apache extends  mimehandler___apache {

}

class webmimetype__apache extends mimehandler___apache {
}
