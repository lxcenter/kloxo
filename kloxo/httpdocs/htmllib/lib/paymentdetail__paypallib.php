<?php 


class paymentdetail__paypal extends Lxdriverclass {

static function createPaymentDetail($list)
{
	$ret['amount'] = $list['mc_gross'];
	$ret['info'] =  $list['payer_email'];
	$ret['transactionid'] = $list['txn_id'];


	$id = $list['item_name'];
	if (!csb($id, "lx_")) {
		log_log("paypal_billing", "Not lx_, skipping... $id\n");
		return;
	}

	$cllist = explode("_", $id);

	$ret['month'] = $cllist[1];
	array_shift($cllist);
	array_shift($cllist);
	$ret['client'] = implode("_", $cllist);

	if (!$ret['client']) {
		log_log("paypal_billing", "No client for transactionid {$ret['transactionid']}.. Exiting...\n");
		return;
	}

	$ret['ddate'] = time();

	return $ret;
}



}

