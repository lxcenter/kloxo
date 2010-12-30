<?php 

include "htmllib/lib/include.php";

create_license();


function create_license()
{
	global $gbl, $sgbl, $login, $ghtml, $argc, $argv; 

	$elements = array(
		'client' => 'On',
		'client_num' => 'Unlimited',
		'pserver_num' => '4',
		'maindomain_num' => 'Unlimited',
		'live_support' => 'On',
	);

	$opt = parse_opt($argv);

	if (!isset($opt['expiry_date']) || !isset($opt['ipaddress'])) {
		print("need expiry_date and IPaddress\n");
		print("Usage: $argv[0] --ipaddress= --expiry_date= [--client=] [--live_support=] [--pserver_num=] [--client_num=] [--maindomain_num]\n");
		exit;
	}


	$now = time();

	$timear = array('y' => 24* 3600 * 365, 'm' => 24 * 3600 * 30, 'd' => 24 * 3600, 'h' => 3600, 's' => 1);

	if (isset($timear[$opt['expiry_date'][strlen($opt['expiry_date']) - 1]])) {
		$val = $timear[$opt['expiry_date'][strlen($opt['expiry_date']) - 1]];
	} else {
		print("time is either y,m,d,h,s");
	}

	$time = substr($opt['expiry_date'], 0, strlen($opt['expiry_date']) - 1);
	print($time . "\n");
	$expiry_date = $now +  $time * $val;
	$opt['expiry_date'] = $expiry_date;
	$elements['expiry_date'] = $expiry_date;
	$elements['ipaddress'] = $opt['ipaddress'];
	$string = null;

	foreach($opt as $k => $v) {
		if (isset($elements[$k])) {
			$elements[$k] = $v;
		}
	}

	foreach($elements as $k => $v) {
		$string .= "$k=$v&";
	}

	dprint($string. "\n");

	$encrypted_string = licenseEncrypt($string);
	lfile_put_contents("license.txt", $encrypted_string);
}


