<?php 

class dskshortcut_a extends Lxaclass {
static $__desc = array("e", "",  "favorite");
static $__desc_nname  	 = array("nS", "",  "link", "a=show");
static $__desc_ddate  	 = array("n", "",  "date", "a=show");
static $__desc_description  	 = array("n", "",  "description", "a=show");
static $__desc_external  	 = array("nS", "",  "description", "a=show");
static $__desc_default_description  	 = array("nS", "",  "default_description", "a=show");


function getId() { return $this->display('description'); } 

function isSync() { return false; }

static function perPage() { return 5000; }

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	$alist[] = "a=addform&c=$class";
	return $alist;

}

function postUpdate()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gbl->setSessionV("__refresh_lpanel", true);
}

function postAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$this->isOn('external')) {
		$url = base64_decode($this->nname);
		if ($sgbl->isHyperVM() && isset($this->vpsparent_clname)) {
			$url = kloxo::generateKloxoUrl($this->vpsparent_clname, null, $url);
			$gbl->__this_redirect = "$url&frm_refresh_lpanel=true";
		} else {
			$gbl->__this_redirect = $url;
			$gbl->setSessionV("__refresh_lpanel", true);
		}
	}
	$gbl->setSessionV("__refresh_lpanel", true);
	$this->ddate = time();
}

static function add($parent, $class, $param)
{
	if (isset($param['external']) && isOn($param['external'])) {
		$param['nname'] = base64_encode($param['nname']);
	}
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = null;
	$vlist['description'] = null;
	$vlist['external'] = array('h', 'on');
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListNlist($parent, $view)
{
	$nlist['ddate'] = '10%';
	$nlist['description'] = '100%';
	return $nlist;
}
function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === 'description') {

		if (isset($this->$var) && $this->$var) {
			return $this->$var;
		}
		$url = base64_decode($this->nname);
		$buttonpath = get_image_path() . "/button/";
		$description = $ghtml->getActionDetails($url, null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
		return "$description[2] for $__t_identity";
	}

	return parent::display($var);
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$this->description) {
		$this->description = $this->display('description');
	}
	$vlist['description'] = null;
	return $vlist;
}

function createShowUpdateform()
{
	$uflist['description'] = null;
	return $uflist;
}

}
