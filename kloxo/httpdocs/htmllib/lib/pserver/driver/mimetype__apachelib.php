<?php 

class mimetype__apache extends Lxdriverclass {

function dbactionUpdate($subaction)
{
	createMimeType();
}

function dbactionAdd()
{
	createMimeType();
}
function dbactionDelete()
{
	createMimeType();
}

function createMimeType()
{
	
	$result = $this->main->__var_mime_list;
	$result = merge_array_object_not_deleted($result, $this->main);
	$string = null;
	foreach($result as $r) {
		$string .= "Addtype {$r['type']} {$r['extension']}\n";
	}

	lfile_put_contents("/etc/httpd/conf/kloxo/mimetype.conf", $string);
}

}
