<?php 

class ftpuser__iis extends lxDriverClass {



function dbactionAdd()
{

	 $msftpsvc = new COM("IIS://LocalHost/MSFTPSVC");



	 
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
	 $newFtpServer= new COM("IIsFtpServer",  $this->main->nname);
	 $RootDir= $newFtpServer->a("IIsFtpVirtualDir","ROOT");
	 $VirtualDir=$RootDir->Delete("IIsFtpVirtualDir",$this->main->directory);
}

function dbactionUpdate()
{
	  switch($subaction) {

		case "password":
			{
				break;
			}

	}
}




}

