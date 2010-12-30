<?php 


class Ticket extends Lxclient {

static $__desc = array("", "",  "ticket");


//Core

//Data
static $__desc_nname =  array("", "",  "id", "a=show");
static $__desc_state =   array("e", "",  "s:state"); 
static $__desc_state_v_open =   array("", "",  "Open"); 
static $__desc_state_v_fixed =   array("", "",  "fixed"); 
static $__desc_escalate =   array("e", "",  "E:escalate"); 
static $__desc_escalate_v_ =   array("", "",  "Off"); 
static $__desc_escalate_v_on =   array("", "",  "On"); 
static $__desc_escalate_v_off =   array("", "",  "Off"); 
static $__desc_escalate_v_dull =   array("", "",  "Off"); 
static $__desc_state_v_analyzed =   array("", "",  "analyzed");
static $__desc_state_v_closed =   array("", "",  "closed");
static $__desc_priority =   array("e", "",  "p:priority");
static $__desc_priority_v_low =   array("", "",  "low");
static $__desc_priority_v_medium =   array("", "",  "medium");
static $__desc_priority_v_high =   array("", "",  "high");
static $__desc_priority_v_urgent =   array("", "",  "urgent");
static $__desc_responsible =   array("", "",  "responsible");
static $__desc_category =   array("", "",  "category");
static $__desc_subcategory =   array("", "",  "sub_category");
static $__desc_made_by =   array("", "",  "created_by");
static $__desc_history_num =   array("", "",  "replies");
static $__desc_sent_to =   array("", "",  "sent_to:send_to");
static $__desc_subject =   array("n", "",  "subject", "a=show");
static $__desc_ddate =   array("", "",  "date"); 
static $__desc_date_modified =   array("", "",  "modified_on"); 
static $__desc_unread_flag =   array("e", "",  "unread"); 
static $__desc_unread_flag_v_dull =   array("e", "",  "read"); 
static $__desc_unread_flag_v_on =   array("e", "",  "unread"); 

static $__desc_descr_f =   array("t", "",  "description"); 

static $__acdesc_update_close =   array("", "",  "close"); 
static $__acdesc_list =   array("", "",  "help_desk"); 
static $__acdesc_update_escalate =   array("", "",  "escalate"); 


static $__desc_filter_show_nonclosed = array("", "",  "show_nonclosed"); 
static $__desc_filter_show_open = array("", "",  "show_open"); 
static $__desc_filter_show_all = array("", "",  "show_all"); 
static $__desc_filter_show_mine = array("", "",  "show_mine"); 

//Objects

//Lists
static $__desc_tickethistory_l =   array("db", "",  "history"); 

static $__hpfilter_show_all = '';
static $__hpfilter_show_nonclosed = array('state', '!=', '"closed"');
static $__hpfilter_show_open = array('state', '=', '"open"');
static $__hpfilter_show_mine = array('sent_to', '=', '"client-admin"');


static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist[] = "a=list&c=$class";
	$alist[] = "a=addform&c=$class";
	if ($login->isAdmin()) {
		$alist[] = "o=ticketconfig&a=updateform&sa=ticketconfig";
		$alist[] = "o=general&c=helpdeskcategory_a&a=list";
	}
	return $alist;

}

function close()
{
	$this->state = "closed";
	$this->dbaction = "update";
}

static function searchVar() { return "subject"; }
function isSync() { return false; }
function isSelect() { global $gbl, $sgbl, $login, $ghtml; return $this->getParentO()->isAdmin(); }

static function defaultSort() { return "ddate"; }
static function defaultSortDir() { return "desc"; }

function isAction($var)
{
	global $gbl, $sgbl, $login;

	if ($var === "made_by" || $var === "sent_to") {
		if ($this->$var === $login->nname) {
			return false;
		}
	}
	return true;
}

function createShowAddform()
{
	//$aflist['tickethistory'] = null;
	//return $aflist;
}

