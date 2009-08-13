<?php 

include_once "lib/pserver/driver/service__linuxlib.php";

class Service__Debian extends lxDriverClass {


	/// We need to properly port this system to debian. I tried using the chkconfig directly on debian, but it seems the individual scripts themselves have to support chkconfig if it has to work, and thus chkconfig fails to run. Now the only way is to use update-rc.d program on debain.

function dbactionAdd()
{
	lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
}


function dbactionUpdate($subaction)
{
	switch($subaction) 
	{

		case "toggle_status":
			{

				if ($this->main->isOn('status')) {
					lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
				} else {
					lxshell_return("update-rc.d", "-f", $this->main->servicename, 'remove');
				}

				break;
			}

		case "toggle_state":
			{
				if ($this->main->isOn('state')) {
					lxshell_return("__path_real_etc_root/init.d/{$this->main->servicename}", "start");
				} else {
					lxshell_return("__path_real_etc_root/init.d/{$this->main->servicename}", "stop");
				}
				break;
			}
	}

}


static function getServiceList()
{
	return Service__Linux::getServiceList();
}


static function checkService($name)
{
	return Service__Linux::checkService($name);
}

}
