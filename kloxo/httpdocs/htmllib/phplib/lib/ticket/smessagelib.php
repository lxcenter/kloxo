<?php 

class Smessage extends Lxdb {

static $__desc = array("", "",  "message");


//Core

//Data
static $__desc_nname =  array("", "",  "id", "a=show");
static $__desc_made_by =   array("", "",  "created_by");
static $__desc_text_sent_to_cmlist =   array("", "",  "sent_to:send_to");
static $__desc_subject =   array("n", "",  "subject", "a=show");
static $__desc_send_mail_f =   array("f", "",  "send_mail", "a=show");
static $__desc_unread_flag_f =   array("e", "",  "unread"); 
static $__desc_unread_flag_f_v_off =   array("e", "",  "unread"); 
static $__desc_unread_flag_f_v_dull =   array("e", "",  "read"); 
static $__desc_ddate =   array("", "",  "date"); 
static $__desc_text_readby_cmlist =   array("", "",  "date"); 
static $__desc_text_description =   array("t", "",  "text_description"); 
static $__acdesc_update_update =   array("", "",  "message"); 


static function createListAlist($parent, $class)
{
	$alist[] = "a=list&c=$class";
	if ($parent->canHaveChild()) {
		$alist['__v_dialog_add'] = "a=addform&c=$class";
	}
	return $alist;

}
static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$i = 0;

	$list[] = "--all-children--" ;

	$cl = $parent->getChildListFilter('L');
	foreach($cl as &$c) {
		$c = $parent->getChildNameFromDes($c);
		$child = $parent->getList($c);
		foreach((array) $child as $q) {
			$list[] = self::getNameRep($q);
		}
	}


	$vlist['made_by'] = array('M', $parent->nname);
	$vlist['text_sent_to_cmlist'] = array('U', $list);
	$vlist['send_mail_f'] = null;
	$vlist['subject'] = null;
	$vlist['text_description'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}

static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$nlist['nname'] = "1%";
	$nlist['unread_flag_f'] = '3%';
	$nlist['subject'] = '100%';
	$nlist['ddate'] = '3%';
	//$nlist['responsible'] = '10%';
	$nlist['made_by'] = '10%';
	$nlist['text_sent_to_cmlist'] = '10%';
	//$nlist['text_readby_cmlist'] = '10%';
	return $nlist;
}


static function createWelcomeMessage()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$name = ucfirst($sgbl->__var_program_name);
	$param['made_by'] = createParentName('pserver', $name);
	$param['text_sent_to_cmlist'] = "," . createParentName('client', 'admin') . ",";
	$param['ddate'] = time();
	$param['subject'] = "Welcome to $name";
	$param['text_description'] = "Welcome to $name";
	$ticketconfig = $login->getObject('ticketconfig');

	$param['nname'] = $ticketconfig->getAndIncrementTicket();
	$param['unread_flag'] = 'on';
	$smessage = new Smessage(null, null, $param['nname']);
	$smessage->create($param);
	$smessage->write();

}

function isSync() { return false; }


function isSelect()
{
	if ($this->isRightParent()) {
		return true;
	}
	return false;
}


function display($var)
{
	if ($var === 'text_sent_to_cmlist') {
		if ($this->getParentO()->getClName() === $this->made_by) {
			$v = $this->convertClCmToNameCm($this->text_sent_to_cmlist);
			if (strlen($v) > 25) {
				$v = substr($v, 0, 25);
				$v .= "...";
			}
			return $v;

		} else {
			return $this->getParentO()->nname;
		}
	}
	if ($var === 'unread_flag_f') {
		if (exists_in_coma($this->text_sent_to_cmlist, $this->getParentO()->getClName())) {
			if (exists_in_coma($this->text_readby_cmlist, $this->getParentO()->getClName())) {
				$this->unread_flag_f = 'dull';
			} else {
				$this->unread_flag_f = 'on';
			}
		} else {
			$this->unread_flag_f = 'dull';
		}
		return $this->unread_flag_f;
	}

	if ($var === 'made_by') {
		return $this->getParentName('made_by');
	}
	return parent::display($var);
}

function createShowUpdateform()
{
	$uflist['update'] = null;
	return $uflist;
}

function updateform($subaction, $param)
{
	$vlist['made_by'] = array('M', $this->getParentName('made_by'));
	$vlist['subject'] = array('M', Htmllib::fix_lt_gt($this->subject));
	$vlist['ddate'] = array('M', lxgettime($this->ddate));
	if ($this->getParentO()->getClName() === $this->made_by) {
		$sent = $this->convertClCmToNameCm($this->text_sent_to_cmlist);
		$vlist['text_sent_to_cmlist'] = array('M', $sent);
	} else {
		$sent = $this->getParentO()->nname;
	}
	$vlist['text_description'] = array('t', null);
	if (!$this->isRightParent()) {
		$vlist['__v_button'] = array();
	}
	return $vlist;
}

static function getNameRep($q)
{
	return "$q->nname ({$q->get__table()})";
}

static function add($parent, $class, $param)
{
	$ticketconfig = $parent->getObject('ticketconfig');

	$cmlist = explode(',', $param['text_sent_to_cmlist']);
	$cl = $parent->getChildListFilter('L');
	foreach($cl as &$c) {
		$c = $parent->getChildNameFromDes($c);
		$child = $parent->getList($c);
		foreach((array) $child as $q) {
			if (!array_search_bool('--all-children--', $cmlist)) {
				if (array_search_bool(self::getNameRep($q), $cmlist)) {
					$list[$q->getClName()] = $q;
				}
			} else {
				$list[$q->getClName()] = $q;
			}
		}
	}

	$param['text_sent_to_cmlist'] = implode(',', array_keys($list));
	$param['text_sent_to_cmlist'] = "," . $param['text_sent_to_cmlist'] . ",";

	$param['ddate'] = time();
	$param['nname'] = getIncrementedValueFromTable("smessage", "nname");
	$param['made_by'] = $parent->getClName();
	$param['text_readby_cmlist'] = ",,";
	$param['name_made_by'] = $parent->nname;

	if (isOn($param['send_mail_f'])) {
		self::send_mail_to($list, $param);
	}

	return $param;
}

static function send_mail_to($list, $param)
{
	$subject = "Message from {$param['name_made_by']}: {$param['subject']}";
	$message = $param['text_description'];
	foreach($list as $l) {
		if ($l->contactemail) {
			lx_mail(null, $l->contactemail, $subject, $message);
		}
	}

}

function postRead()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (exists_in_coma($this->text_sent_to_cmlist, $login->getClName())) {
		if (!exists_in_coma($this->text_readby_cmlist, $login->getClName())) {
			$this->text_readby_cmlist .= $login->getClName() . ",";
			$this->setUpdateSubaction('');
		}
	}
}


static function initThisListRule($parent, $class)
{
	$res[] = array("made_by", '=', "'" . $parent->getClName() . "'");
	$res[] = 'OR';
	$res[] = array("text_sent_to_cmlist", 'LIKE', "'%," . $parent->getClName() .",%'");
	return $res;
}

}
