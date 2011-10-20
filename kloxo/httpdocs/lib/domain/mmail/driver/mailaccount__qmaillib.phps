<?php     

// Core

class Mailaccount__Qmail  extends lxDriverClass {

static function Mailaccdisk_usage($accname)
{   
    global $gbl, $sgbl, $login, $ghtml;

	$name = explode('@', $accname);
	$mailpath = mmail__qmail::getDir($name[1]);
	//$mailpath = "/home/lxadmin/mail/domains/{$name[1]}";
	$path = "$mailpath/{$name[0]}";
	dprint("Path of the File is :$path\n");
	return lxfile_dirsize($path);

}



function syncUseradd()
{
	global $gbl, $sgbl, $ghtml; 

	global $global_shell_error;

	$password = $this->main->password;
	if (!$this->main->password) {
		$password = crypt('something');
	}

	$quser = explode("@", $this->main->nname);
	$domain = $quser[1];

	$res = lxuser_return(mmail__qmail::getUserGroup($domain), "__path_mail_root/bin/vadduser", $this->main->nname, '-e', $password);

	if ($res) {
		// --- Issue #702 - Error 'mailaccount_add_failed' when add email account
	//	if (!csb($this->main->nname, "postmaster")) {
			throw new lxException("mailaccount_add_failed", "nname", $global_shell_error);
	//	}
	}

	$this->syncQmail();
	$this->syncQuota();
}

function syncQmail()
{
	global $gbl, $sgbl, $ghtml; 

	$quser = explode("@", $this->main->nname);
	$domain = $quser[1];
	$mailpath = mmail__qmail::getDir($quser[1]);
	$sysuser = mmail__qmail::getUserGroup($domain);

//	$qmailfile = "$mailpath/". $quser[0] . "/.qmail";
	$qmailfile = "$mailpath/{$quser[0]}/.qmail";
	$maildropfile = "$mailpath/{$quser[0]}/.maildroprc";
	$user = $quser[0];

	$fdata = null;

	//$fdata .= $this->getAutoresConf();

	if ($this->main->isOn('autorespond_status')) {

		$mfile = "$mailpath/{$quser[0]}/autorespond/message";
		if (!lxfile_exists($mfile)) {
			lxuser_mkdir($sysuser, dirname($mfile));
			lxuser_put_contents($sysuser, $mfile, "Autoresponder");
		}
		if ($this->main->__var_autores_driver === 'qmail') {
			//$fdata .= "| autorespond 1000  5 autorespond/message autorespond 0 + &{$this->main->nname}\n";  
			$fdata .= "| autorespond 100  100 autorespond/message autorespond 0\n";  
		} else { 
			$fdata .= "| $sgbl->__path_php_path $sgbl->__path_program_root/script/autorespond.php {$this->main->nname}\n";
		}
	}

	$spamdir = "to $mailpath/$user/Maildir";
	if ($this->main->filter_spam_status === 'delete') {
		$spamdir = "EXITCODE=0\nexit";
	} else if ($this->main->filter_spam_status === 'spambox') {
		$spamdir = "to $mailpath/$user/Maildir/.Spam/";
	} else if ($this->main->filter_spam_status === 'mailbox') {
		$spamdir = "to $mailpath/$user/Maildir";
	}

	dprint("Spam status " . $this->main->__var_spam_status);
	$addextraspamheader = null;
	if ($this->main->isOn('__var_spam_status')) {
		if ($this->main->__var_spam_driver === 'spamassassin') {
			$maildropspam = "spamc -p 783 -u {$this->main->nname}";
			$addextraspamheader = " if ( /^X-Spam-status: Yes/ ) \n {\n $spamdir\n} \n";
		} else {
			$bogconf = "$mailpath/$user/.bogopref.cf";
			if (!lxfile_exists($bogconf)) {
				lxfile_touch($bogconf);
			}
			$maildropspam = "bogofilter -d /var/bogofilter/ -ep -c $bogconf";
			$addextraspamheader = "if ( /^X-Bogosity: Spam, tests=bogofilter/ ) \n{\n $spamdir\n }\n";
		}
		$fdata .= "| /var/qmail/bin/preline maildrop $maildropfile\n";
	} else{
		$fdata .= "|true\n";
		$fdata .= "./Maildir/\n";
	}


	/*
	if (!lxfile_exists("$spamdir/maildirfolder")) {
		exec("rmdir $spamdir >/dev/null 2>&1 ");
		lxshell_return("maildirmake", "-f", "Spam", "$mailpath/$user/Maildir");
		lxfile_unix_chown_rec($spamdir, mmail__qmail::getUserGroup($domain));
	}
*/

	lxuser_return(mmail__qmail::getUserGroup($domain), "maildirmake", "-f", "Spam", "$mailpath/$user/Maildir");
	$spamdirm = "$mailpath/$user/Maildir";
	//lxfile_unix_chown_rec($spamdirm, mmail__qmail::getUserGroup($domain));

	$maildropdata = "SHELL=/bin/sh\n\n";
	if ($this->main->isOn('__var_spam_status')) {
		$maildropdata .= "if ( \$SIZE < 96144 )\n{\nexception  {\nxfilter \"$maildropspam\" \n}\n}\n $addextraspamheader\n";
	}
	$maildropdata .= "to $mailpath/$user/Maildir/\n";


	if ($this->main->isOn('no_local_copy')) {
		dprint("Setting to null\n");
		$fdata = null;
	}

	if ($this->main->isOn('forward_status')) {
		foreach($this->main->forward_a as $value) {
			$value->nname = trim($value->nname);
			if (csb($value->nname, "|")) {
				$fdata .= "{$value->nname}\n";
			} else if(csa($value->nname, "@")) {
				$fdata .= "&{$value->nname}\n";
			} else {
				$fdata .= "&$value->nname@$domain\n";
			}
		}
	} 

	lxfile_rm($maildropfile);
	lfile_write_content($maildropfile, $maildropdata, mmail__qmail::getUserGroup($domain));

	lxfile_rm($qmailfile);
	lfile_write_content($qmailfile, $fdata, mmail__qmail::getUserGroup($domain));
	lxfile_unix_chmod($maildropfile, "700");
}

function syncUserdel()
{		
    global $gbl, $sgbl, $ghtml; 
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];

