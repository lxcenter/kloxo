<?php 

ob_start();
include_once "htmllib/lib/include.php";

process_main();

function process_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	global $argv;

	$list = parse_opt($argv);

	$exitchar = $sgbl->__var_exit_char;

	$res = new Remote();
	$res->exception = null;
	$res->ddata = "hello";
	$res->message = "hello";
	$total = file_get_contents($list['temp-input-file']);
	@ lunlink($list['temp-input-file']);
	$string = explode("\n", $total);
	if (csb($total, "__file::")) {
		ob_end_clean();
		file_server(null, $total);
	} else {
		$reply = process_server_input($total);
		//fprint(unserialize(base64_decode($reply)));
		ob_end_clean();
		print("$reply\n$exitchar\n");
		flush();
	}
	exit;
}


