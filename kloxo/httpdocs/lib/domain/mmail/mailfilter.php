<?php 


class MailFilter extends Lxdb {

//Core
static $__desc = array("", "",  "mail_account");

//Data
static $__desc_nname  	 = array("", "",  "account_name");
static $__desc_rule  	 = array("", "",  "rule");
static $__desc_action  	 = array("", "",  "action");
static $__rewrite_nname_const =    Array("parent_clname", "rule");

function createListNlist()
{
	$nlist['rule'] = '100%';
	$nlist['action'] = null;
	return $nlist;
}
}
