<?php 

class TicketConfig  extends Lxdb {

static $__desc = array("", "",  "ticket_history");
static $__acdesc_update_ticketconfig = array("", "",  "helpdesk_configuration");
static $__desc_ticketid = array("", "",  "current_ticket_id_(can_only_be_increased)");
static $__desc_mail_account = array("", "",  "mail_account");
static $__desc_mail_server = array("", "",  "mail_server");
static $__desc_mail_period = array("", "",  "mail_period_(minutes)");
static $__desc_mail_password = array("", "",  "mail_password");
static $__desc_mail_ssl_flag = array("f", "",  "use_ssl");
static $__desc_mail_enable = array("f", "",  "enable_mail");


static function initThisObjectRule($parent, $class, $name = null)
{
	return 'ticketconfig';
}

function getAndIncrementTicket()
{
	$this->ticketid++;
	$ret = $this->ticketid;
	$this->setUpdateSubaction();
	$this->write();
	return $ret ;
}

function isSync() { return false; }

function updateTicketconfig($param)
{
	if (intval($param['ticketid']) < $this->ticketid) {
		//throw new lxException ("ticketid_cannot_be_less_than_current", 'ticketid');
	}
	return $param;
}

function createShowPropertyList(&$alist)
{
	$nalist = ticket::createListAlist($this->getParentO(), 'ticket');
	foreach($nalist as $a) {
		$alist['property'][] = "goback=1&$a";
	}
	
}
function updateform($subaction, $param)
{
	//$vlist['ticketid'] = null;
	$vlist['__c_subtitle_mailgate'] = "MailGate";
	$vlist['mail_enable'] = null;
	//$vlist['mail_period'] = null;
	$vlist['mail_server'] = null;
	$vlist['mail_account'] = null;
	$vlist['mail_password'] = null;
	$vlist['mail_ssl_flag'] = null;
	return $vlist;
}
}
