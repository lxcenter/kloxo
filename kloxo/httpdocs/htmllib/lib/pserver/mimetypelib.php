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
<<<<<<< HEAD
	$this->__var_mime_list = $mydb->getRowsWhere("syncserver = '{$this->syncserver}'");
=======
	$this->__var_mime_list = $mydb->getRowsWhere('syncserver = :syncserver', array(':syncserver' => $this->syncserver));
>>>>>>> upstream/dev
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
<<<<<<< HEAD
	$list = $sq->getRowsWhere("domainname = '$parent->nname'");
=======
	$list = $sq->getRowsWhere('domainname = :nname', array(':nname' => $parent->nname));
>>>>>>> upstream/dev
	$parent->setListFromArray($parent->__masterserver, $parent->__readserver, 'mimetype', $result, true);
}

}
