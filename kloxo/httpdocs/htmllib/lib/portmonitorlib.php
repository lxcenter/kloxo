<?php 

function port_send_email($portstatus)
{
	$port = $portstatus->getParentO();
	$mac = $port->getParentO();
	$client = $mac->getParentO();
	$eidlist = $client->getList('emailalert');
	$contactid = new emailalert(null, null, $client->contactemail);
	$contactid->emailid = $client->contactemail;
	$eidlist[$contactid->nname] = $contactid;
	process_port($eidlist, array($port));
}


function do_send_email()
{
	global $global_reminder;
	//fprint($global_reminder, 0);
	foreach($global_reminder as $k => $v) {
		$string = null;
		foreach($v as $kk => $vv) {
			$string .= "{$vv[1]}\n";
		}
		lx_mail(null, $k, "Message from hyperVM alert system", $string);
	}
}


function process_port($eidlist, $plist)
{
	$downlist = null;
	foreach($plist as $p) {
		$statlist = $p->getList('portstatus');
		$match = false;
		if (!$statlist) {
			dprint("Something's very wrong...\n");
			continue;
		}

		$rcount = 0;
		foreach($statlist as $k => $s) {
			$mingstatus = $s->getObject('monitoringserverstatus');
			if ($mingstatus->updatetime > (time() - 900)) {
				$rcount++;
			} else {
				$s->dbaction = 'delete';
				$s->write();
				unset($statlist[$k]);
			}
		}

		if (!$rcount) {
			// There are no recent updates. All updates are old. So just skip.
			continue;
		}


		$upcount = 0;
		$totalerr = null;
		$downcount = 0;
		foreach($statlist as $s) {
			if ($s->isOn('portstatus')) {
				$upcount++;
				$totalerr[] = "{$s->servername}, success";
			} else {
				$downcount++;
				$totalerr[] = "{$s->servername}, {$s->errorstring}";
			}
		}

		$totalerr = implode(": ", $totalerr);

		// If the port is down, then no one will ever say it is up, but if it is up, then it is possible that some of the buggers say it is down, because of their own personal problems. So if even one guy says it is up, then it is up.

		// We need two guys at least to make a decision.
		if ($rcount == 1) {
			if ($upcount == 1) {
				$newstatus = 'on';
			} else {
				$newstatus = 'off';
				$downlist[] = $p->portnumber;
			}
		} else {
			if ($upcount >= 2) {
				$newstatus = 'on';
			} else {
				$newstatus = 'off';
				$downlist[] = $p->portnumber;
			}
		}


		if ($newstatus !== $p->portstatus) {
			$gap = time() - $p->changetime;
			$p->portstatus = $newstatus;
			$p->changetime = time();
			$p->setUpdateSubaction();
			$p->write();
			send_mails_to_all($eidlist, $p, $gap, strtilfirst($p->getParentName(), "___"));
			$porthname = $p->nname . "___" . time();
			$porthist = new PortHistory(null, null, $porthname);
			$porthist->initThisDef();
			$porthist->portnname = $p->nname;
			$porthist->ddate = time();
			$porthist->laststatustime = $gap;
			$porthist->portstatus = $newstatus;

			if ($porthist->isOn('portstatus')) {
				$porthist->errorstring = "";
			} else {
				$porthist->errorstring = $totalerr;
			}

			// Temporarility let us have a record of what happened...
			$porthist->errorstring = $totalerr;
			$porthist->dbaction = 'add';
			$porthist->write();

		}
	}
	return $downlist;
}

function send_mails_to_all($eidlist, $p, $gap, $servername)
{
	global $global_reminder;
	if ($p->isOn("portstatus")) {
		$oldstate = "Down";
		$newstate = "Up";
	} else {
		$oldstate = "Up";
		$newstate = "Down";
	}
	$text = lfile_get_contents("__path_program_root/file/statechange.txt");
	$text = str_replace("%port%", $p->portnumber, $text);
	$text = str_replace("%oldstate%", $oldstate, $text);
	$text = str_replace( "%gap%", round($gap/60, 1), $text);
	$text = str_replace("%newstate%", $newstate, $text);
	$text = str_replace("%server%", $servername , $text);
	foreach($eidlist as $eid) {
		$global_reminder[$eid->emailid][] = array("p", $text);
		$eid->last_sent = time();
		$eid->setUpdateSubaction();
		$eid->write();
	}
}

