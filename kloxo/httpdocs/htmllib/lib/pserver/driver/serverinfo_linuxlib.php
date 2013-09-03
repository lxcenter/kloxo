<?php 
class serverinfo__linux extends lxDriverClass {

	static function sysinfo($isAdmin = false)
	{
		$cmd_uname = shell_exec('/bin/uname -a 2>&1');
		$cmd_uptime = shell_exec('/bin/cut -d. -f1 /proc/uptime');
		$cmd_loadavg = shell_exec('/bin/cat /proc/loadavg'); 
		$cmd_cpucount = shell_exec('/bin/cat /proc/cpuinfo | grep processor | wc -l 2>&1');
		$osinfo = findOperatingSystem(); // this is array..
		$uptime = array('days' => floor($cmd_uptime/60/60/24),
		'hours' => ($cmd_uptime/60/60%24),
		'mins' => ($cmd_uptime/60%60),
		'secs' => ($cmd_uptime%60));
		if(is_array($osinfo) && isset($osinfo['os']))
		{
			$osinfo['os'] = strtoupper($osinfo['os']);
		} else $osinfo = array('os' => 'unknown', 'version' => 'unknown', 'pointversion' => 'unknown');
		$cnf_loadavg = substr($cmd_loadavg, 0, strpos($cmd_loadavg, ' '));
		$cnf_cpucount = trim($cmd_cpucount);
		$str_cpucount = "({$cnf_cpucount} CPU's)";
		$str_loadavg = "System load: {$cnf_loadavg}";
		$str_osinfo = "Operating system: {$osinfo['os']}\nVersion: {$osinfo['version']}".((boolean) $isAdmin ? "Point version: {$osinfo['pointversion']}\n":null);
		$str_uptime = "System uptime: {$uptime['days']} days {$uptime['hours']} hours {$uptime['mins']} minutes and {$uptime['secs']} seconds.";
		if(empty($cmd_uname) || empty($cmd_uptime))
		{
			$str_return = 'Cannot read the system information';
		} else $str_return = "{$str_osinfo}{$str_loadavg} {$str_cpucount}\n{$str_uptime}".((boolean) $isAdmin ? "\nKernel: {$cmd_uname}":null);
		return $str_return;
	}
	
	static function cpuinfo()
	{
		$cmd_cpucount = shell_exec('/bin/cat /proc/cpuinfo | grep processor | wc -l 2>&1');
		$cmd_cpuinfo = shell_exec('/bin/cat /proc/cpuinfo | grep -e ^processor -e ^model -e ^cache -e ^core -e ^sibling -e ^physical 2>&1');
		if(empty($cmd_cpucount) || empty($cmd_cpuinfo))
		{
			$str_return = 'Cannot read the processor\'s CPU information';
		} else $str_return = "Total processors: {$cmd_cpucount}{$cmd_cpuinfo}";
		return $str_return;
	}
	
	static function meminfo()
	{
		$cmd_meminfo = shell_exec("/usr/bin/free -t -l 2>&1");
		if(empty($cmd_meminfo))
		{
			$str_return = 'Cannot read the memory information';
		} else $str_return = $cmd_meminfo;
		return $str_return;
	}
	
	static function diskinfo()
	{
		$cmd_diskinfo = shell_exec("/bin/df -a -l -T -l -k -h 2>&1");
		if(empty($cmd_diskinfo))
		{
			$str_return = 'Cannot read the disk\'s information';
		} else $str_return = $cmd_diskinfo;
		return $str_return;
	}
}
