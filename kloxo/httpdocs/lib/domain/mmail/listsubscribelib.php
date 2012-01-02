<?php 


class ListSubscribe extends Lxclass {


static $__desc = array("", "",  "list_member");

//Data
static $__desc_nname  	 = array("", "",  "list_member");
static $__desc_address  	 = array("", "",  "addresses (one per line)");
static $__rewrite_nname_const =    Array("address", "parent_clname");

function get() {}
function write() {}


static function add($parent, $class, $param)
{
	$param['parent_clname'] = $parent->getClName();
	$param['syncserver'] = $parent->syncserver;
	return $param;

}

static function createListAlist($parent, $class) 
{
	$alist[] = "a=show";
	$alist[] = "a=updateform&sa=update";
	$alist[] = "a=list&c=mailinglist_mod_a";
	$alist[] = "a=updateform&sa=editfile";
	$alist[] = "a=addform&c=listsubscribe";
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['address'] = '100%';
	return $nlist;
}

static function addform($parent, $class, $typetd = null)
{

	$vlist['address'] = array('t', null);
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}

static function initThisList($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$res = rl_exec_in_driver($parent, 'listSubscribe', "readSubscribeList", array($parent->nname));

	// Creat the extra varibles.. That are normally stgored in the db.

	if ($res) foreach($res as &$__rt) {
		$__rt['nname'] = "{$__rt['address']}___{$parent->getClName()}";
		$__rt['syncserver'] = $parent->syncserver;
		$__rt['parent_clname'] = createParentName("mailinglist", $parent->nname);
	}
	return $res;
}



}

