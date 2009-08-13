<?php 

function send_to_some_http_server($raddress, $port, $url)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$url = "login-class=client&login-name=admin&login-password=hell&output-type=json&$url";

	$ch = curl_init("http://$raddress:$port/bin/webcommand.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
	$totalout = curl_exec($ch);
	$totalout = trim($totalout);
	$totalout = json_decode($totalout);
	return $totalout;
}

function get_and_print_a_select_variable($description, $remotevar, $localvar)
{
	list($server, $port) = explode(":", $_SERVER['SERVER_NAME']);
	print("$description <br> ");
	// Send the remote variable to hypervm and get the result.
	$out = send_to_some_http_server($server, $port, "action=simplelist&resource=$remotevar");
	// The resul is a json object with two paramters.
	// The message, which will tell you whether it was a success or not, and the result, which conttins the data.

	// Check the reutrn value, you can print the message you got from hypervm.
	if ($out->return === 'error') {
		print("The server said, error. The message is:\n");
		print($out->message);
		exit;
	}

	print_select($localvar, $out->result);
	print("<br> ");
}

function print_select($var, $list)
{
	print("<select name=$var> ");
	foreach($list as $realname => $displayname) {
		print("<option value=$realname> $displayname </option>");
	}
	print("</select>");
}
