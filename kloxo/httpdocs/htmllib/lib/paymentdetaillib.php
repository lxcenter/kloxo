<?php 

class paymentDetail extends lxdb {

static $__desc = array("", "",  "payment");

static $__desc_nname =  array("n", "",  "payment");

static $__desc_ddate = array("", "",  "date");
static $__desc_amount = array("", "",  "amount");
static $__desc_client = array("", "",  "client");
static $__desc_month = array("", "",  "month");
static $__desc_paymentgw = array("", "",  "payment_gateway");
static $__desc_transactionid = array("", "",  "transaction_id");

static $__rewrite_nname_const =  array("client", 'month');

function dosyncToSystem() { }


static function defaultSort() { return "ddate"; }
static function defaultSortDir() { return "desc"; }

static function createListAlist($parent, $class)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$alist[] = "a=list&c=billingamount";
	if ($login->isAdmin()) {
		$alist[] =  "a=addform&c=billingamount";
	}
	
	$alist[] = "a=list&c=invoice";

	$alist[] = "a=list&c=paymentdetail";
	if ($login->isAdmin()) {
		$alist[] =  "a=addform&c=paymentdetail";
		$alist[] = "a=updateform&sa=createinvoice";
	}
	


	return $alist;
}

static function add($parent, $class, $param)
{
	$param['client'] = $parent->nname;
	$param['ddate'] = time();
	return $param;
}

function isSync()
{
	return false;
}
static function addform($parent, $class, $typetd = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$vlist['client'] = array('M', $parent->nname);
	$sq = new Sqlite(null, 'invoice');
	$res = $sq->getRowsWhere("client = '$parent->nname'", array("month"));
	$list = get_namelist_from_arraylist($res, 'month');
	$list = array_reverse($list);
	$vlist['month'] = array('s', $list);
	$vlist['amount'] = null;
	$vlist['paymentgw'] = null;
	$vlist['transactionid'] = null;
	$ret['variable'] = $vlist;
	$ret['action'] = 'add';
	return $ret;
}


static function createListNlist($parent, $view)
{
	if ($parent->isAdmin()) {
		$nlist['client'] = '100%';
	}
	$nlist['month']  = '100%';
	$nlist['ddate']  = '10%';
	$nlist['amount'] = '10%';
	$nlist['paymentgw'] = '10%';
	$nlist['transactionid'] = '10%';
	return $nlist;
}

static function initThisListRule($parent, $class)
{
	if ($parent->isAdmin()) {
		return "__v_table";
	}

	return array('parent_clname', '=', "'" . createParentName($parent->getClass(), $parent->nname). "'");
}


static function checkIftransactionExists($transactionid)
{
	static $sq;
	if (!$sq) { $sq = new Sqlite(null, 'paymentdetail'); }

	if ($sq->getRowsWhere("transactionid = '$transactionid'")) {
		return true;
	}
	return false;
}

static function process_paypal($list)
{
	initProgram('admin');

	$sq = new Sqlite(null, 'paymentdetail');

	$r = paymentdetail__paypal::createPaymentDetail($list);

	if (self::checkIftransactionExists($r['transactionid'])) {
		log_log("paypal_billing", "Transactionid {$r['transactionid']} already exists\n");
		return;
	}

	$i = 0;
	while (true) {
		$r['nname'] = implode("___", array($r['client'], $r['month'], $i));
		if (!$sq->getRowsWhere("nname = '{$r['nname']}'")) {
			break;
		}
		$i++;
	}

	$r['parent_clname'] = createParentName('client', $r['client']);

	$cl = new Client(null, null, $r['client']);
	$cl->get();
	if (!$cl->isOn('status')) {
		$cl->updateEnable(null);
		$cl->was();
	}

	$payp = new paymentDetail(null, null, $r['nname']);

	$r['complete_detail'] = $list;

	$r['paymentgw'] = 'paypal';

	$payp->create($r);

	$payp->write();
	log_log("paypal_billing", "saved the payment detail $p->nname");

}

}
