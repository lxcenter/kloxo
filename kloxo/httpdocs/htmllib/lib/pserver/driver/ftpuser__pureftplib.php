<?php 

class ftpuser__pureftp extends lxDriverClass {

function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$dir = $this->main->__var_full_directory;
	$dir = expand_real_root($dir);
	$pass = $this->main->realpass;
	if (!$pass) { $pass = randomString(8); }
	lxshell_input("$pass\n$pass\n", "pure-pw", "useradd",  $this->main->nname, "-u", $this->main->__var_username, "-d",  $dir, "-m");
	if (!lxfile_exists($dir)) {
		lxfile_mkdir($dir);
		lxfile_unix_chown($dir, $this->main->__var_username);
	}

	$this->setQuota();

	// If the user is added is fully formed, this makes sure that all his properties are synced.
	$this->toggleStatus();

}

function dbactionDelete()
{

//	$command =  "pure-pw userdel " . $this->main->nname . " -f /etc/pureftpd.passwd -m";

//	dprint($command); 
//	shell_exec($command);

	lxshell_return("pure-pw", "userdel", $this->main->nname, "-m" ) ;
}

function toggleStatus()
{
	if ($this->main->isOn('status')) {
		lxshell_return("pure-pw", "usermod", $this->main->nname, "-z", "0000-2359", "-m");
	} else {
		lxshell_return("pure-pw", "usermod", $this->main->nname, "-z", "0000-0000", "-m");
	}
}

function setQuota()
{
	if ($this->main->ftp_disk_usage > 0) {
		lxshell_return("pure-pw", "usermod", $this->main->nname, "-N", $this->main->ftp_disk_usage, "-m");
	} else {
		// This is because the shell_return cannot send '' to the program.
		$cmd = "pure-pw usermod {$this->main->nname} -N '' -m";
		log_log("shell_exec", $cmd);
		system($cmd);
		//lxshell_return("pure-pw", "usermod", $this->main->nname, "-N", "", "-m");
	}
}

function dbactionUpdate($subaction)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$dir = $this->main->__var_full_directory;
	$dir = expand_real_root($dir);
	switch($subaction) 
	{
		case "full_update":
			$pass = $this->main->realpass;
			lxshell_input("$pass\n$pass\n", "pure-pw", "passwd", $this->main->nname, "-m");
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-d", $dir, "-m");
			$this->toggleStatus();
			$this->setQuota();
			break;

		case "password":
			$pass = $this->main->realpass;
			lxshell_input("$pass\n$pass\n", "pure-pw", "passwd", $this->main->nname, "-m");
			break;

		case "toggle_status":
			$this->toggleStatus();
			break;

		case "edit":
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-d", $dir, "-m");
			$this->setQuota();
			break;

		case "changeowner":
			lxshell_return("pure-pw", "usermod", $this->main->nname, "-u", $this->main->__var_username, "-d", $dir, "-m");
			break;
	}
}

}


