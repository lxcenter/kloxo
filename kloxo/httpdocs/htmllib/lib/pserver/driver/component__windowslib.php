<?php 


class component__windows extends lxDriverClass {


static function getListVersion($syncserver, $list)
{

	$list[]['componentname'] = 'mysql';
	$list[]['componentname'] = 'perl';
	$list[]['componentname'] = 'php';
	$list[]['componentname'] = 'IIS';
	$list[]['componentname'] = 'Photoshop';
	$list[]['componentname'] = 'InternetExplorer';

	try {
		$obj = new COM("Winmgmts://./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	$nlist = $obj->execQuery("select * from Win32_Product");

	foreach($nlist as $k => $l) {
		$name = $l->Name;
		$sing['nname'] = $name . "___" . $syncserver;
		$sing['componentname'] = $name;
		$sing['status'] = "off";
		$sing['version'] = "Not Installed";

		$sing['version'] = $l->Version;
		$sing['status'] = "on";
		/*
		if (isOn($sing['status'])) {
			$sing['full_version'] = `rpm -qi $name`; 
		} else {
			$sing['full_version'] = $sing['version'];
		}
	*/
		$ret[] = $sing;
	}
	return $ret;
}

}
