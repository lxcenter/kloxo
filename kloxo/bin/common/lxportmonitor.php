<?php 


class Remote { }

$sgbl = new Remote();

$global_failure = false;

set_global_debug();
monitor_main();


function monitor_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;
	global $global_ip_array;
	global $global_remoteserver;
	global $global_remoteport;

	error_reporting(E_ALL);
	

	$list = parse_opt($argv);

	if (isset($list['data-server'])) {
		$global_remoteserver = $list['data-server'];
		$global_remoteport = "8888";
	} else  {
		$global_remoteserver = "localhost";
		$global_remoteport = "5558";
	}


	if (false) {
		if (function_exists("posix_getpwnam")) {
			if (!isset($list['switch-user'])) {
				print("Needs a non-privileged user to be suplied as --switch-user=<user>... Using lxlabs\n");
				$list['switch-user'] = 'lxlabs';
			}

			$pw = posix_getpwnam($list['switch-user']);
			if (!$pw) {
				print("User {$list['switch-user']} doesnt exist. Please create it\n\n\n\n");
				exit;
			}

			posix_setuid($pw['uid']);
			posix_setgid($pw['gid']);
		}
	}



	$sgbl->thisserver = get_my_name();



	$list = get_data_from_server();

	if (!$list) {
		print("No list from the server...\n");
	}

	dprintr($list);

	getDnsesFirst($list);

	$oldserverhistlist = null;



	$maincount = 0;
	while (1) {
		$maincount++;
		$serverhistlist = null;
		$sendserverhistlist = null;
		$startmaintime = time();

		foreach($list as $l) {
			$ports = $l['monitorport_l'];
			$porthistlist = null;
			foreach($ports as $p) {
				if (isset($portmonlist[$l['nname']][$p['nname']][2])) {
					print("SOcket Already exists... \n");
					socket_close($portmonlist[$l['nname']][$p['nname']][2]);
				}
				$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
				socket_set_nonblock($socket);
				$portmonlist[$l['nname']][$p['nname']] = array($l['servername'], $p['portnumber'], $socket);
			}
		}

		$serverhistlist = null;
		dprintr($portmonlist);
		do_monitor_list($portmonlist, $serverhistlist);

		$portmonlist = prepare_error_portmonlist($serverhistlist);

		dprintr("Second try\n\n");
		dprintr($portmonlist);

		sleep(1);
		do_monitor_list($portmonlist, $serverhistlist);

		$endmaintime = time();

		if ($oldserverhistlist) {
			foreach($serverhistlist as $s => $slist) {
				foreach($slist as $p => $plist) {
					if (!isset($oldserverhistlist[$s][$p])) {
						$sendserverhistlist[$s][$p] = $serverhistlist[$s][$p];
						continue;
					}
					if ($serverhistlist[$s][$p]['portstatus'] !== $oldserverhistlist[$s][$p]['portstatus']) {
						$sendserverhistlist[$s][$p] = $serverhistlist[$s][$p];
					}
				}
			}
		} else {
			$sendserverhistlist = $serverhistlist;
		}

		$oldserverhistlist = $serverhistlist;
		if ($sendserverhistlist) {
			dprint("Sending Data\n");
			//print_r($sendserverhistlist);
			dprintr($sendserverhistlist);
			send_data_to_server($sendserverhistlist);
		}
			
			
		$timeleft = 60 - $endmaintime + $startmaintime;

		if ($timeleft > 0) {
			//print("Sleep for $timeleft\n");
			sleep($timeleft);
		} else {
			//print("No sleep for $timeleft\n");
		}

		if ($maincount === 10) {
			$maincount = 1;

			if ($global_failure) {
				//print("Sending Data\n");
				//print($serverhistlist);
				send_data_to_server($serverhistlist);
			}
			$list = get_data_from_server();
			send_alive_info();
			//print("Getting again from server\n");
			//$oldserverhistlist = null;
			//print_r($list);
			getDnsesFirst($list);
		}
	}

}

function get_my_name()
{
	global $global_remoteserver; 

	$rmt = new Remote();
	$rmt->cmd = "my_name";
	$res = remote_http_exec_monitor($global_remoteserver, "80", $rmt);
	return $res;

}

