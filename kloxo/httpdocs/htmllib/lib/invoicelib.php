<?php 

class invoice extends Lxdb {

static $__desc = array("", "",  "invoice");

static $__desc_nname =  array("n", "",  "payment");

static $__desc_ddate = array("", "",  "date");
static $__desc_total = array("", "",  "total");
static $__desc_paid = array("", "",  "paid:payments_recevied_to_this_invoice");
static $__desc_remaining = array("", "",  "amount");
static $__desc_payment_url = array("", "",  "payment_url");
static $__desc_client = array("", "",  "client");
static $__desc_month = array("", "",  "month", "a=show");
static $__desc_paymentgw = array("", "",  "payment_gateway");
static $__desc_text_invoice = array("", "",  "invoice");
static $__desc_m_text_invoice_f = array("", "",  "invoice");
static $__acdesc_update_edit = array("", "",  "edit");

function dosyncToSystem() { }

function getId()
{
	return str_replace("___", " for ", $this->nname);
}

static function createListAlist($parent, $class)
{
	return paymentdetail::createListAlist($parent, $class);
}

static function defaultSort() { return "ddate"; }
static function defaultSortDir() { return "desc"; }

function isSync()
{
	return false;
}

function createShowUpdateform()
{
	$uflist['edit'] = null;
	return $uflist;
}

function getPaymentUrl()
{
	$con = lfile_get_contents("../file/url.txt");
	$con = str_replace("%lxmonth%", $this->month, $con);
	$con = str_replace("%username%", $this->client, $con);
	$p = $this->getTotalPaid();
	$left = $this->total - $p;
	if ($left == 0) { return "already_paid"; }
	$con = str_replace("%grand_total%", $left, $con);
	return $con;
}

function display($var)
{
	if ($var === 'paid') {
		return $this->getTotalPaid();
	}
	return parent::display($var);

}

function getTotalPaid()
{
	$sq = new Sqlite(null, "paymentdetail");
	$res = $sq->getRowsWhere("month = '$this->month' AND client = '$this->client'");
	$total = 0;
	if ($res) {
		foreach($res as $r) {
			$total += $r['amount'];
		}
	}
	return $total;
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$vlist['payment_url'] = array('M', $this->getPaymentUrl());
	$vlist['month'] = array('M', null);
	$total = $this->getTotalPaid();
	$vlist['paid'] = array('M', $total);
	$vlist['ddate'] = array('M', lxgettime($this->ddate));

	if ($login->isAdmin()) {
		$vlist['total'] = null;
		$vlist['text_invoice'] = array('t', null);
	} else {
		$vlist['total'] = array('M', "\$$this->total");
		$this->m_text_invoice_f = preg_replace("+(https://[^ \n]*)+", "[b] (Please Use the Link at the Top) [/b] ", $this->text_invoice);
		$vlist['m_text_invoice_f'] = array('M', $this->m_text_invoice_f);
		$vlist['__v_button'] = array();
	}

	return $vlist;

}

function isSelect()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isAdmin()) {
		return true;
	}
	return false;
}

static function createListNlist($parent, $view)
{
	if ($parent->isAdmin()) {
		$nlist['client'] = '10%';
	}
	$nlist['month']  = '100%';
	$nlist['ddate']  = '10%';
	$nlist['total'] = '10%';
	$nlist['paid'] = '10%';
	//$nlist['transactionid'] = '10%';
	return $nlist;
}


static function initThisListRule($parent, $class)
{
	if ($parent->isAdmin()) {
		return "__v_table";
	}

	return array('parent_clname', '=', "'" . createParentName($parent->getClass(), $parent->nname). "'");
}


}
