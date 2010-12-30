<?php 

class dirindexlist_a extends Lxaclass {
	static $__desc =  array("", "",  "directory_index");
	static $__desc_nname =  array("", "",  "directory_index");

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;
}


static function createListAddForm($parent, $class)
{
	return true;
}
}


class genlist extends Lxdb {

//Core

//Data
static $__desc =  array("", "",  "general");
static $__desc_nname =  array("", "",  "general");
static $__acdesc_dirindex =  array("", "",  "directory_index_list");

static function initThisObjectRule($parent, $class, $name = null)
{
	return 'admin';
}

}