function send_alive_info()
{

	$host = `hostname`;
	global $global_remoteserver; 

	$rmt = new Remote();
	$rmt->cmd = "im_alive";
	$rmt->ddata = null;
	$res = remote_http_exec_monitor($global_remoteserver, "80", $rmt);
}

function send_data_to_server($serverhistlist)
{
	global $global_remoteserver; 

	$rmt = new Remote();
	$rmt->cmd = "set_list";
	$rmt->ddata = $serverhistlist;
	$res = remote_http_exec_monitor($global_remoteserver, "80", $rmt);

}

function get_data_from_server()
{
	$rmt = new Remote();
	global $global_remoteserver;

	$rmt->cmd = "get_list";
	$res = remote_http_exec_monitor($global_remoteserver, "80", $rmt);
	return $res;
}

function remote_http_exec_monitor($server, $port, $rmt)
{
	global $global_remoteport;
	global $global_remoteserver;

	$port = $global_remoteport;
	$server = $global_remoteserver;
	$var = base64_encode(serialize($rmt));
	$data = send_to_some_http_server_monitor($server, "", $port, $var);

	dprint($server);

	$res = unserialize(base64_decode($data));

	dprintr($res);

	if (!$res) {
		print("Got Nothing from server for CMd: $rmt->cmd\n");
		print($data);
		$global_failure = true;
	} else {
		$global_failure = false;
	}

	if ($res->exception) {
		throw $res->exception;
	}
	return $res->ddata;
}



