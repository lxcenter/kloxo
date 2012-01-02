<?php 

class sshconfig__linux extends lxDriverClass {

function dbactionUpdate($subaction)
{
	if (if_demo()) { throw new lxException ("demo", $v); }

	if ($this->main->ssh_port && !($this->main->ssh_port > 0)) {
		throw new lxException('invalid_ssh_port', 'ssh_port', '');
	}
	dprint($this->main->ssh_port);

	$this->main->ssh_port = trim($this->main->ssh_port);
	if (!$this->main->ssh_port) {
		$port = "22";
	} else {
		$port = $this->main->ssh_port;
	}

	if (lxfile_exists("/etc/fedora-release")) {
		$str = lfile_get_contents("../file/template/sshd_config-fedora-2");
	} else {
		$str = lfile_get_contents("../file/template/sshd_config");
	}

	$str = str_replace("%ssh_port%", $port, $str);
	if ($this->main->isOn('without_password_flag')) {
		$wt = 'without-password';
	} else {
		$wt = 'yes';
	}

	if ($this->main->isOn('disable_password_flag')) {
		$pwa = 'no';
	} else {
		$pwa = 'yes';
	}

	$str = str_replace("%permit_root_login%", $wt , $str);
	$str = str_replace("%permit_password%", $pwa , $str);
	$ret = lfile_put_contents("/etc/ssh/sshd_config", $str);
	if (!$ret) {
		throw new lxException('could_not_write_config_file', '', '');
	}
	exec_with_all_closed("/etc/init.d/sshd restart");

}


}
