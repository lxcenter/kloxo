<?php 

class ftpuser__iisftp extends lxDriverClass {



function dbactionAdd()
{

	 $msftpsvc = new lxCOM("IIS://LocalHost/MSFTPSVC");



	 
	 //Creating FTP Virtual Directory

	 /*
     $newFtpServer= msftpsvc->(new COM("IIsFtpServer", $this->main->nname);
	 $RootDir=newFtpServer->(new COM("IIsFtpVirtualDir","ROOT");
	 $VirtualDir=RootDir->Creat("IIsFtpVirtualDir",$this->main->directory);

	 $VirtualDir->Path = array('d:\sfu\home\root\\' . $this->main->nname);
	 $VirtualDir->AccessFlags = array(513);
     $VirtualDir->SetInfo();
	 */
}
function dbactionDelete()
{
	$newFtpServer= new lxCOM("IIsFtpServer",  $this->main->nname);
	$RootDir= $newFtpServer->a("IIsFtpVirtualDir","ROOT");
	if ($RootDir) {
		$VirtualDir=$RootDir->Delete("IIsFtpVirtualDir",$this->main->directory);
	}
}

function dbactionUpdate($subaction)
{
	  switch($subaction) {

		case "password":
			break;

	}
}




}

