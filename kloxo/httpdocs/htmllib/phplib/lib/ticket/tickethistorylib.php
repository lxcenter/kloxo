<?php 

class TicketHistory  extends Lxdb {

static $__desc = array("", "",  "reply");

static $__desc_nname = array("S", "",  "ticket_name");
static $__desc_state_from = array("", "",  "ticket_state_from");
static $__desc_state = array("eS", "",  "state");
static $__desc_state_v_open =   array("", "",  "Open"); 
static $__desc_state_v_fixed =   array("", "",  "fixed"); 
static $__desc_state_v_analyzed =   array("", "",  "analyzed");
static $__desc_state_v_closed =   array("", "",  "closed");
static $__desc_ddate = array("", "",  "date");
static $__desc_made_by = array("S", "",  "changed_by");
static $__desc_text_reason = array("StW", "",  "text_reason");

static $__desc_subject_f = array("St", "",  "subject");
static $__acdesc_add = array("", "",  "reply");




static function searchVar()
{
	return "text_reason";
}

static function ticketSendNotification($from, $id, $category, $object, $action, $actxt, $made_by, $subject, $message, $extra = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$o = $login->getObject('ticketconfig');

	$name = ucfirst($sgbl->__var_program_name);

	$notf = $object->getObject('notification');

	$val = null;
	$flag = $action . "_flag";
	if (!$object->contactemail || !$notf->notflag_b->isOn($flag)) {
		return;
	}

	log_message("Sending Notification $subject to $object->nname $object->contactemail \n");
	$subject = "[ticket:$object->nname:$id] $subject";
	list($parentclass, $parentname) = getParentNameAndClass($made_by);
	$mail = null;
	if ($o->isOn('mail_enable')) {
		$mail  .= "When replying please leave the subject intact for the helpdesk to parse...\n-----\n";
	}
	$mail .= "A ticket $actxt by $parentclass:$parentname at the $name ticketing system\n";
	$mail .= "Message:\n--------------------------\n";
	$mail .= $message;
	$mail .= "\n--------------------\n";


	 $reply_to = $o->mail_account;

	 if (!$reply_to) {
		 $reply_to = "helpdesk";
	 }

	 $extra .= "Reply-To: $reply_to\n";
	 $extra .= "X-Category: $category\n";

	 $contactemail = $object->getAllContactEmail();

	 callInBackground('lx_mail', array($from, $contactemail, $subject, $mail, $extra));
}

static function createListAlist($parent, $class)
{
	$alist[] = "a=show";
	return null;
}

static function createAddformList($parent, $class) { return true; } 

function isSync() { return false; }



static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$nlist['made_by'] = '10%';
	$nlist['text_reason'] = '100%';
	$nlist['ddate'] = '3%';
	$nlist['state'] = '3%';
	return $nlist;
}

function display($var)
{
	if ($var === 'made_by') {
		return $this->getParentName('made_by');
	}
	return parent::display($var);
}

static function defaultSort(){ return 'ddate'; }
static function defaultSortDir() {return "desc"; }


static function escalateSend($parent, $param, $fullmess)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$action = 'escalate';
	$obj = new client(null, null, 'admin');
	$obj->get();

	$extra = null;

	$from = "helpdesk";

	if ($sgbl->__var_program_name === 'lxlabsclient') {
		$ip = "lxlabs.com";

		if ($parent->isOn('escalate')) {
			$extra .= "X-escalate: Escalated\n";
		}

		/*
		if ($parent->used->vps_num > 100) {
			$subject = "P:100: $subject";
		} else if ($parent->used->vps_num > 50) {
			$subject = "P:50: $subject";
		} else {
			$subject = "P:10: $subject";
		}
	*/
	} else {
		$ip = getFQDNforServer('localhost');
	}

	$pass = $parent->realpass;
	$ticktid = $parent->nname;

	$extram = base64_encode(serialize(array('ticket_c' => $obj->getClName())));
	$urllink = "Click here to login to the Ticket: http://$ip:{$sgbl->__var_prog_port}/htmllib/phplib/?frm_clientname=$ticktid&frm_class=ticket&frm_password=$pass";


	$actxt = "has been escalated";
	$extra = "Message-ID: $parent->mail_messageid\n";

	$message = $fullmess;

	ticketHistory::ticketSendNotification($from, $parent->nname, $category, $obj, $action, $actxt, $param['made_by'], $parent->subject, $message, $extra);

}


