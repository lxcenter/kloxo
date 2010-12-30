<?php 

class mailqueue__qmail extends lxDriverClass {


static function QueueFlush()
{
	lxshell_return("pkill", "-14", "-f", "qmail-send");
}

static function QueueDelete($list)
{
	global $gbl, $sgbl, $login, $ghtml; 

	foreach($list as $f) {
		$string[] = "-d$f";
	}
	$string = implode(" ", $string);
	lxfile_unix_chmod("__path_program_root/bin/misc/qmHandle", "0755");
	exec_with_all_closed("$sgbl->__path_program_root/bin/misc/qmHandle $string");
}

static function readSingleMail($name)
{
	$ret['message'] = lxshell_output("__path_program_root/bin/misc/qmHandle", "-m$name");
	$oldtime = time() - 7200;
	$newtime = time() - 500;
	$fp = fopen("/var/log/kloxo/maillog", "r");
	$fsize = lxfile_size("/var/log/kloxo/maillog");
	$ot =  date("Y-m-d:H-i");
	dprint("Start time: $ot\n");
	$res = FindRightPosition($fp, $fsize, $oldtime, $newtime, array("mailtraffic", "getTimeFromOriginalQmailString"));

	if ($res < 0) {
		$ret['log'] = null;
		return $ret;
	} 

	//$s = fgets($fp);
	dprint("here $s\n");
	
	takeToStartOfLine($fp);
	takeToStartOfLine($fp);

	//$s = fgets($fp);
	dprint("here $s\n");

	$delivery = null;
	while (!feof($fp)) {
		$s = fgets($fp);
		if (!$delivery) {
			if (csa($s, "starting delivery") && csa($s, "msg $name")) {
				$delivery = preg_replace("/.*delivery ([^:]*):.*/", "$1", $s);
				$delivery = trim($delivery);
				dprint("Deliver num: $delivery*\n");
				continue;
			}
		} else {
			dprint("$s\n");
			if (csa($s, "delivery $delivery:")) {
				dprint("$s\n");
				$ret['log'] = $s;
				break;
			}
		}
	}

	return $ret;
}

static function readMailqueue()
{

	lxfile_unix_chmod("__path_program_root/bin/misc/qmHandle", "0755");
	$res = lxshell_output("__path_program_root/bin/misc/qmHandle", "-l");

	$list = array('subject', 'to', 'from', 'date', 'size');

	//$res = lfile_get_contents("a.txt");


	$res = explode("\n", $res);
	//dprintr($res);

	$i = 0;
	foreach($res as $r) {
		$r = trim($r);
		if (!$r) {
			$i++;
			continue;
		}

		if (is_numeric($r[0])) {
			list($nname, $s, $ss) = explode(" ", $r);
			$ret[$i]['nname'] = $nname;
			if (cse($ss, 'R)')) {
				$ret[$i]['type'] = 'remote';
			} else {
				$ret[$i]['type'] = 'local';
			}
			continue;
		}

		foreach($list as $l) {
			$ul = ucfirst($l);
			if (csb($r, "$ul:")) {
				$ret[$i][$l] = strfrom($r, "$ul:");
				if ($l === 'size') {
					$ret[$i][$l] = strtil($ret[$i][$l], " bytes");
					$ret[$i][$l] = trim($ret[$i][$l]);
				}
			}
		}
	}


	return $ret;

}


}

