<?php 


class Wlist_a extends LxMailClass {

static $__desc = array("", "",  "white_list");
static $__desc_nname = array("n", "",  "white_list_address");

static function createListAddForm($parent, $class) { return true; } 
static function createListAlist($parent, $class)
{
	
	$alist[] = 'a=show';
	$alist[] = 'a=list&c=wlist_a';
	$alist[] = 'a=list&c=blist_a';
	return $alist;
}


}

class Blist_a extends LxMailClass {

static $__desc = array("", "",  "black_list");
static $__desc_nname = array("n", "",  "black_list_address");
static function createListAddForm($parent, $class) { return true; } 

static function createListAlist($parent, $class)
{

	return Wlist_a::createListAlist($parent, $class);
}

}

class Spam extends Lxdb {

static $__desc = array("", "",  "spam");
static $__desc_status = array("f", "",  "enable_spam_filter");
static $__desc_status_v_on = array("e", "",  "spam_is_on");
static $__desc_status_v_off = array("e", "",  "spam_is_off");
static $__desc_nname = array("", "",  "spam");
static $__desc_spam_hit = array("n", "",  "score_at_which_mail_is_judged_spam");
static $__desc_subject_tag = array("", "",  "subject_tag_for_spam");
static $__desc_wlist_a 	=  array("", "",  "white_list");
static $__desc_blist_a 	=  array("", "",  "black_list");

static $__acdesc_update_update 	=  array("", "",  "spam_status");

function defaultValue($var)
{
	if ($var === 'spam_hit') {
		return "5";
	}

	if ($var === 'subject_tag') {
		return "******SPAM******";
	}
}



function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	return null;
	if ($this->getTrueParentO()->isClass('mailaccount') && !$this->getTrueParentO()->isLogin()) {
		$this->getTrueParentO()->createShowPropertyList($alist);
		foreach($alist['property'] as &$__a) {
			if (!$ghtml->is_special_url($__a)) {
				$__a = strfrom($__a, "goback=2&");
				$__a = "goback=3&$__a";
			}
		}
	} else if ($this->getTrueParentO()->isClass('mmail')) {
		$alist['property'][] = 'goback=2&a=show';
		$alist['property'][] = 'goback=1&a=list&c=mailaccount';
		$alist['property'][] = 'goback=2&a=show&sa=config';
	} else {
		$alist['property'][] = 'a=show';
	}
	//$alist['property'][] = 'a=list&c=wlist_a';
	//$alist['property'][] = 'a=list&c=blist_a';
}

function createShowAlist(&$alist, $subaction = null)
{

	return $alist;
}

function createShowClist($subaction)
{
	return null;
}


static function removeOtherDriver($driverapp)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($driverapp === 'bogofilter') {
		@ exec("rpm -e --nodeps spamassassin 2>/dev/null");
	} else if ($driverapp === 'spamassassin') {
		@ exec("rpm -e --nodeps bogofilter 2>/dev/null");
	}
}

static function switchProgramPost($old, $new)
{
	if ($new === 'spamassassin') {
		lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");
		createRestartFile("spamassassin");
	}
}

function createShowUpdateform()
{
	$ulist['update'] = null;
	return $ulist;
}

function updateform($subaction, $param)
{
	if (!$this->status) {
		$this->status = 'off';
	}

	$vlist['status'] = null;
	$vlist['spam_hit'] = null;
	$vlist['subject_tag'] = null;
	$vlist['__v_updateall_button'] = array();
	return $vlist;
}

function postUpdate()
{
	$parent = $this->getTrueParentO();

	if ($parent->is__table('mmail')) {
		$list = $parent->getList('mailaccount');
		foreach($list as $l) {
			$sp = $l->getObject('spam');
			$sp->spam_hit = $this->spam_hit;
			$sp->status = $this->status;
			$sp->subject_tag = $this->subject_tag;
			$sp->setUpdateSubaction($this->subaction);
			$l->setUpdateSubaction('change_spam');
			$l->was();
			$sp->was();
		}
	} else {
		$parent->setupdateSubaction('change_spam');
	}
}


}
