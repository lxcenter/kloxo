<?php

class Uuser__Windows  extends lxDriverClass {

// Core


function createUser()
{
	global $gbl, $sgbl, $login, $ghtml;

	$mn = $this->main;
    $passwd = $mn->password;
	$parentname = $mn->getParentName();
	
	//lxfile_mkdir("c:/webroot/home/root/hell");
	print("creatinguser\n");
	$obj = new COM("WinNT://.");

	try {
		$user = new COM("WinNT://./{$this->main->nname}");
		dprint("description\n");
		dprint($user->Description);
		dprint("\n");
		if ($user->Description === uuser::getUserDescription($this->main->getParentName())) {
			return true;
		} else {
			throw new lxexception("user_exists", 'web_s_uuser_nname', $this->main->nname);
		}
	} catch (exception $e){
		$user = $obj->create("user", $this->main->nname);
		$user->HomeDirectory = convertTobackSlash("c:/webroot/$parentname/$parentname");
		//$user->Put("HomeDirDrive", "c:");
	}

	try {
		$user->setInfo();
	} catch (exception $e) {
		log_error("User Couldn't be created");
		throw new lxexception("user_exists", 'web_s_uuser_nname', $this->main->nname);
	}
		
	//$obj->AccountDisabled = true;
	$grp = new COM("WinNT://./Users");
	$grp->Add("WinNT://{$this->main->nname}");
	$user->setPassword($this->main->realpass);
	$user->Description = uuser::getUserDescription($this->main->getParentName());
	$user->setInfo();
}

static function getShellList()
{
	$newcont[] = '--Enabled--';
	$shelllist = add_disabled($newcont);
	return $shelllist;

}

function dbactionAdd()
{
	$this->createUser();
}

function dbactionUpdate($subaction)
{
	switch($subaction) 
	{
		case "enable":
		case "disable":
		case "toggle_status":
			{
				$this->SwitchStatus();
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


function SwitchStatus()
{
	$obj = new COM("WinNT://./{$this->main->nname}");
	if ($this->main->isOn('status')) {
		$obj->AccountDisabled = false;
	} else {
		$obj->AccountDisabled = true;
	}

	$obj->setInfo();


}

function createShowAlist(&$alist, $subaction = null)
{
	if (!$this->main->isDisabled('shell')) {
		$alist[] = 'a=updateform&sa=remote_desktop';
	}
}
function shellModify()
{
	$obj = new COM("WinNT://./Remote Desktop Users");
	if ($this->main->isDisabled('shell')) {
		$obj->Remove("WinNT://{$this->main->nname}");
	} else {
		$obj->Add("WinNT://{$this->main->nname}");
	}

	$obj->setInfo();
		
}

function changePassword()
{
	$obj = new COM("WinNT://./{$this->main->nname}");
	$obj->setPassword($this->main->realpass);
	$obj->setInfo();
}


function dbactionDelete()
{
	$obj = new COM("WinNT://.");
	try {
	$obj->Delete("user", $this->main->nname);
	} catch (exception $e) {
	}
}


}



