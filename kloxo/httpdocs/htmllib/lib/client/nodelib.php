<?php 

class Node extends ClientBase {


static $__desc  = array("","",  "node"); 

static $__desc_nname =     array("", "",  "node", URL_SHOW);

static $__desc_client_o = array("qR", "",  "");



static function add($parent, $class, $param)
{
	$param['realpass'] = $param['password'];
	$param['password'] = crypt($param['password']);
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	$vlist['nname'] = null;
	$vlist['password'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function createShowAlist(&$alist, $subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['__title_main'] = $login->getKeywordUc('resource');
	$this->getLxclientActions($alist);
	$alist[] = 'a=show&o=client';
	return $alist;
}

}

