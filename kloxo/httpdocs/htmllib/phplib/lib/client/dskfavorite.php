<?php 


class ndskshortcut extends lxdb {
static $__desc = array("e", "",  "favorite");
static $__desc_nname  	 = array("nS", "",  "link");
static $__desc_url  	 = array("nS", "",  "link");
static $__desc_ddate  	 = array("n", "",  "date");
static $__desc_description  	 = array("n", "",  "description", "a=show");
static $__desc_separatorid  	 = array("n", "",  "separatorid");
static $__desc_sortid  	 = array("n", "",  "sortid", "a=show");
static $__desc_ttype  	 = array("n", "",  "type");
static $__desc_ttype_v_separator  	 = array("n", "",  "separator");
static $__desc_ttype_v_favorite  	 = array("n", "",  "favorite");
static $__desc_external  	 = array("nS", "",  "description");
static $__desc_default_description  	 = array("nS", "",  "default_description");
static $__acdesc_list  	 = array("nS", "",  "edit_favorites", "a=show");


function getId() { return $this->display('description'); } 

function isSync() { return false; }

static function perPage() { return 5000; }

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	//$alist[] = "a=addform&c=$class&dta[var]=ttype&dta[val]=favorite";
	$alist[] = "a=addform&c=$class&dta[var]=ttype&dta[val]=separator";
	return $alist;

}

function createExtraVariables()
{
}

function deleteSpecific()
{
	if_demo_throw_exception('short');
}

function postUpdate()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$gbl->setSessionV("__refresh_lpanel", true);
}

function postAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!$this->isOn('external') && !$this->isSeparator()) {
		$url = base64_decode($this->url);
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
	if ($param['ttype'] === 'separator') {
		$sq = new Sqlite(null, 'ndskshortcut');
		$separatorid = getIncrementedValueFromTable('ndskshortcut', 'separatorid');
		$param['separatorid'] = $separatorid;
		$param['nname'] = "{$separatorid}___{$parent->getClName()}";
		return $param;
	}
	if (isset($param['external']) && isOn($param['external'])) {
		$param['url'] = base64_encode($param['url']);
	}
	$param['nname'] = "{$param['url']}___{$parent->getClName()}";
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	if_demo_throw_exception('short');

	$vlist['sortid'] = null;

	if ($typetd['val'] !== 'separator') {
		$vlist['url'] = null;
		$vlist['description'] = null;
		$vlist['external'] = array('h', 'on');
	}
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListNlist($parent, $view)
{
	//$nlist['ddate'] = '10%';
	$nlist['sortid'] = '10%';
	$nlist['description'] = '100%';
	return $nlist;
}
function isSeparator()
{
	return ($this->ttype === 'separator');
}
function display($var)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($var === 'description') {

		if ($this->isSeparator()) {
			return "--Separator--";
		}
		if (isset($this->$var) && $this->$var) {
			return $this->$var;
		}
		$url = base64_decode($this->url);
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
	$vlist['sortid'] = null;
	if (!$this->isSeparator()) {
		$vlist['description'] = null;
	}
	return $vlist;
}

function createShowUpdateform()
{
	$uflist['description'] = null;
	return $uflist;
}

}

class ndsktoolbar extends ndskshortcut {

}