	$sys_cmd =  "__path_mail_root/bin/vdeluser" ;
	lxuser_return(mmail__qmail::getUserGroup($domain), $sys_cmd, $this->main->nname);
}

function createAutoResFile()
{
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];

	if (csb($mailpath, "domain") && cse($mailpath, "exist")) {
		dprint("Got a non-existent Domain $mailpath\n");
		return;
		//throw new lxException("domain_doesnt_exist", '');
	}

	$sys_path = "$mailpath/{$quser[0]}";
	$sys_fpath = "$mailpath/{$quser[0]}/autorespond/message";
	$sys_apath = "$mailpath/{$quser[0]}/autorespond";
	if (!lxfile_exists($sys_apath)) {
		lxuser_mkdir(mmail__qmail::getUserGroup($domain), $sys_apath);
		lxfile_unix_chown_rec($sys_apath, mmail__qmail::getUserGroup($domain));
	}
	$sysuser = mmail__qmail::getUserGroup($domain);
	lxuser_put_contents($sysuser, $sys_fpath, "From: {$this->main->nname}\nSubject: Response\n\n Message Received");
}

function syncrealpass()
{
	global $gbl, $sgbl, $ghtml; 
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];
	$sysuser = mmail__qmail::getUserGroup($domain);

	if (!$this->main->realpass) {
		$pass = "something";
	} else {
		$pass = $this->main->realpass;
	}

	lxuser_return($sysuser, "__path_mail_root/bin/vpasswd", $this->main->nname, $pass);
}

function syncQuota()
{
	global $gbl, $sgbl, $ghtml; 
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];
	$sysuser = mmail__qmail::getUserGroup($domain);

	if (is_unlimited($this->main->priv->maildisk_usage)) {
		$disksize =  "NOQUOTA";
	} else {
		$disksize = $this->main->priv->maildisk_usage * 1024 * 1024;
	} 

	$ret = lxuser_return($sysuser, "__path_mail_root/bin/vsetuserquota", $this->main->nname, $disksize);

}

