<?php
//    Kloxo, Hosting Control Panel
//
//    Copyright (C) 2000-2009	LxLabs
//    Copyright (C) 2009-2014	LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// This file is running when lowmem flag is disabled
//

function lxserver_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv, $argc;

	if ($argv[1] === 'slave') {
		$login = new Client(null, null, 'slave');
		$gbl->is_slave = true;
		$gbl->is_master = false;
		$rmt = unserialize(lfile_get_contents("__path_slave_db"));
		$login->password = $rmt->password;
		$argv[1] = "Running as Slave";
	} else if($argv[1] === 'master'){
		$login = new Client(null, null, 'admin');
		$gbl->is_master = true;
		$gbl->is_slave = false;
		$login->get();
		$argv[1] = "Running as Master";
	} else {
		print("Wrong arguments\n");
		exit;
	}
	$login->cttype = 'admin';

    // Set php script execution timer to unlimited
	set_time_limit(0);

    // Start internal socket for remote
	some_server();

}



function do_server_stuff()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (if_demo()) {
		return;
	}

	try {
		timed_execution();
		if ($sgbl->is_this_master()) {
			$schour = null;
            $scminute = null;

            $timefile = "../etc/conf/scavenge_time.conf";

            if (lxfile_exists($timefile)) {

                $readvalue = file_get_contents($timefile);
                $readvalue = explode(" ", $readvalue);
                $schour = $readvalue['0'];
                $scminute = $readvalue['1'];

            }

            log_log("cron_exec", "Initialize Scavenge Cronjob");
			if ($schour) {
				cron_exec($schour, $scminute, "exec_scavenge");
			} else {
				cron_exec("03", "35", "exec_scavenge");
			}
		}
	} catch (exception $e) {
		print("Caught Exception: ");
		print($e->getMessage());
		print("\n");
	}
}

function cron_exec($hour, $minute, $func)
{
	static $localvar;

    $time = mktime($hour, $minute);
    $now = time();

    $nowH = date("H");
    $nowM = date("i");

    if ($func === "exec_scavenge") {
        $niceNameFunc = "Scavenge";
    } else {
        $niceNameFunc = $func;
    }

    log_log("cron_exec", "Cron $niceNameFunc starts at ($hour:$minute)");
    log_log("cron_exec", "Time now is ($nowH:$nowM)");

	if (isset($localvar[$func]) && $localvar[$func]) {
		if ($now > $time + 2 * 60) {
            log_log("cron_exec", "Cron timing: $niceNameFunc finished, back to normal state.");
            $localvar[$func] = false;
		} else {
            log_log("cron_exec", "Cron timing: $niceNameFunc is running.");
            return ;
        }
	}

    if ($hour === $nowH && $minute === $nowM) {
        $localvar[$func] = true;
		log_log("cron_exec", "Starting $niceNameFunc");
		$func();
	}
}

function timed_exec($time, $func)
{
	$v = "global_v$func";
	global $$v;
	$ct = time();
    if (($ct - $$v) >= $time * 30 ) {
		$$v = $ct;
		$func();
	}
}

function exec_scavenge()
{
	global $gbl, $sgbl, $login, $ghtml;

	$olddir = getcwd();
	lchdir("__path_program_htmlbase");
	exec_with_all_closed("$sgbl->__path_php_path ../bin/scavenge.php");
	lchdir($olddir);
}

function checkRestart()
{
	
	if (if_demo()) {
		return;
	}
    log_log("cron_exec","Check service restarts...\n");
	$res = lscandir_without_dot("__path_program_etc/.restart");

	if ($res === false) {
		dprint(".restart does not exist... Creating\n");
		lxfile_mkdir("__path_program_etc/.restart");
		lxfile_generic_chown("__path_program_etc/.restart", "lxlabs");
	}

	foreach((array) $res as $r) {
		if (csb($r, "._restart_")) {
			$cmd = strfrom($r, "._restart_");
		}
		lunlink("__path_program_etc/.restart/$r");
		dprint("Restarting $cmd\n");

		switch($cmd) {
			case 'lxcollectquota':
                log_log("cron_exec","Start collecting Quota's\n");
                exec_justdb_collectquota();
				break;

			case 'openvz_tc':
                log_log("cron_exec","Start openvz_tc script\n");
                exec_openvz_tc();
				break;

			default:
                log_log("cron_exec","Restarting $cmd\n");
                exec_with_all_closed("/etc/init.d/$cmd restart");
				break;
		}
	}
}

function exec_openvz_tc()
{
	lxshell_background("sh", "__path_program_etc/openvz_tc.sh");
}

function special_bind_restart($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (WindowsOs()) {
		return;
	}
	if (myPcntl_fork() === 0) {
		socket_close($sgbl->__local_socket);
		exec("/etc/init.d/$cmd restart  </dev/null >/dev/null 2>&1 &");
		exit;
	} else {
		myPcntl_wait();
	}


}


function reload_lxserver_password()
{
	global $gbl, $sgbl, $login, $ghtml; 

	static $time;

	$stat = llstat("__path_admin_pass");
	$cur = $stat['mtime'];

	if ($cur > $time) {
		$rmt = lfile_get_contents("__path_admin_pass");
		$login->password = $rmt;
		$time = $cur;
	}

}

function root_main($d)
{
	reload_lxserver_password();

	try {
		$res = do_root_main($d);
		$res->exception = null;
	} catch (exception $e) {
		dprint("Caught Exception: " . $e->getMessage());
		$res = new Remote();
		$res->ret = -1;
		$res->exception = $e;
	}
	return $res;
}

function do_root_main($data)
{

	dprintr("Remote: ");
	dprintr($data);
	return  do_remote($data);
}


