<?php 

include_once "htmllib/lib/include.php"; 
include_once "htmllib/lib/parsemail/parseMail.php";

initProgram('admin');

$STDIN = fopen("php://stdin", "r");
$content = null;
while (!feof($STDIN)) {
	$content .= fread($STDIN, 8192);
}


$email = new parseMail($content);

$subject = $email->subject;
$message = $email->part[0]['content'];
$smallfrom  = $email->from;

if (csa($smallfrom, "<")) {
	$smallfrom = strfrom($smallfrom, "<");
	$smallfrom = strtilfirst($smallfrom, ">");
}
$smallfrom = trim($smallfrom);
$smallfrom = trim($smallfrom, "<>");
//$email->from = $smallfrom;

$email->message = $message;


preg_match("/.*\[ticket:([^:]*):([^:]*)\].*/i", $subject, $matches);
print_r($matches);


if (!$matches) {
	$param['subject'] = $subject;
	$param['descr_f'] = $message;
	$param['sent_to'] = 'client-admin';
	$param['category'] = 'complaint';
	$param['priority'] = 'medium';
	$csq = new Sqlite(null, 'client');
	$c = $csq->getRowsWhere("contactemail = '$smallfrom'", array('nname'));
	if ($c) {
		$clientname = $c[0]['nname'];
		$client = new Client(null, null, $clientname);
		$client->get();
		$param = ticket::add($client, 'ticket', $param);
		$tick = new Ticket(null, null, $param['nname']);
		$tick->create($param);
		$tick->postAdd();
		$tick->was();
	} else {
		$m = "There is no user with your from address in the system.";
		$m .= "-------------------\n.........$content";
		mail($smallfrom, "HelpDesk Failed", $m);
	}
	exit;
}


$ticketid = $matches[2];


$pclass = "client";
$pname = $matches[1];

if (cse($pname, ".vm")) {
	$pclass = "vps";
}

	/*
if (!csa($matches[1], "-")) {
	$pclass = "client";
	$pname = $matches[1];
} else {
	$pp = explode("-", $matches[1]);
	$pclass = $pp[0];
	$pname = $pp[1];
}
*/

$pobject = new $pclass(null, null, $pname);
$pobject->get();


$tick = new Ticket(null, null, $ticketid);
$tick->get();

$param['state'] = 'open';
$param['text_reason'] = $message;
$param['pobject'] = $pobject;
$param['made_by'] = createClName($pclass, $pname);
$param['from_ad'] = $email->from;





$param = tickethistory::add($tick, 'tickethistory', $param); 


$newob = new TicketHistory(null, null, $param['nname']);

$newob->ddate = time();
$tick->unread_flag = 'dull';
$newob->parent_clname = $tick->getClName();
$newob->create($param);
$newob->write();
$tick->write();

