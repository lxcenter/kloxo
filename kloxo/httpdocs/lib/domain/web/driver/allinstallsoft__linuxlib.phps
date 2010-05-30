<?php 

class allinstallapp__linux extends LxDriverclass {

static function getListofApps()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->dbg < 1) {
		$list = lfile("__path_kloxo_httpd_root/installappdata/description/base_linux.data");
	} else {
		$list = lfile("__path_kloxo_httpd_root/installappdata/description/base_linux.data");
		//$list = lfile("__path_kloxo_httpd_root/installappdata/description/base_linux.data.debug");
	}

	$res = null;

	$res[] = array('nname' => 'installapp', 'appname' => 'installapp', 'description' => "installapp");
	foreach((array) $list as $l) {
		$l = trim($l);
		if (!$l) {
			continue;
		}
		if ($l[0] === '#') {
			continue;
		}
		$v = explode(" ", $l);
		$r = null;
		$r['nname'] = array_shift($v);
		$r['appname'] = $r['nname'];
		$r['description'] = implode(" ", $v);

		$res[] = $r;
	}

	return $res;

}


}
