<?php 

class dirlocation__linux extends lxDriverClass {

function dbactionUpdate($subaction)
{

	dprint("here\n");
	switch($subaction)
	{
		case "add_xen_location_a":
			$this->check_xen_dirlocation();
			break;

	}
	
}


static function getSizeForAll($list)
{
	foreach($list as $l) {
		if (csa($l, "lvm:")) {
			$ret[$l] = vg_diskfree($l);
		} else {
			$ret[$l] = lxfile_disk_free_space($l);
		}
	}
	dprintr($ret);
	return $ret;
}


function check_xen_dirlocation()
{
	$diro = getFirstFromList($this->main->__t_new_xen_location_a_list);
	$dirlocation = $diro->nname;

	if (!csb($dirlocation, "lvm:")) {
		return;
	}

	$dirlocation = fix_vgname($dirlocation);

	$ret = exec_with_all_closed_output("vgdisplay -c $dirlocation");

	if (!csa($ret, ":")) {
		throw new lxException ("the_lvm_doesnt_exist", 'nname', $dirlocation);
	}
}


}
