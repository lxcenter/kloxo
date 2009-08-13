<?php
class Servermail__Qmail  extends lxDriverClass {

function queue_lifetime()
{
	$queue_file="/var/qmail/control/queuelifetime";	
	$life_time=$this->main->queuelifetime;
	lfile_put_contents($queue_file, $life_time);
}

function concurrency_remote()
{
	$remote_file="/var/qmail/control/concurrencyremote";	
	$concurrency_data=$this->main->concurrencyremote;
	if (!lfile_exists("/var/qmail/control/concurrencyremote")) {
		lxfile_touch("/var/qmail/control/concurrencyremote");
	}
	lfile_put_contents($remote_file, $concurrency_data);
}

function save_myname()
{
	$rfile = "/var/qmail/control/me";
	lfile_put_contents($rfile, $this->main->myname);
	$rfile = "/var/qmail/control/defaulthost";
	lfile_put_contents($rfile, $this->main->myname);
	$rfile = "/var/qmail/control/defaultdomain";
	lfile_put_contents($rfile, $this->main->myname);
	$smtpgr = "{$this->main->myname} - Welcome to Qmail";
	$rfile = "/var/qmail/control/smtpgreeting";
	lfile_put_contents($rfile, $smtpgr);
}


function dbactionAdd()
{
	//
}

function dbactionDelete()
{
	////
}


function save_xinetd_qmail()
{

	if (if_demo()) { throw new lxException ("demo", $v); }

	$bcont = lfile_get_contents("../file/template/xinetd.smtp_lxa");
	$maps = null;
	if ($this->main->isOn("enable_maps")) { $maps = "/usr/bin/rblsmtpd -r bl.spamcop.net"; }

	$domkey = null;
	if ($this->main->isOn('domainkey_flag')) { $domkey = "DKSIGN=/var/qmail/control/domainkeys/%/private"; }
	$virus = null;
	if ($this->main->isOn('virus_scan_flag')) { $virus = "QMAILQUEUE=/var/qmail/bin/simscan"; }
	$spamdyke = null;

	if ($this->main->isOn('spamdyke_flag')) {
		$spamdyke = "/usr/bin/spamdyke -f /etc/spamdyke.conf";
		$ret = lxshell_return("rpm", "-q", "spamdyke");
		if ($ret) {
			throw new lxException('spamdyke_is_not_installed', 'spamdyke_flag', '');
		}
	}

	$servicename = "smtp";

	if ($this->main->smtp_instance > 0) {
		$instance = $this->main->smtp_instance;
	} else {
		$instance = "UNLIMITED";
	}

	if ($this->main->isOn('virus_scan_flag')) {
		$ret = lxshell_return("rpm", "-q", "simscan-toaster");
		if ($ret) {
			throw new lxException('simscan_is_not_installed_for_virus_scan', 'virus_scan_flag', '');
		}
		lxfile_cp("../file/clamav.init", "/etc/init.d/clamav");
		lxfile_unix_chmod("/etc/init.d/clamav", "755");
		lxshell_return("chkconfig", "clamav", "on");
		os_service_manage("clamav", "restart");
		os_service_manage("freshclam", "restart");
		lxshell_return("chkconfig", "freshclam", "on");
		lxfile_cp("../file/linux/simcontrol", "/var/qmail/control/");
		lxshell_return("/var/qmail/bin/simscanmk");
		lxshell_return("/var/qmail/bin/simscanmk", "-g");
	} else {
		lxshell_return("chkconfig", "clamav", "off");
		os_service_manage("clamav", "stop");
		os_service_manage("freshclam", "stop");
		lxshell_return("chkconfig", "freshclam", "off");
	}


	if ($this->main->max_size) {
		lfile_put_contents("/var/qmail/control/databytes", $this->main->max_size);
	}


	$bcont = str_replace("%maps%", $maps, $bcont);
	$bcont = str_replace("%domainkey%", $domkey, $bcont);
	$bcont = str_replace("%virusscan%", $virus, $bcont);
	$bcont = str_replace("%instance%", $instance, $bcont);
	$bcont = str_replace("%spamdyke%", $spamdyke, $bcont);

	$cont = str_replace("%servicename%", $servicename, $bcont);
	lfile_put_contents("/etc/xinetd.d/smtp_lxa", $cont);


	if ($this->main->additional_smtp_port > 0) {
		$servicename = "kloxo_smtp";

		$cont = str_replace("%servicename%", $servicename, $bcont);
		lfile_put_contents("/etc/xinetd.d/kloxo_smtp_lxa", $cont);
		remove_line("/etc/services", "kloxo_smtp");
		add_line("/etc/services", "kloxo_smtp {$this->main->additional_smtp_port}/tcp\n");
	} else {
		lxfile_rm("/etc/xinetd.d/kloxo_smtp_lxa");
		remove_line("/etc/services", "kloxo_smtp");
	}


	exec_with_all_closed("/etc/init.d/xinetd restart");
}

function dbactionUpdate($subaction)
{

	switch($subaction) {
		case "flushqueue":
			$this->flushqueue();
			break;

		case "update":
			$this->queue_lifetime();
			$this->save_myname();
			$this->save_xinetd_qmail();
			createRestartFile("qmail");

		case "spamdyke":
			$this->savespamdyke();
			break;


		case "add_mail_graylist_wlist_a":
			$this->writeWhitelist();
			break;

		case "delete_mail_graylist_wlist_a":
			$this->writeWhitelist();
			break;

	}
}

function writeWhitelist()
{
	$list = get_namelist_from_objectlist($this->main->mail_graylist_wlist_a);
	lfile_put_contents("/etc/spamdyke-exception.list", implode("\n", $list));
}

function savespamdyke()
{
	if ($this->main->isOn('graylist_flag')) {
		$graylist = "always-create-dir";
	} else {
		$graylist = "none";
	}
	lxfile_mkdir("/var/tmp/graylist.d/");
	lxfile_touch("/etc/spamdyke-exception.list");
	$bcont = lfile_get_contents("../file/template/spamdyke.conf");
	$bcont = str_replace("%lx_greet_delay%", "0", $bcont);
	$bcont = str_replace("%lx_graylist_level%", $graylist, $bcont);
	lfile_put_contents("/etc/spamdyke.conf", $bcont);
}

function deleteQueue()
{
	foreach($list as &$__l) {
		$__l = "-d$__l";
	}
	$arg = lx_merge_good(array("__path_program_root/bin/misc/qmHandle"), $list);
	call_user_func_array("lxshell_return", $arg);
}

function flushqueue()
{
	lxshell_return("pkill", "-14", "-f", "qmail-send");
}

}





