<?php 

class DiskUsage__Windows extends lxDriverClass {


/*
Set objFSO = CreateObject("Scripting.FileSystemObject")
Set colDrives = objFSO.Drives
For Each objDrive in colDrives
 Wscript.Echo "Drive letter: " & objDrive.DriveLetter
Next
*/

static function getDiskUsage()
{

	try {
		$obj = new COM("Winmgmts://./root/cimv2");
	} catch (exception $e) {
		throw new lxException("com_failed", 'disk');
	}


	$i = 0;
	$list = $obj->execQuery("select * from Win32_LogicalDisk");

	foreach($list as $l) {
		$result[$i]['nname'] = $l->Name;
		$result[$i]['kblock'] = round($l->Size/1000);
		$result[$i]['available'] = round($l->FreeSpace/1000);
		$result[$i]['used'] = $result[$i]['kblock'] - $result[$i]['available'];
		$result[$i]['pused'] = $result[$i]['used']/ $result[$i]['kblock'];
		$result[$i]['mountedon'] = $l->Name;
		$i++;
	 }
	 return $result;
}

}
