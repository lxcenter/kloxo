<?php 


class mimehandler extends lxdb {


static $__desc_extension	 = array("n", "",  "extension(s)");

static function createListNlist($parent, $view)
{
	$nlist['mimehandler'] = '100%';
	$nlist['extension'] = '10%';
	return $nlist;
}

function createExtraVariables()
{

	$path = $this->getParentO()->getFullDocRoot();
	$this->__var_htp = "$path/.htaccess";
	
	$sq = new Sqlite(null, $this->get__table());
	$res = $sq->getRowsWhere("parent_clname = '$this->parent_clname'");

	$result = merge_array_object_not_deleted($res, $this);

	foreach($result as $r) {
		$out[$r['mimehandler']] = $r['extension'];
	}

	$this->__var_mimehandler = $out;
}

static function createListAddForm($parent, $class) { return true; } 
static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function addform($parent, $class, $typetd = null)
{
	$vlist['mimehandler'] = null;
	$vlist['extension'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function add($parent, $class, $param)
{
	$param['nname'] = "{$parent->getClName()}___{$param['mimehandler']}";
	$param['syncserver'] = $parent->syncserver;
	return $param;
}

}


class webhandler extends mimehandler {

static $__desc =  array("", "",  "handler");
static $__desc_nname	 = array("n", "",  "client_name");
static $__desc_mimehandler	 = array("n", "",  "handler");

}

class webmimetype extends mimehandler {
static $__desc =  array("", "",  "mimetype");
static $__desc_nname	 = array("n", "",  "client_name");
static $__desc_mimehandler	 = array("n", "",  "mimetype");


}
