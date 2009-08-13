<?php 

include "lib/include.php";


	$objWMIService=new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
	$objService = $objWMIService->Get("Win32_BaseService");
	$colListOfServices = $objWMIService->ExecQuery("Select * from Win32_Service Where Name = 'LxaMultiplexer' or Name = 'LxaServer'");
	foreach( $colListOfServices as $objService)
	{
		if ($argv[1] == 'stop') {
			$objService->StopService();
		}

		if ($argv[1] == 'start') {
			$objService->StartService();
		}
		if ($argv[1] == 'restart') {
			$objService->StopService();
			sleep(1);
			$objService->StartService();
		}

		print("\n Done \n");
	}



