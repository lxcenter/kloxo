<?php 

class Process__Linux extends lxDriverClass {

static function readProcessList()
{


	$list = lscandir("/proc");

	foreach($list as $pid) {
		if (is_numeric($pid) && $pid[0] != ".") {

			$cmdlinearr = lfile("/proc/" . $pid. "/cmdline");

			$return[$pid]['nname'] = $pid;

			if (!$cmdlinearr)  {
				unset($return[$pid]);
				continue;
			}

			$cmdline = $cmdlinearr[0];
			$cmdline = preg_replace('+\0+i', " " , $cmdline);
			$return[$pid]["command"] = substr($cmdline, 0, 100);
			if (csa($cmdline, "display.php") && csa($cmdline, "kloxo")) {
				unset($return[$pid]);
				continue;
			}
			$arr  =  lfile("/proc/" . $return[$pid]["nname"] . "/status");
			foreach($arr as $a) {
				if (csb($a, "State:")) {
					$a = trim($a);
					$a = strtil($a, "(");
					$a = strfrom($a, "State:");
					$a = trim($a);
					$return[$pid]["state"] = $a;
					$return[$pid]["state"] = ($return[$pid]["state"] === "S")? "ZZ": $return[$pid]["state"];
				}

				if (csa($a, "Uid")) {
					$uidarr = explode(":", $a);
					$value = trimSpaces($uidarr[1]);
					$uidarr2 = explode(" ", $value);
					$uid = trim($uidarr2[1]);
					$pwd = posix_getpwuid($uid);
					$username = $pwd['name'];
					$return[$pid]["username"] = $username;
				}

				if (csa($a, "VmSize")) {
					$uidarr = explode(":", $a);
					$uidarr = trimSpaces($uidarr[1]);
					$uidarr = strtilfirst($uidarr, " ");
					$return[$pid]['memory'] = round($uidarr/1024, 2);
				}
			}
		}
	}
  return $return;

}


function dbactionUpdate($subaction)
{
	if_demo_throw_exception('ps');
	lxshell_return("kill", "-" . $this->main->signal, $this->main->nname);
}


}
