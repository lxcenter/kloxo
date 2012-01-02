<?php 

class sshclient extends lxclass {

static $__desc = array("", "", "Ssh Client");
static $__desc_nname = array("", "", "Ssh Client");

static $__acdesc_show = array("", "",  "Ssh Terminal");


function get() {}
function write() {}


function showRawPrint($subaction = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$parent = $this->getParentO();




	$v = lfile_get_contents("htmllib/filecore/sshterm-applet.htm");


	if ($parent->is__table('pserver')) {
		$v = str_replace("%username%", "root", $v);
		$ip = getFQDNforServer($parent->nname);

		$sshport = db_get_value("sshconfig", $parent->nname, "ssh_port");
		if (!$sshport) { $sshport = "22"; }
		$v = str_replace("%host%", $ip, $v);
		$v = str_replace("%port%", $sshport, $v);
		$v = str_replace("%connectimmediately%", "true", $v);
	} else if ($parent->is__table('client')) {
		if ($parent->isDisabled('shell') || !$parent->shell) {
			$ghtml->print_information("pre", "updateform", "sshclient", "disabled");
			exit;
		}
		$sshport = db_get_value("sshconfig", $parent->websyncserver, "ssh_port");
		if (!$sshport) { $sshport = "22"; }
		$ghtml->print_information("pre", "updateform", "sshclient", "warning");
		$ip = getFQDNforServer("localhost");
		$v = str_replace("%username%", $parent->username, $v);
		$v = str_replace("%host%", $ip, $v);
		$v = str_replace("%port%", $sshport, $v);
		$v = str_replace("%connectimmediately%", "true", $v);
	} else {
		$v = str_replace("%username%", "root", $v);
		$ip = $parent->getOneIP();
		$sshport = db_get_value("sshconfig", $parent->syncserver, "ssh_port");

		if (!$ip) {
			throw new lxException("need_to_add_at_least_one_ip_to_the_vps_for_logging_in");
		}
		if (!$sshport) { $sshport = "22"; }
		$v = str_replace("%host%", $ip, $v);
		$v = str_replace("%port%", $sshport, $v);
		$v = str_replace("%connectimmediately%", "true", $v);
	}
	print($v);
}

static function initThisObjectRule($parent, $class) { return "sshclient"; }

static function initThisObject($parent, $class, $name = null) { return "sshclient"; }


}
