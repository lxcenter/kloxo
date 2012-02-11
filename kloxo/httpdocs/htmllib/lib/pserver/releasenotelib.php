<?php 
class ReleaseNote extends Lxlclass {

static $__desc = array("S", "",  "release_note");

// Mysql
static $__desc_nname =     array("n", "",  "note");
static $__desc_version =     array("n", "",  "version");
static $__desc_description =     array("n", "",  "description");
static $__desc_over_r =     array("e", "",  "Past");
static $__desc_over_r_v_dull =     array("", "",  "Old_version");
static $__desc_over_r_v_on =     array("", "",  "Newer_Version");
static $__desc_ttype =     array("", "",  "type");
static $__desc_ttype_v_critical =     array("s", "",  "critical");
static $__desc_ttype_v_feature =     array("s", "",  "enhancement");



static function createListAlist($parent, $class)
{
	$alist[] = "a=show";
	$alist[] = "a=list&c=$class";
	return $alist;
}

static function createListNlist($parent, $view)
{

	$nlist['over_r'] = '5%';
	$nlist['version'] = '5%';
	$nlist['ttype'] = '5%';
	$nlist['description'] = '100%';
	return $nlist;
}

static function defaultSort() { return "version"; }
static function defaultSortDir() { return "desc"; }

static function perPage() {return 500; }
function isSelect()
{
	return false;
}
static function createListBlist($parent, $class)
{
	return null;
}


static function parseReleaseNote($list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$maj = $sgbl->__ver_major;
	$ver = $sgbl->__ver_major_minor_release;
	$detail = null;
	$k = 0;
	$list = array_reverse($list);
	$mine = false;
	foreach($list as $l) {
		$dd = curl_get_file("releasenotes/release-$l.txt");
		// We are going backwards, and when we reach our own version we get out. We need info only about versions greater than ourselves.
		if ($l === $ver) {
			$mine = true;
		}
		foreach((array) $dd as $d) {
			$d = trim($d);
			if (!$d) {
				continue;
			}
			$k++;
			$v = explode(" ", $d);
			$newvar['version'] = $l;
			if ($mine) {
				$newvar['over_r'] = 'dull';
			} else {
				$newvar['over_r'] = 'on';
			}
			$newvar['ttype'] = array_shift($v);
			$newvar['description'] = implode(" ", $v);
			$newvar['nname'] = $k;
			$result[] = $newvar;
		}
	}

	return $result;

}

static function initThisList($parent, $class)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$ret = null;
	/*
	if (checkIfLatest()) {
		return $ret;
	}
*/
	$list = getFullVersionList();
	$result = self::parseReleaseNote($list);
	return $result;
}


}
