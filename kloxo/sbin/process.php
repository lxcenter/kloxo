<?php 

chdir("c:/Program Files/lxlabs/kloxo/httpdocs");

include_once "lib/include.php";

process_main();

function process_main()
{
	global $gbl, $sgbl, $login, $ghtml; 


	ob_start();

	$exitchar = $sgbl->__var_exit_char;

	$res = new Remote();
	$res->exception = null;
	$res->ddata = "hello";
	$res->message = "hello";

	$in = fopen('php://stdin', 'r');
	$out = fopen('php://stdout', 'w');

	$total = null;
	while(true) {

		$buf = fgets($in, 30);

		//$buf = fgets($in);

		if($buf) {
			$total .= $buf;
		}


		if (strstr($total, $exitchar)) {
			fprint("GOt Full $total");
			$reply = process_server_input(null, $total);
			ob_end_clean();
			print("$reply\n$exitchar\n");
			flush();
			//fprint("here");
			ob_start();
			$total = null;
		}
	}
}