function send_to_some_http_server_monitor($raddress, $socket_type, $port, $var)
{
	global $gbl, $sgbl, $login, $ghtml; 

	//print_time('server');

	$ch = curl_init("http://$raddress:$port/htmllib/mibin/monitordata.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "frm_rmt=$var");
	$totalout = curl_exec($ch);
	print(curl_error($ch));
	print($totalout);
	$totalout = trim($totalout);
	return $totalout;
}


function checkPort($sname, $num, $nname)
{
	global $gbl, $sgbl, $login, $ghtml; 


	$sip = gethostbyname($sname);

	if (validate_ipaddress($sip)) {
		$res =  fsockopen($sip, $num, $erno, $erstr, 10);
	} else {
		$res = null;
		$erno = 1;
		$erstr = "Dns failed";
	}


	if (!$res) {
		$obj['portstatus'] = 'off';
		$obj['errornumber'] = $erno;
		$obj['errorstring'] = $erstr;
	} else {
		fclose($res);
		$obj['portstatus'] = 'on';
		$obj['errorstring'] = "";
	}

	$obj['portnname'] = $nname;

	$obj['servername'] = $sgbl->thisserver;
	return $obj;
}


function validate_ipaddress($ip)
{
	$ind= explode(".",$ip);
	$d=0;
	$c=0;
	foreach($ind as $in) {
		$c++;
		if(is_numeric($in) && $in >= 0 && $in <= 255 ) {
			$d++;
		} else {
			return 0;
		}
	}
	if($c ===  4)   {
		if($d === 4) {
			return 1;
		} else {
			return 0;
		}
	} else  {
		return 0;
	}
}


function parse_opt($argv)
{
	unset($argv[0]);
	if (!$argv) {
		return  null;
	}
	foreach($argv as $v) {
		if (!(strpos($v, "--") === 0)) {
			$ret['final'] = $v;
			continue;
		}
		$v = strfrom($v, "--");
		if (strstr($v, "=") !== false) {
			$opt = explode("=", $v);
			$ret[$opt[0]] = $opt[1];
		} else {
			$ret[$v] = $v;
		}
	}
	return $ret;
}

function getDnsesFirst($list)
{
	global $global_ip_array;
	$global_ip_array = null;

	foreach($list as $l) {
		if (!isset($global_ip_array[$l['servername']])) {
			$ip = gethostbyname($l['servername']);
			$global_ip_array[$l['servername']] = $ip;
		} 
	}
}

function strfrom($string, $needle)
{
	return substr($string, strpos($string, $needle) + strlen($needle));
}


function do_monitor_list($portmonlist, &$serverhistlist)
{

	global $global_ip_array;
	global $global_remoteserver;
	global $global_remoteport;

	$loopcount = 0;
	while (true) {
		$count = 0;
		$loopcount++;
		foreach($portmonlist as $s => &$serv) {
			foreach($serv as $k => &$data) {
				$nname = $k;
				if ($data[4] === 'done') {
					continue;
				}

				if (isset($global_ip_array[$data[0]])) {
					$ip = $global_ip_array[$data[0]];
				} else {
					$ip = "Silly screwup. Can't find dns for {$data[0]}\n";
				}


				if (!validate_ipaddress($ip)) {
					//print("failed Dns $ip for *{$data[0]}*\n");
					$obj['portstatus'] = 'off';
					//$obj['errornumber'] = 100;
					$obj['errorstring'] = "Dns failed for $ip";
					$obj['portnname'] = $nname;
					$data[2] = null;
					$data[4] = 'done';
					$serverhistlist[$s][$k] = $obj;
					socket_clear_error();
					continue;
				}

				$ret = socket_connect($data[2], $ip, $data[1]);

				$err = socket_last_error($data[2]);
				if ($ret === true || $err === 10056 || $err === 0) {
					fclose($data[2]);
					$data[2] = null;
					$data[4] = 'done';
					$obj['portstatus'] = 'on';
					$obj['errorstring'] = "SSS";
					//	$obj['errornumber'] = 0;
					$obj['portnname'] = $nname;
					$serverhistlist[$s][$k] = $obj;
					continue;
				}


				//print("$s: $k, $ret, $err $here\n");
				if ($err === 115 || $err === 114 || $err === 10035 || $err === 10037) {
					$data[4] = 'notdone';
					$count++;
					if ($loopcount < 14) {
						//print("Timeout not reached ... " . time() . " $startmon\n");
					} else {
						$obj['portstatus'] = 'off';
						//$obj['errornumber'] = $err;
						$obj['errorstring'] = "Timeout";
						$obj['portnname'] = $nname;
						$data[2] = null;
						$data[4] = 'done';
						$serverhistlist[$s][$k] = $obj;
						socket_clear_error();
					}
					continue;
				}


				//$obj['errornumber'] = $err;
				$strerror = socket_strerror($err);
				$vrttt = var_export($ret, true);
				if (!$err || !$strerror || $strerror === "Success") {
					$obj['errorstring'] = "Got NO error. Errno $err... ret was $vrttt";
					$obj['portstatus'] = 'on';
				} else {
					$obj['errorstring'] = "($err) $strerror (ret: $vrttt)";
					$obj['portstatus'] = 'off';
				}
				$obj['portnname'] = $nname;
				$data[2] = null;
				$data[4] = 'done';
				$serverhistlist[$s][$k] = $obj;
				socket_clear_error();
			}
		}

		if ($count === 0) {
			break;
		}
		sleep(1);
	}

	return $serverhistlist;
}


function set_global_debug()
{
	global $debug_var;

	$val = @ file_get_contents("commands.php");
	if ($val === "2") {
		$debug_var = 2;
	} else {
		$debug_var = 0;
	}
}
function dprint($mess)
{
	global $debug_var;

	if ($debug_var >= 2) {
		print($mess);
	}

}

function dprintr($var)
{
	global $debug_var;
	if ($debug_var >= 2) {
		print_r($var);
	}

}

function prepare_error_portmonlist($serverhistlist)
{
	foreach($serverhistlist as $k => $l) {
		$servername = strtilfirst($k, "___");
		foreach($l as $kk => $p) {
			if ($p['portstatus'] === 'on') {
				continue;
			}

			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			socket_set_nonblock($socket);
			$portnumber = strtilfirst($kk, "___");
			$portmonlist[$k][$kk] = array($servername, $portnumber, $socket);
		}
	}
	return $portmonlist;
}


function strtil($string, $needle)
{
	if (strrpos($string, $needle)) {
		return substr($string, 0, strrpos($string, $needle));
	} else {
		return $string;
	}
}

function strtilfirst($string, $needle)
{
	if (strpos($string, $needle)) {
		return substr($string, 0, strpos($string, $needle));
	} else {
		return $string;
	}
}


