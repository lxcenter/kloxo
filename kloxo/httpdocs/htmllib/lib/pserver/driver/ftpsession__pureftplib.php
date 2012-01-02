<?php 

class ftpsession__pureftp extends lxDriverClass {

function dbactionDelete()
{
	lxshell_return("kill", $this->main->nname);
}

static function getFtpList($username = null)
{


	$list = process__linux::readProcessList();

	$ret = null;
	foreach($list as $l) {
		if (!csa($l['command'], "pure-ftp")) {
			continue;
		}

		dprintr($l);
		$r['pid'] = $l['nname'];
		$r['nname'] = $r['pid'];

		if ($username && $username !== $l['username']) {
			continue;
		}

		$r['account'] = $username;

		$r['state'] = $l['state'];
		$ret[] = $r;
	}
	return $ret;

}

}
