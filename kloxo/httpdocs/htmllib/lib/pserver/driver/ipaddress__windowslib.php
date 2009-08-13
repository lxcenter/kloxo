<?php 

class Ipaddress__Windows extends lxDriverClass{ 


static function listSystemIps($machinename)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$result = self::getCurrentIps();
	foreach($result as &$_res) {
		$_res['status'] = 'on';
	}

	foreach($result as $r) {
		if ($sgbl->isKloxo()) {
			ipaddress::copyCertificate($r['devname'], $machinename);
		}
	}

	return $result;
}

static function getCurrentIps()
{

	$ipconf = new COM("winmgmts://./root/cimv2");
	$list = $ipconf->ExecQuery("select * from Win32_NetworkAdapterConfiguration where IPEnabled=TRUE");
	foreach($list as $l) {
		if ($l->IPAddress) {
			//for($i = 0; $i< count($l->IPAddress); $i++) {
			foreach($l->IPAddress as $ip) {
				$res['ipaddr'] = $ip;
				$res['devname'] = "Ethernet-" . $l->Index;
				foreach($l->IPSubnet as $s) {
					$sub[] = "$s";
				}
				foreach($l->DefaultIPGateway as $d) {
					$dg[] = "$d";
				}
				$res['netmask'] = implode(",", $sub);
				$res['gateway'] = implode(",", $dg);
				$result[] = $res;
			}
		}
	}

	return $result;
}
}
