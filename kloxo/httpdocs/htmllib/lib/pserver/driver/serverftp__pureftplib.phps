<?php 

class serverftp__pureftp extends lxDriverclass {



function dbactionAdd()
{
}

function dbactionUpdate($subaction)
{
	$this->updateXinConfig();
}

function updateXinConfig()
{
	if ($this->main->isOn('enable_anon_ftp')) { $anonval = "";
	} else { $anonval = "-E"; }

	$txt = lfile_get_contents("../file/template/pureftp");
	$txt = str_replace("%lowport%", $this->main->lowport, $txt);
	$txt = str_replace("%highport%", $this->main->highport, $txt);
	$txt = str_replace("%maxclient%", $this->main->maxclient, $txt);
	$txt = str_replace("%anonymous%", $anonval, $txt);
	lfile_put_contents("/etc/xinetd.d/pureftp", $txt);
	createRestartFile('xinetd');
}

}
