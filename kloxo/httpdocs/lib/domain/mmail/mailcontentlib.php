<?php 


class Mailcontent extends Lxclass {


static $__desc = array("", "",  "mail");
static $__desc_nname = array("", "",  "mail");
static $__desc_location = array("", "",  "location");
static $__desc_subject = array("", "",  "subject", "a=show");
static $__desc_from = array("", "",  "from", "a=show");
static $__desc_body = array("", "",  "body");
static $__desc_header = array("", "",  "header");
static $__desc_date = array("", "",  "date");
static $__acdesc_update_edit = array("", "",  "Content");
static $__acdesc_list = array("", "",  "spam_training");



function get() {}
function write() {}

function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function getId()
{
	return basename($this->nname);
}

static function createListAlist($parent, $class) 
{
	$alist[] = "a=list&c=$class";
	$alist[] = "a=update&sa=clear_spam_db";
	return $alist;
}

function updateform($subaction, $param)
{
	$vlist['from'] = array('M', $this->from);
	$vlist['subject'] = array('M', $this->subject);
	$vlist['header'] = array('T', $this->header);
	//$vlist['body'] = array('T', $this->body);
	$vlist['__v_button'] = array();
	return $vlist;
}

static function createListBlist($object, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isAdmin()) {
		$blist[] = array('a=update&sa=train_as_system_spam');
		$blist[] = array('a=update&sa=train_as_system_ham');
	}

	$blist[] = array("a=update&sa=train_as_spam");
	$blist[] = array("a=update&sa=train_as_ham");
	return $blist;
}
static function createListNlist($parent, $view)
{
	$nlist['location'] = '10%';
	$nlist['from'] = '10%';
	$nlist['date'] = '10%';
	$nlist['subject'] = '100%';
	return $nlist;
}

static function initThisListRule($parent, $class) { return null; }

static function initThisList($parent, $class) 
{
	$res = rl_exec_in_driver($parent, $class, "getMailContent", array($parent->nname));
	return $res;

}

}
