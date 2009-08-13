<?php 

include "htmllib/lib/include.php";

security_blanket_main();

function security_blanket_main()
{

	global $argv;
	//sleep(100);
	$rem = unserialize(lfile_get_contents($argv[1]));
	unlink($argv[1]);
	if (!$rem) { exit; }

	if (is_array($rem->func)) {
		dprintr($rem);
		$object = new $rem->func[0](null, null, 'hello');
	}
	call_user_func_array($rem->func, $rem->arglist);

	$sq = new Sqlite(null, $rem->table);
	$res = $sq->getRowsWhere("nname = '$rem->nname'", array($rem->flagvariable));

	if ($res[0][$rem->flagvariable] === 'doing') {
		$sq->rawQuery("update $rem->table set $rem->flagvariable = 'Program Got aborted in the midst. Please try again.' where nname = '$rem->nname'");
	}


}
