<?php 

include_once "htmllib/lib/include.php";

if (!os_isSelfSystemOrLxlabsUser()) {
	print("Must be Root \n");
	exit;
}


database_main();

function database_main()
{

	global $argc, $argv;
	global $gbl, $login, $ghtml; 

	initProgram('admin');

	if ($argv[1] == 'exec') {
		$db = new Sqlite(null, 'client');
		$res = $db->rawQuery($argv[2]);
		foreach($res as &$r) {
			foreach($r as $k => &$__r) {
				if (csb($k, "ser_")) {
					$__r = unserialize(base64_decode($__r));
				}
			}
		}
		print_r($res);
		exit;
	}

}
