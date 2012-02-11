<?php 
include_once "htmllib/lib/include.php";

if ($sgbl->isHyperVm()) {
	exit(10);
}


if (lxfile_exists("/proc/user_beancounters")) {
	$list = lfile("/proc/user_beancounters");
	foreach($list as $l) {
		$l = trimSpaces($l);
		if (!csb($l, "privvmpages")) {
			continue;
		}

		$d = explode(" ", $l);

		$mem = $d[3]/ 256;
	}
	exit(11);
} else if (lxfile_exists("/proc/xen")) {
	exit (11);
} else {
	$mem = pserver__linux::getTotalMemory();
	$mem += 200;
}

if ($mem < 180) {
	exit(15);
}

exit(11);
