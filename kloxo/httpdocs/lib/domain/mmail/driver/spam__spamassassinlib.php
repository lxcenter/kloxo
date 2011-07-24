<?php 

class Spam__Spamassassin extends lxDriverClass
{


	static function installMe()
	{

		$ret = lxshell_return("yum", "-y", "install", "spamassassin");
		if ($ret) {
			throw new lxexception('install_spamassassin_failed', 'parent');
		}
		lxshell_return("chkconfig", "spamassassin", "on");
		lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");
		createRestartFile("spamassassin");
	}

	static function uninstallMe()
	{
		lxshell_return("service", "spamassassin", "stop");
		lxshell_return("rpm", "-e", "--nodeps", "spamassassin");
	}

	function dbactionAdd()
	{
		//
		$this->syncSpamUserPref();
	}

	function dbactionDelete()
	{
		//
	}


	function syncSpamUserPref()
	{
		global $gbl, $sgbl, $ghtml;
		// The parent can be either a domain or a user. CHeck for the @ sign.
		if (csa($this->main->nname, "@")) {
			list($user, $domain) = explode("@", $this->main->nname);
		} else {
			$domain = $this->main->nname;
			$user = null;
		}

		// --- issue #578 - missing in version 6.1.6
		$mailpath = "/home/lxadmin/mail/spamassassin";

		if ($user) {
			$prefpath = "$mailpath/$domain/$user/user_prefs";
		} else {
			$prefpath = "$mailpath/$domain/user_prefs";
		}

		if (!lxfile_exists(dirname($prefpath))) {
			lxfile_mkdir(dirname($prefpath));
			lxfile_generic_chown(dirname($prefpath), "lxpopuser:lxpopgroup");
		}

		$fdata = null;
		$fdata .= "required_score  " . $this->main->spam_hit . "\n";
		$fdata .= "ok_locales   all\n";
		$fdata .= "rewrite_header Subject  {$this->main->subject_tag}\n";
		foreach ((array) $this->main->wlist_a as $wlist) $fdata .= "whitelist_from   " . $wlist->nname . "\n";
		$fdata .= "#***********************************\n";
		foreach ((array) $this->main->blist_a as $blist) $fdata .= "blocklist_from   " . $blist->nname . "\n";

		lxfile_rm($prefpath);
		lfile_write_content($prefpath, $fdata, "lxpopuser:lxpopgroup");
	}

	function dbactionUpdate($subaction)
	{

		switch ($subaction) {
			case "full_update":
				{
				$this->syncSpamUserPref();
				break;
				}
			case "update":
			case "add_wlist_a" :
			case "add_blist_a" :
			case "delete_blist_a" :
			case "delete_wlist_a" :
				{
				$this->syncSpamUserPref();
				break;
				}
		}


	}


}
