<?php

class Uuser__Linux  extends lxDriverClass {

// Core


function createUser()
{
	global $gbl, $sgbl, $login, $ghtml;
	global $global_shell_out, $global_shell_error, $global_shell_ret;

	return;
	$mn = $this->main;
    $passwd = $mn->password;
	lxfile_mkdir("__path_httpd_root/" .$mn->getParentName());
	$cmd = "useradd"; 
	$shell = fix_disabled($this->main->shell, $sgbl->__var_noaccess_shell);

	$ret = lxshell_return($cmd, "-m", '-c', uuser::getUserDescription($this->main->getParentName()), "-k","__path_program_root/file/user-skel/", "-d", "{$mn->getParentName()}/", "-s", $shell, "-p", $passwd, $mn->nname);

	if ($ret) {
		// Error... Do a lot of stuff;
		log_error($global_shell_out);
		throw new lxexception('user_create', 'web_s_uuser_s_nname', $this->main->nname);
	}

	/*
	if($mn->quota != "Unlimited"){
		lxshell_return("setquota", "-ur", "-F", "vfsv0" , $mn->nname, "0", $mn->quota,"200", "0" ,"0", "-a" , "ext3"); 
	}
*/
	return 1;
}


function createShowAlist(&$alist, $subaction = null)
{
}

static function getShellList()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$shell_file = "__path_real_etc_root/shells";
	$newcont = lfile_trim($shell_file);
	$newcont = array_remove($newcont, $sgbl->__var_noaccess_shell);
	$shells = add_disabled($newcont);
	return $shells;
}

function shellModify()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$shell = fix_disabled($this->main->shell, $sgbl->__var_noaccess_shell);
	//lxshell_return("usermod", "-s" , $shell,  $this->main->nname);
}

function changePassword()
{
	$pass = $this->main->password;
	// Need to use single quotes.
	lxshell_return("usermod", "-p" , $pass,  $this->main->nname);
}

function syncNewquota()
{
	if(!is_unlimited($this->main->priv->disk_usage)) {
		lxshell_return("setquota", "-ur","-F","vfsv0", $this->main->nname , "0", $this->main->priv->disk_usage, "200", "0", "0", "-a", "ext3");
	}
}


static function checkIfUserExists($name, $id)
{
	if (posix_getpwnam($name)) {
		$username = $name;

		$list = lfile("/etc/passwd");
		$comment = null;
		foreach($list as $l) {
			$l = trim($l);
			if (csb($l, "$username:")) {
				$useri = explode(":", $l);
				$comment = $useri[4];
				break;
			}
		}
		//dprint($comment . "Hello\n");
		if ($comment === uuser::getUserDescription($id)) {
			log_error("User {$name} Already Exists. But is of the same domain");
			return true;
		} else {
			log_error("User {$name} Already Exists. But is of NOT of the same domain");
			throw new lxexception("User_Exist", 'web_s_uuser_nname', $name);
		}

	}
	return false;
}

function dbactionAdd()
{
	dprintr($this);
	$ret = self::checkIfUserExists($this->main->nname, $this->main->getParentName());
	if ($ret) {
		return true;
	}
	$this->createUser();
	$this->shellModify();
	$this->toggleStatus();
}



function dbactionDelete()
{
	$pwd = posix_getpwnam($this->main->nname);
	if ($pwd['uid'] > 500) {
		lxshell_return("userdel", "-r", $this->main->nname);
		lxshell_return("groupdel", $this->main->nname);
	} else {
		dprint("User Id Less then 500 " . $pwd['uid'] . " Cannot Delete the User {$this->main->nname} <br> \n");
	}
}


function toggleStatus()
{
	if ($this->main->isOn('status')) {
		$ret =	lxshell_return("usermod", "-U", $this->main->nname);
	} else {
		$ret = lxshell_return("usermod", "-L", $this->main->nname);
	}
	if($ret)
		log_message($ret);
}

function dbactionUpdate($subaction)
{
	switch($subaction) 
	{
		case "full_update":
			{
				$this->changePassword();
				$this->shellModify();
				$this->toggleStatus();
				break;
			}
		case "disable":
		case "enable":
		case "toggle_status":
			{
				$this->toggleStatus();
				break;
			}
		case "shell_access":
			{
				$this->shellModify();
				break;
			}
		case "password":
			{
				$this->changePassword();
				break;
			}
	}
}

}