function syncToggleUser()
{
	global $gbl, $sgbl, $login;
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];
	$sysuser = mmail__qmail::getUserGroup($domain);

	if($this->main->status === "on"){
		lxuser_return($sysuser, "__path_mail_root/bin/vmoduser",  "-x", $this->main->nname);
	} else {
		lxuser_return($sysuser, "__path_mail_root/bin/vmoduser", "-pwsi", $this->main->nname);
	}

}

function syncAutoRes()
{
	$quser = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($quser[1]);
	$domain = $quser[1];
	$sysuser = mmail__qmail::getUserGroup($domain);

	$autorespath = "$mailpath/{$quser[0]}/autorespond";
	if (!lxfile_exists($autorespath)) {
		lxuser_mkdir($sysuser, $autorespath);
	}
	$autoresfile = "$autorespath/message";
	$mess = "From: {$this->main->nname}\nSubject: {$this->main->__var_autores_subject}\n\n";
	$mess .= $this->main->__var_autores_message;
	lxuser_put_contents($sysuser, $autoresfile, $mess);
	
}


function dbactionAdd()
{
	$this->syncUseradd();
	$this->createAutoResFile();
}
function dbactionDelete()
{
	$this->syncUserdel();
}

function syncAutoRespond()
{
}

function clearSpamDb()
{
	list($user, $domain) = explode("@", $this->main->nname);
	$mailpath = mmail__qmail::getDir($domain);
	$prefpath = "$mailpath/$user/.bogopref.cf";
	$fname = fix_nname_to_be_variable($this->main->nname);
	lunlink("/var/bogofilter/$fname.wordlist.db");
	system("bogofilter -d /var/bogofilter/ --wordlist=R,user,$fname.wordlist.db,1 -n < /etc/my.cnf");
}

function trainAsSpam()
{
	global $global_dontlogshell;
	$global_dontlogshell = true;

	$listname = "{$this->main->subaction}_list";

	$name = fix_nname_to_be_variable($this->main->nname);

	if (csb($this->main->subaction, "train_as_system_")) {
		$optstring = null;
	} else {
		$optstring = "--wordlist=R,user,$name.wordlist.db,1";
	}

	$flag = "-n";
	if (cse($this->main->subaction, '_spam')) {
		$flag = "-s";
	} 

	foreach($this->main->$listname as $f) {
		$name = str_replace("_s_coma_s_", ",", $f);
		$name = str_replace("_s_colon_s_", ":", $name);
		$cmd = "bogofilter -d /var/bogofilter/ $optstring $flag < $name";
		do_exec_system("__system__", null, $cmd, $out, $err, $ret, null);
		//$out = null;
		//$ret = null;
		//exec($cmd, $out, $ret);
		//$out = implode(" ", $out);
		//log_shell("$ret: $out: $cmd");
	}

}


function dbactionUpdate($subaction)
{
	switch($subaction)
	{
		case "full_update":
			$this->syncQmail();
			$this->syncrealpass();
			$this->syncAutoRes();
			$this->syncToggleUser();
			$this->syncQuota();
			break;


		case "toggle_status":
			$this->syncToggleUser();
			break;

		case "train_as_system_spam":
		case "train_as_system_ham":
		case "train_as_spam":
		case "train_as_ham":
			$this->trainAsSpam();
			break;

		case "clear_spam_db":
			$this->clearSpamDb();
			break;

		case "password" : 
			$this->syncrealpass();
			break;

		case "limit":
			$this->syncQuota();
			break;

		case "autores":
			$this->syncAutoRes();
			break;

		case "add_forward_a":
		case "delete_forward_a":
		case "sync_forward" :
		case "sync_autorespond" : 
		case "change_spam" : 
		case "configuration" : 
		case "filter" : 
			$this->syncQmail();
			break;

	}
}

}

