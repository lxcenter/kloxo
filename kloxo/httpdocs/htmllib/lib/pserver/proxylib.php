<?php 


class Proxy extends Lxdb {

//Core
static $__desc = array("", "",  "proxy");

//Data
static $__desc_nname   =  Array("", "",  "proxy");
static $__desc_syncserver   =  Array("", "",  "proxy");
static $__desc_proxyacl_l = array('', '', '', '');



function createShowClist($subaction)
{
	$clist['proxyacl'] = null;
	return $clist;
}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=user';
	$alist['property'][] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=host';
	$alist['property'][] = 'a=addform&c=proxyacl&dta[var]=ttype&dta[val]=group';
}
function createShowAlist(&$alist, $subaction = null)
{
	return $alist;

}

}