static function getObjectsTosend($parent, $param, $action)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$pclass = $parent->getParentClass('made_by');
	$pname = $parent->getParentName('made_by');
	$obj = new $pclass($parent->__masterserver, null, $pname);
	$obj->get();
	$extra = null;

	if (isset($param['from_ad'])) {
		$from = $param['from_ad'];
	} else if (isset($param['pobject'])) {
		$from = $param['pobject']->contactemail;
	} else if ($obj->contactemail) {
		$from = "$obj->nname <$obj->contactemail>";
	} else {
		$from = "helpdesk";
	}


	$subject = $parent->subject;

	if ($parent->isOn('escalate')) {
		$extra .= "X-escalate: Escalated\n";
	}

	if ($sgbl->isLxlabsClient()) {
		$ip = "lxlabs.com";


		if ($obj->isClient() && !$obj->isAdmin()) {
			$obj->findTotalBalance(null);
			$sq = new Sqlite(null, "ticket");
			$tlist = $sq->getRowsWhere("made_by = 'client-$obj->nname' AND category LIKE '%TechnicalSupport%'");
			$nticket = count($tlist);
			$to = $obj->find_actual_billing("2009.05");
			$extra .= "X-lxheader: $to->total P: $obj->total_paid B: $obj->total_balance T: $nticket\n";
		}
	} else {
		$ip = getFQDNforServer('localhost');
	}

	$pass = $parent->realpass;
	$ticktid = $parent->nname;
	$category = $parent->category;

	$extram = base64_encode(serialize(array('ticket_c' => $obj->getClName())));
	$urllink = "Click here to login to the Ticket: http://$ip:{$sgbl->__var_prog_port}/htmllib/phplib/?frm_clientname=$ticktid&frm_class=ticket&frm_password=$pass";

	$otherclass = $parent->getParentClass('sent_to');
	$othername = $parent->getParentName('sent_to');
	if (!$otherclass) {
		return;
	}
	$otherobj = new $otherclass($parent->__masterserver, null, $othername);
	$otherobj->get();

	$extras =  base64_encode(serialize(array('ticket_c' => $otherobj->getClName())));

	if ($action === 'ticketadd') {
		$actxt = "has been opened";
		$extra .= "Message-ID: $parent->mail_messageid\n";
	} else {
		$actxt = "state has been changed from '$parent->state' to '{$param['state']}'";
		$extra .= "In-Reply-To: $parent->mail_messageid\n";
	}

	$message = "{$param['text_reason']}\n";

	ticketHistory::ticketSendNotification($from, $parent->nname, $category, $obj, $action, $actxt, $param['made_by'], $subject, $message, $extra);

	$message = $param['text_reason'];
	ticketHistory::ticketSendNotification($from, $parent->nname, $category, $otherobj, $action, $actxt, $param['made_by'], $subject, $message, $extra);

}


static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$parent->history_num += 1;
	$param['nname'] = $parent->nname . "___" . $parent->history_num;

	if (!isset($param['made_by'])) {
		if ($parent->isLogin()) {
			$ret = $gbl->getSessionV('extra_var');
			dprintr($ret);
			$param['made_by'] = $ret['ticket_c'];
		} else {
			$param['made_by'] = $parent->getParentO()->getClName();
		}
	}
		
	//$param['text_reason'] = str_replace("'", "\'", $param['text_reason']);
	$parent->dbaction = 'update';


	self::getObjectsTosend($parent, $param, "ticketchange");

	$parent->state = $param['state'];
	$parent->date_modified = time();
	$parent->setUpdateSubaction();
	return $param;
}


static function addform($parent, $class, $typetd = null)
{

	//$vlist['subject_f'] = array('M', $parent->subject);
	$slist = Ticket::getStateList();

	unset($slist[$parent->state]);
	$vlist['state'] = array('s', $slist);

	
	$vlist['text_reason'] = null;


	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;


}


}

