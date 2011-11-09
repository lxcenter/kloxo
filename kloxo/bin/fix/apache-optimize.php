<?php

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$select = strtolower($list['select']);

$spare = (isset($list['spare'])) ? (int)$list['spare'] : null;

setApacheOptimize($select, $spare);

/* ****** BEGIN - setApacheOptimize ***** */

function setApacheOptimize($select, $spare = null)
{

	global $gbl, $sgbl, $login, $ghtml;

	log_cleanup("Apache optimize");

	$status = shell_exec("/etc/init.d/httpd status");

	if ($select === 'status') {
		log_cleanup("- Status: $status");
	}
	elseif ($select === 'optimize') {
		//--- stristr for Case-insensitive
		if (stristr($status, 'running') !== FALSE) {
			log_cleanup("- Service stop");
			$ret = lxshell_return("service", "httpd", "stop");
			if ($ret) { throw new lxexception('httpd_stop_failed', 'parent'); }
		}

		lxshell_return("sync; echo 3 > /proc/sys/vm/drop_caches");

		if (file_exists("/etc/httpd/conf.d/swtune.conf")) {
			//--- some vps include /etc/httpd/conf.d/swtune.conf
			log_cleanup("- Delete /etc/httpd/conf.d/swtune.conf if exist");
			lunlink("/etc/httpd/conf.d/swtune.conf");
		}

		$m = array();

		// check memory -- $2=total, $3=used, $4=free, $5=shared, $6=buffers, $7=cached

		$m['total']   = (int)shell_exec("free -m | grep Mem: | awk '{print $2}'");
		$m['spare']   = ($spare) ? $spare : ($m['total'] * 0.25);

		$m['apps']    = (int)shell_exec("free -m | grep buffers/cache: | awk '{print $3}'");

	/*
		$m['used']    = (int)shell_exec("free -m | grep Mem: | awk '{print $3}'");
		$m['free']    = (int)shell_exec("free -m | grep Mem: | awk '{print $4}'");
		$m['shared']  = (int)shell_exec("free -m | grep Mem: | awk '{print $5}'");
		$m['buffers'] = (int)shell_exec("free -m | grep Mem: | awk '{print $6}'");
		$m['cached']  = (int)shell_exec("free -m | grep Mem: | awk '{print $7}'");
	
		$m['avail']   = $m['free'] + $m['shared'] + $m['buffers'] + $m['cached'] - $m['spare'];
	*/

		$m['avail'] = $m['total'] - $m['spare'] - $m['apps'];

	//	$maxpar = (int)($m['avail'] / 25);
	//	$minpar = (int)($maxpar / 2);

		$maxpar_p = (int)($m['avail'] / 30) + 1;
		$minpar_p = (int)($maxpar_p / 2);

		$maxpar_w = (int)($m['avail'] / 35) + 1;
		$minpar_w = (int)($maxpar_w / 2);
		
		// because on apache 2.2.x no appear 'overflow' memory so
		// no need ServerLimit = MaxClients = $maxpar_p for prefork/itk
		// no need MaxClients = ThreadsPerChild = $maxpar_p for worker/event

		$s = <<<EOF
Timeout 150
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

<IfModule prefork.c>
	StartServers 2
	MinSpareServers {$minpar_p}
	MaxSpareServers {$maxpar_p}
	ServerLimit 256
	MaxClients 256
	MaxRequestsPerChild 4000
	MaxMemFree 2
</IfModule>

<IfModule itk.c>
	StartServers 2
	MinSpareServers {$minpar_p}
	MaxSpareServers {$maxpar_p}
	ServerLimit 256
	MaxClients 256
	MaxRequestsPerChild 4000
	MaxMemFree 2
</IfModule>

<IfModule worker.c>
	StartServers 2
	MaxClients 150
	MinSpareThreads {$minpar_w}
	MaxSpareThreads {$maxpar_w}
	ThreadsPerChild 25
	MaxRequestsPerChild 0
	ThreadStackSize 8196
	MaxMemFree 2
</IfModule>

<IfModule event.c>
	StartServers 2
	MaxClients 150
	MinSpareThreads {$minpar_w}
	MaxSpareThreads {$maxpar_w}
	ThreadsPerChild 25
	MaxRequestsPerChild 0
	ThreadStackSize 8196
	MaxMemFree 2
</IfModule>

Include /home/apache/conf/defaults/*.conf
Include /home/apache/conf/domains/*.conf
Include /home/apache/conf/redirects/*.conf
Include /home/apache/conf/webmails/*.conf
Include /home/apache/conf/wildcards/*.conf

###version0-6###
EOF;

		log_cleanup("- Calculate Apache threads limit (max/min -> $minpar_w/$maxpar_w) and server limits (max/min -> $minpar_p/$maxpar_p");

		log_cleanup("- Write to /etc/httpd/conf.d/~lxcenter.conf");

		// $s=implode("", file("/etc/httpd/conf.d/~lxcenter.conf"));
		$f = fopen("/etc/httpd/conf.d/~lxcenter.conf", "w");
		fwrite($f,$s,strlen($s));

		log_cleanup("- Service start");
		$ret = lxshell_return("service", "httpd", "start");
		if ($ret) { throw new lxexception('httpd_start_failed', 'parent'); }
	}
}

/* ****** END - setApacheOptimize ***** */
