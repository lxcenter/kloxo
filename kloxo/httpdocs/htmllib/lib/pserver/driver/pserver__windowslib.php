<?php 


class pserver__Windows extends lxDriverClass {

function poweroff()
{

	$obj = new COM("winmgmts:{(Shutdown)}//./root/cimv2");
	$list = $obj->ExecQuery("select * from Win32_OperatingSystem where Primary=true");

	foreach($list as $l) {
		$l->shutdown();
	}
}

function reboot()
{

	$obj = new COM("winmgmts:{(Shutdown)}//./root/cimv2");
	$list = $obj->ExecQuery("select * from Win32_OperatingSystem where Primary=true");

	foreach($list as $l) {
		$l->Reboot();
	}
}


function dbactionUpdate($subaction)
{
	switch($subaction)
	{
		case "reboot":
			{
				$this->reboot();
				break;
			}
		case "poweroff":
			{
				$this->poweroff();
				break;
			}
		case "password":
			{
				$this->main->syncPasswordCommon();
				break;
			}
	}
}

static function pserverInfo()
{

	try {
		$obj = new COM("Winmgmts://./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", '');
	}


	//$list = $obj->execQuery("select TotalVisibleMemorySize, FreePhysicalMemory, FreeVirtualMemory, TotalVisibleMemorySize from Win32_OperatingSystem");
	$list = $obj->execQuery("select TotalVisibleMemorySize, FreePhysicalMemory, TotalVirtualMemorySize, FreeVirtualMemory from Win32_OperatingSystem");


	
	foreach($list as $l) {
		$unit = 1024;


		$ret['priv_s_memory'] = $l->TotalVisibleMemorySize/$unit;
		$ret['used_s_memory'] = ($l->TotalVisibleMemorySize - $l->FreePhysicalMemory)/$unit;
		$ret['priv_s_virtual'] = $l->TotalVirtualMemorySize/$unit;
		$ret['used_s_virtual'] = ($l->TotalVirtualMemorySize - $l->FreeVirtualMemory)/$unit;

		foreach ($ret as &$vvv) {
			$vvv = round($vvv);
		}
	}

	

	$list = $obj->execQuery("select CurrentClockSpeed, L2CacheSize, Name from Win32_Processor");

	$processornum = 0;
	
	foreach($list as $v) {
		$cpu[$processornum]['used_s_cpumodel'] = $v->Name;
		$cpu[$processornum]['used_s_cpuspeed'] = round($v->CurrentClockSpeed/1000, 3) . " GHz";
		$cpu[$processornum]['used_s_cpucache'] = $v->L2CacheSize;
		$processornum++;
	}

	$ret['cpu'] = $cpu;
	return $ret;
}

function createShowAlist(&$alist, $subaction = null)
{
	$cnl = array('odbc');
	foreach($cnl as $cn) {
		$alist = $this->getListActions($alist, $cn);
	}



}


}
