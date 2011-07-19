<?php

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

exec("echo 3 > /proc/sys/vm/drop_caches >>/dev/null");

$status = shell_exec("service httpd status");

//--- stristr for Case-insensitive
if (stristr($status, 'running') !== FALSE) {
	shell_exec("service httpd stop");
}

$m = array();

// check memory -- $2=total, $3=used, $4=free, $5=shared, $6=buffers, $7=cached


$m['total']   = (int)shell_exec("free -m | grep Mem: | awk '{print $2}'");
$m['spare']   = ($m['total'] * 0.25);

$m['apps']    = (int)shell_exec("free -m | grep buffers/cache: | awk '{print $3}'");

/*
$m['used']    = (int)shell_exec("free -m | grep Mem: | awk '{print $3}'");
$m['free']    = (int)shell_exec("free -m | grep Mem: | awk '{print $4}'");
$m['shared']  = (int)shell_exec("free -m | grep Mem: | awk '{print $5}'");
$m['buffers'] = (int)shell_exec("free -m | grep Mem: | awk '{print $6}'");
$m['cached']  = (int)shell_exec("free -m | grep Mem: | awk '{print $7}'");
*/

// $m['avail']   = $m['free'] + $m['shared'] + $m['buffers'] + $m['cached'] - $m['spare'];

$m['avail'] = $m['total'] - $m['spare'] - $m['apps'];

$maxpar = (int)($m['avail'] / 20);
$minpar = (int)($maxpar / 2);

exec("service httpd start");

$s = <<<EOF
Timeout 150
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

<IfModule prefork.c>
	StartServers 2
	MinSpareServers {$minpar}
	MaxSpareServers {$maxpar}
	ServerLimit {$maxpar}
	MaxClients {$maxpar}
	MaxRequestsPerChild 4000
	MaxMemFree 2
</IfModule>

<IfModule itk.c>
	StartServers 2
	MinSpareServers {$minpar}
	MaxSpareServers {$maxpar}
	ServerLimit {$maxpar}
	MaxClients {$maxpar}
	MaxRequestsPerChild 4000
	MaxMemFree 2
</IfModule>

<IfModule worker.c>
	StartServers 2
	MaxClients 150
	MinSpareThreads {$minpar}
	MaxSpareThreads {$maxpar}
	ThreadsPerChild 25
	MaxRequestsPerChild 0
	ThreadStackSize 8196
	MaxMemFree 2
</IfModule>

<IfModule event.c>
	StartServers 2
	MaxClients 150
	MinSpareThreads {$minpar}
	MaxSpareThreads {$maxpar}
	ThreadsPerChild 25
	MaxRequestsPerChild 0
	ThreadStackSize 8196
	MaxMemFree 2
</IfModule>

Include /home/httpd/conf/defaults/*.conf
EOF;

// $s=implode("", file("/etc/httpd/conf.d/~lxcenter.conf"));
$f = fopen("/etc/httpd/conf.d/~lxcenter.conf", "w");
fwrite($f,$s,strlen($s));