function createShowPropertyList(&$alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$alist['property'][] = "a=show";
	$alist['property'][] = "a=addform&c=tickethistory";
	if ($login->isAdmin() && ($sgbl->isLxlabsClient() || $sgbl->isdebug())) {
		$alist['property'][] = "a=updateform&sa=escalate";
	}
	return $alist;
}
static function createListNlist($parent, $view)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$nlist['nname'] = "1%";
	//$nlist['priority'] = '3%';
	if ($login->isAdmin() && ($sgbl->isLxlabsClient() || $sgbl->isdebug())) {
		$nlist['escalate'] = '3%';
	}
	$nlist['unread_flag'] = '3%';
	$nlist['made_by'] = '10%';
	$nlist['subject'] = '100%';
	$nlist['state'] = '3%';

	if ($sgbl->__var_ticket_subcategory) {
		$nlist['subcategory'] = '3%';
	}

	$nlist['category'] = '3%';
	$nlist['date_modified'] = '3%';
	$nlist['history_num'] = '3%';
	//$nlist['responsible'] = '10%';
	$nlist['sent_to'] = '10%';
	return $nlist;
}

static function createListSlist($parent)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$nlist['nname'] = null;

	$gen = $login->getObject('general');
	$cat = get_namelist_from_objectlist($gen->helpdeskcategory_a);
	$cat = lx_merge_good('--any--', $cat);

	$nlist['category'] = array('s', $cat);
	return $nlist;
}


function updateEscalate($param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = $this->getList('tickethistory');
	$message = null;
	foreach($list as $l) {
		$dd = @ date('Y-M-d h:m');
		$message .= "----------$dd-------\n";
		$message .= "$l->text_reason\n";
	}


	$hist = new TicketHistory($this->__masterserver, $this->__readserver, $this->nname . "___" . ++$this->history_num);
	$rhis['text_reason'] = "\nEscalated\n\n";
	$rhis['state_from'] = "";
	$rhis['ddate'] = time();
	$rhis['parent_clname'] = $this->getClName();
	$rhis['state'] = "open";
	$rhis['made_by'] = $this->made_by;
	$hist->create($rhis);
	$this->addToList("tickethistory", $hist);

	$em['text_reason'] = "Escalating the ticket: Past messages:\n\n";
	$em['text_reason'] .= $message;
	$em['state']  = 'escalate';
	$em['made_by']  = $login->getClName();
	$this->escalate = 'on';
	ticketHistory::getObjectsTosend($this, $em, "ticketchange");
	$param['nothing'] = 'h';

	return $param;
}


function createShowClist($subaction)
{
	$clist['tickethistory'] = null;
	return $clist;

}

function updateClose($param)
{

	$hist = new TicketHistory($this->__masterserver, $this->__readserver, $this->nname . "___" . ++$this->history_num);
	$rhis['text_reason'] = "Default Close";
	$rhis['state_from'] = "";
	$rhis['ddate'] = time();
	$rhis['parent_clname'] = $this->getClName();
	$rhis['state'] = "closed";
	$rhis['made_by'] = $this->made_by;
	$hist->create($rhis);
	$this->addToList("tickethistory", $hist);
	$param['state'] = 'closed';
	ticketHistory::getObjectsTosend($this, $rhis, "ticketchange");
	return $param;

}

function deleteSpecific()
{
	$rhis['state'] = "delete";
	$rhis['made_by'] = $this->getParentO()->nname;
	$rhis['text_reason'] = 'delete';
	ticketHistory::getObjectsTosend($this, $rhis, "ticketchange");
}

function postAdd()
{

	$hist = new TicketHistory($this->__masterserver, $this->__readserver, $this->nname . "___" . '0');
	$rhis['text_reason'] = $this->descr_f;
	$rhis['state_from'] = "";
	$rhis['ddate'] = time();
	$rhis['parent_clname'] = $this->getClName();
	$rhis['state'] = "open";
	$rhis['made_by'] = $this->made_by;
	$hist->create($rhis);
	$this->addToList("tickethistory", $hist);

	$this->realpass = randomString(6);
	$this->password = crypt($this->realpass);
	$this->status = 'on';
	$this->cpstatus = 'on';
	$this->escalate = 'dull';
	list($sec, $usec) = explode(" ", microtime());
	$this->mail_messageid = "<$sec$usec.GA8614@lxlabs.com>";

	ticketHistory::getObjectsTosend($this, $rhis, "ticketadd");
}


