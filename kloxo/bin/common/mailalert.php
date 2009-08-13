<?php 


include_once "htmllib/lib/include.php";


monitor_child();

function monitor_child()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_reminder;

	initProgram('admin');
	$login->loadAllObjects('client');
	$login->loadAllObjects('vps');
	$cllist = $login->getList('client');
	$vpslist = $login->getList('vps');
	$clist = lx_array_merge(array($cllist, $vpslist));
	foreach($clist as $c) {
		$downlist = null;
		$mlist = $c->getList('monitorserver');
		if (!$mlist) {
			continue;
		}
		foreach($mlist as $ml) {
			$plist = $ml->getList('monitorport');
			$eidlist = $ml->getList('emailalert');
			$nidlist = $c->getList('emailalert');
			$rlist = lx_array_merge(array($nidlist, $eidlist));
			$portlist =  process_port($rlist, $plist);

			if ($portlist) {
				$text = file_get_contents("../file/mailalert.txt");
				$text = str_replace("%port%", implode(" ", $portlist), $text);
				$text = str_replace("%server%", $ml->servername, $text);
				foreach($rlist as $eid) {
					if ((time() - $eid->last_sent) > $eid->period * 60) {
						log_message("Sending mail to $eid->emailid about $ml->servername at " . time());
						$global_reminder[$eid->emailid][] = array("s", $text);
						$eid->last_sent = time();
						$eid->setUpdateSubaction();
						$eid->write();
					}
				}

			}
		}

	}
}


