<?php 

class mimetype extends lxdb { 

static $__desc = array("", "",  "mime_type");
static $__desc_nname = array("", "",  "mime_type");
static $__desc_domainname = array("", "",  "domain_name");
static $__desc_syncserver = array("", "",  "domain_name");
static $__desc_type = array("n", "",  "type");
static $__desc_extension = array("n", "",  "extension");
static $__rewrite_nname_const =    Array("type", "domainname");


function createExtraVariables()
{
	$mydb = new Sqlite(null, "mimetype");
	$this->__var_mime_list = $mydb->getRowsWhere("syncserver = '{$this->syncserver}'");
}

static function add($parent, $class, $param)
{
	$param['domainname'] = $parent->nname;
	return $param;
}


static function addform($parent, $class, $typetd = null)
{
	$vlist['type'] = null;
	$vlist['extension'] = null;
	return $vlist;

}

static function initThisList($parent, $class)
{
	$sq = new Sqlite(null, 'mimetype');
	$list = $sq->getRowsWhere("domainname = '$parent->nname'");
	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'mimetype', $result, true);
}

}