static function add($parent, $class, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 



	if ($parent->get__table() !== 'client') {
		$param['sent_to'] = $parent->getParentO()->getClName();
	}

	if ($param['sent_to'] === '--all_children--') {
		throw new lxexception('select_one_child', 'sent_to');
	}

	//$param['parent_clname'] = "client_s_vv_p_admin";
	$param['parent_clname'] = "client-admin";
	$param['nname'] = getIncrementedValueFromTable("ticket", "nname");
	$param['made_by'] = $parent->getClName();
	$param['unread_flag'] = 'on';
	$param['state'] = 'open';
	$param['ddate'] = time();
	$param['date_modified'] = time();

	$param['history_num'] = 0;
	return $param;
}

static function getStateList()
{
	$slist = array('open' => 'open', 'closed' => 'closed', 'fixed' => 'fixed', 'analyzed' => 'analyzed');
	return $slist;

}

static function createListBlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$blist[] = array("a=delete&c=$class");
	//$blist[] = array("a=update&sa=close&c=$class");
	$blist[] = array("a=list&c=$class&frm_filter[show]=nonclosed", 1);
	$blist[] = array("a=list&c=$class&frm_filter[show]=open", 1);
	$blist[] = array("a=list&c=$class&frm_filter[show]=all", 1);

	return $blist;
}

static function createWelcomeTicket()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$name = ucfirst($sgbl->__var_program_name);

	$parent['parent_clname'] = createParentName('client', 'admin');
	$param['made_by'] = createParentName('pserver', $name);
	$param['sent_to'] = createParentName('client', 'admin');
	$param['subject'] = "Welcome to $name";
	//$param['priority'] = "low";
	$param['category'] = "Welcome";
	$param['descr_f'] = "Welcome to $name";
	$param['ddate'] = time();
	$param['date_modified'] = time();
	$ticketconfig = $login->getObject('ticketconfig');

	$param['nname'] = $ticketconfig->getAndIncrementTicket();
	$ticketconfig->write();
	$param['unread_flag'] = 'on';
	$param['state'] = 'open';
	$ticket = new Ticket(null, null, $param['nname']);
	$ticket->create($param);
	$ticket->postAdd();
	$ticket->was();

}

static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 

	if ($parent->get__table() === 'client') {
		$tag['key'] = "--all_children--" ;
		$tag['val'] = "-----Children";
		$list = $parent->getAllChildrenAndParents($tag);
	}

	$gen = $login->getObject('general');

	$cat = get_namelist_from_objectlist($gen->helpdeskcategory_a);

	$vlist['made_by'] = array('M', $parent->nname);

	if ($parent->get__table() === 'client') {
		$vlist['sent_to'] = array('A', $list);
	} else {
		$vlist['sent_to'] = array('M', $parent->getParentO()->nname);
	}

	if (!$cat) {
		$cat = array("billing");
	}
	$vlist['category'] = array('s', $cat);
	$vlist['subject'] = null;
	//$vlist['priority'] = array('s', array('low', 'medium', 'high', 'urgent'));
	$vlist['descr_f'] = null;
	if ($sgbl->isLxlabsClient()) {
		$vlist['__m_message_pre'] = "lxlabsclient_ticket_forum";
	}
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;

}

function display($var)
{
	if ($var === 'made_by') {
		return $this->getParentName('made_by');
	}
	if ($var === 'sent_to') {
		return $this->getParentName('sent_to');
	}
	return parent::display($var);
}

function postRead()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($login->getClName() === $this->sent_to) {
		$this->unread_flag = 'dull';
		$this->setUpdateSubaction('');
	}
}

function dontDelete()
{
	global $gbl, $sgbl, $login, $ghtml; 
	return !$login->isAdmin();
}




static function initThisListRule($parent, $class)
{
	if ($parent->isAdmin()) {
		return "__v_table";
	}
	$res[] = array("made_by", '=', "'" . $parent->getClName() . "'");
	$res[] = 'OR';
	$res[] = array("sent_to", '=', "'" . $parent->getClName() ."'");
	return $res;
}

}

