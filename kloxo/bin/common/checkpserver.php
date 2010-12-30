<?php 

include_once "htmllib/lib/include.php"; 
initProgram('admin');

$list = $login->getList('pserver');

foreach($list as $l) {
	print("Checking $l->nname..");
	flush();
	try { 
		$ret = rl_exec_get(null, $l->nname, "findOperatingSystem");
	} catch (Exception $e) {
		print("$l->nname gave error {$e->getMessage()}\n");
		continue;
	}
	print("Success... Got Information : ");
	print("{$ret['version']}\n");
}
