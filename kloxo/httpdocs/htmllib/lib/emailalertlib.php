<?php 


class emailalert extends lxdb {


static $__desc = array("S", "",  "Email Contact");
static $__desc_nname =  array("n", "",  "email_address");
static $__desc_emailid =  array("n", "",  "email_address", "a=show");
static $__desc_period =  array("n", "",  "alert period (minutes)");

static $__rewrite_nname_const = array("emailid", "parent_clname");

static $__acdesc_update_update =  array("","",  "information"); 
static $__acdesc_list =  array("","",  "contacts"); 




function getId() { return strtilfirst($this->nname, "___"); }


static function addform($parent, $class, $typetd = null)
{

	$list = array("10", "20", "30", "40", "50", "60");
	$vlist['emailid'] = null;
	//$vlist['period'] = array('s', $list);
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';

	return $ret;

}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	return $alist;

}

static function createListAddForm($parent, $class)
{
	return true;

}

function updateform($subaction, $param)
{
	$list = array("10", "20", "30", "40", "50", "60");
	$vlist['emailid'] = array('M', null);
	$vlist['period'] = array('s', $list);
	return $vlist;
}
function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}
function isSync() { return false ; }

static function createListNlist($parent, $view)
{
	$nlist['emailid'] = '100%';
	//$nlist['period'] = '10%';
	return $nlist;
}


}
