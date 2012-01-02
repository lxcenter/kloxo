<?php 

class ProxyAcl extends Lxdb {

//Core
static $__desc = array("", "",  "proxy_acl");

//Data
static $__desc_nname   =  Array("", "",  "proxy");
static $__desc_syncserver   =  Array("", "",  "proxy");
static $__desc_http   =  Array("", "",  "proxy");
static $__desc_ftp   =  Array("", "",  "proxy");
static $__desc_id   =  Array("", "",  "proxy");
static $__desc_classid   =  Array("", "",  "class-id", URL_SHOW);
static $__desc_description   =  Array("", "",  "proxy");
static $__desc_password   =  Array("", "",  "proxy");
static $__desc_extensions   =  Array("", "",  "proxy");
static $__desc_group   =  Array("", "",  "proxy");
static $__desc_ttype =  Array("e", "",  "proxy");
static $__desc_ttype_v_host =  Array("", "",  "host");
static $__desc_ttype_v_user =  Array("", "",  "user");
static $__desc_ttype_v_group =  Array("", "",  "group");
static $__desc_status  = array("e", "",  "s", URL_TOGGLE_STATUS);
static $__desc_status_v_on  = array("", "",  "enabled"); 
static $__desc_status_v_off  = array("", "",  "disabled"); 


static function createListNlist($parent, $view)
{

	$nlist['status'] = '5%';
	$nlist['http'] = '5%';
	$nlist['ftp'] = '5%';
	$nlist['id'] = '5%';
	$nlist['classid'] = '30%';
	$nlist['extensions'] = '5%';
	$nlist['description'] = '100%';
	return $nlist;
}

static function getGroupList()
{
	return array('something');
}

static function addform($parent, $class, $typetd = null)
{
	switch($typetd['val']) {
		case 'group':
			{
				$vlist['id'] = null;;
				$vlist['classid'] = null;
				$vlist['http'] = null;
				$vlist['ftp'] = null;
				break;
			}

		case 'user':
			{
				$vlist['id'] = null;
				$vlist['classid'] = null;
				$vlist['password'] = null;
				$vlist['group'] = array('s', self::getGroupList());
				break;
			}
		case 'host':
			{
				$vlist['id'] = null;
				$vlist['ipaddress'] = null;
				$vlist['group'] = array('s', self::getGroupList());
				break;
			}
	}

	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

function updateform($subaction, $param)
{
	$vlist['http'] = null;
	$vlist['ftp'] = null;
	$vlist['description'] = null;
	return $vlist;
}

function createShowUpdateform()
{

	$uflist['update'] = null;
	return $uflist;
}

static function createListAlist($parent, $class)
{

	$alist[] = 'a=show';
	$alist[] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=user';
	$alist[] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=host';
	$alist[] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=group';
	return $alist;
}

}

