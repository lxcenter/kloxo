<?php


class Hostdeny extends Lxdb
{

//Core
static $__desc = array("", "",  "blocked_host");

//Data
static $__desc_nname = array("", "",  "blocked_host");
static $__desc_parent_name  = array("", "",  "blocked_host");
static $__desc_syncserver = array("", "",  "blocked_host");
static $__desc_hostname = array("", "",  "host_name");

static $__rewrite_nname_const  = array("hostname","syncserver");


function createExtraVariables()
{
	$pserver = $this->getParentO();
	$hdb = new Sqlite($this->__masterserver, 'hostdeny');
	$string = "syncserver = '{$pserver->nname}' " ;
	$hlist = $hdb->getRowsWhere($string);
	$this->__var_hostlist = $hlist;
	dprintr($this->__var_hostlist);

}


static function createListNlist($parent, $view)
{
	
	//$nlist["nname"] = "100%";
	$nlist["hostname"] = "100%";

	return $nlist;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;

}

static function add($parent, $class, $param)
{
	$param['syncserver'] = $parent->nname;
	return $param;
}

static function addform($parent, $class, $typetd = null)
{

	$vlist['hostname'] = array('m', null);
	$ret['action'] = "add";
	$ret['variable'] = $vlist;
	return $ret;
}

static function createListAddForm($parent, $class)
{
	return true;

}



}



