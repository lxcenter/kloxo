<?php 


class package__yum extends lxDriverClass {

static function getYumCommand()
{
	return 'yum check-update';
}

static function getPackages($nocache = false)
{

	$cmd = self::getYumCommand();
	$file = fix_nname_to_be_variable($cmd);
	$file = "__path_program_root/cache/$file";

	if ($nocache) {
		$val = `$cmd`;
	} else {
		$val = get_with_cache($cmd, $file);
	}


	$list = explode("\n", $val);

	$match = false;
	$res = null;
	foreach($list as $l) {
		if (strstr($l, "---------------------")) {
			$match = true;
			continue;
		}
		if (!$match) {
			continue;
		}

		$l = trimSpaces($l);

		$l = trim($l);
		if (!$l) {
			continue;
		}
		$v = explode(" ", $l);
		$out['nname'] = $v[0];
		//$out['arch'] = $v[1];
		$out['update_version'] = $v[2];
		$out['kloxo_status'] = 'off';
		if ($v[3] === 'kloxo-repos') {
			$out['kloxo_status'] = 'on';
		} 
		$out['repo'] = $v[3];
		$res[] = $out;
	}

	//dprintr($res);
	return $res;



}

static function doUpdate($list)
{
	$cmd = self::getYumCommand();
	$file = fix_nname_to_be_variable($cmd);
	$file = "__path_program_root/cache/$file";

	lxshell_return("yum", "-y", "install", implode(" ", $list));
	lunlink($file);

}

}
