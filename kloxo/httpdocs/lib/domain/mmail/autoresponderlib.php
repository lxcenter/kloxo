<?php 


class Autoresponder extends Lxdb {

static $__desc = array("", "",  "auto_responder");
static $__desc_nname  	 = array("n", "",  "autoresponder_name", URL_SHOW);
static $__desc_autores_name  	 = array("n", "",  "autoresponder_name", URL_SHOW);
static $__desc_reply_subject  	 = array("n", "",  "subject_of_reply", URL_SHOW);
static $__rewrite_nname_const =    Array("autores_name", "parent_clname");
static $__desc_status  = array("e", "",  "s", URL_TOGGLE_STATUS);
static $__desc_status_v_on  = array("", "",  "enabled"); 
static $__desc_status_v_off  = array("", "",  "disabled"); 
static $__desc_rule = array("", "",  "request_text:request_text_in_subject_(blank_for_always_respond)");
static $__desc_text_message = array("t", "",  "message");

static $__acdesc_update_update = array("", "",  "edit");




function createShowUpdateform()
{
	$uform['update'] = null;
	return $uform;
}

function updateform($subaction, $param)
{
	$vlist['autores_name'] = array('M', null);
	//$vlist['rule'] = null;
	$vlist['text_message'] = null;
	return $vlist;
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=autoresponder";
	$alist[] = "a=addform&c=autoresponder";
	$alist[] = "a=updateform&sa=autores";
	$alist[] = $parent->getGenToggleUrl('autorespond');
	return $alist;
}
static function createListNlist($parent, $view)
{
	//$nlist['status'] = '5%';
	$nlist['autores_name'] = '100%';
	//$nlist['rule'] = '100%';
	return $nlist;
}

function display($val)
{
	if ($val === 'rule') {
		if (!$this->rule) {
			return "Always Respond";
		}
	}
	return $this->$val;
}

static function add($parent, $class, $param)
{
	$param['status'] = 'on';
	$param['parent_clname'] = $parent->getClName();
	return $param;
}

static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml;
	
	$vlist['autores_name'] = null;

	$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->syncserver, 'autoresponder');
	if ($driverapp === 'sync') {
		$vlist['rule'] = null;
	}

	$vlist['reply_subject'] = null;


	$vlist['text_message'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}


}
