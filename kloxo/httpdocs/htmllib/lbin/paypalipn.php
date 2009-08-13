<?php 
// read the post from PayPal system and add 'cmd'
chdir("../../");
include_once "htmllib/lib/include.php"; 


$req = 'cmd=_notify-validate';

log_log("paypal_billing", "start " . var_export($_POST, true));

foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);

// assign posted variables to local variables
//$item_name = $_POST['item_name'];
//$item_number = $_POST['item_number'];
//$payment_status = $_POST['payment_status'];
//$payment_amount = $_POST['mc_gross'];
//$payment_currency = $_POST['mc_currency'];
//$txn_id = $_POST['txn_id'];
//$receiver_email = $_POST['receiver_email'];
//$payer_email = $_POST['payer_email'];

if (!$fp) { sleep(3); }
$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);
if (!$fp) { sleep(3); }
$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);

if (!$fp) {
	log_log("paypal_billing", "Could not connect to paypal");
	// HTTP ERROR
} else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {
			paymentDetail::process_paypal($_POST);
			// check the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment
		} else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			log_log("paypal_billing", "Fake confirmation " . var_export($_POST, true));
		}
	}
	fclose ($fp);
}


