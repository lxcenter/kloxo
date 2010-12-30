<?php 
include_once "htmllib/lib/include.php";

backup_main();

function backup_main()
{
	global $argc, $argv;
	global $gbl, $login, $ghtml; 

	$gbl->__restore_flag = true;

	if ($argc === 1) {
		print("Usage: $argv[0] --restore/--list --accounts='domain-<domain1.com>,client-<client1>,domain-<domain2.com>' <backup-file> [--switchserverlist='oldserver1:newserver1,oldserver2:newserver2']\n Use --accounts=all to restore everything.\n");
		exit;
	}


	initProgram("admin");
	$object = $login;

	$opt = parse_opt($argv);


	if (isset($opt['class']) && isset($opt['name'])) {
		$object = new $opt['class'](null, null, $opt['name']);
		$object->get();
		if ($object->dbaction === 'add') {
			log_error("{$opt['class']} doesnt exist");
			print("{$opt['class']} doesnt exist\n");
			exit;
		}
	}

	$class = $opt['class'];
	$name = $opt['name'];
	if (lx_core_lock("$class-$name.restore")) {
		print("Another Restore for the same class is happening..\n");
		exit;
	}

	$backup = $object->getObject('lxbackup');

	if (isset($opt['switchserverlist'])) {

		$sq = new Sqlite(null, "pserver");
		$serverlist = $sq->getTable();
		$serverlist = get_namelist_from_arraylist($serverlist);
		$server = $opt['switchserverlist'];

		$list = explode(",", $server);

		foreach($list as $l) {
			if (!$l) {
				continue;
			}
			$q = explode(":", $l);
			$rlist[$q[0]] = $q[1];
			if (!array_search_bool($q[1], $serverlist)) {
				print("The server {$q[1]} doesn't exist in the server system here\n");
				exit;
			}
		}
		$param['switchserverlist'] = $rlist;
		dprint("\n");
	} else {
		$param['switchserverlist'] = null;
	}


	/*
	if (!testAllServersWithMessage()) {
		$backup->restorestage = "Failed due to: could not connect to slave servers";
		clearLxbackup($backup);
		exit;
	}
*/


	$file = $opt['final'];
	//$param = get_variable($opt);

	if (isset($opt['list'])) {
		$gbl->__var_list_flag = true;
		$param['_accountselect'] = null;
	}  else if (isset($opt['restore'])) {
		$gbl->__var_list_flag = false;
		if (!isset($opt['accounts'])) {
			print("Restore option needs accounts that are to be restored. --accounts='domain-domain.com,client:clientname'... Use --list to find out all the domain/clients in the backup archive.\n");
			clearLxbackup($backup);
			exit;
		}
		$account = $opt['accounts'];
		//$account = str_replace(":", "_s_vv_p_", $account);
		$account = str_replace(":", "-", $account);
		$accountlist = explode(",", $account);
		$param['_accountselect'] = $accountlist;
	} else {
		print("Usage: $argv[0] <--list/--restore --accounts=> <filename>\n");
		clearLxbackup($backup);
		exit;
	}

	if (isset($opt['priority']) && $opt['priority'] === 'low') {
		sleep(20);
	}

	dprintr($param);

	//dprint($file);


	try {
		$backup->doUpdateRestore($file, $param);
		$backup->restorestage = 'done';
	} catch (exception $e) {
		log_error("Restore Failed. Reason: {$e->__full_message} \n");
		print("Restore Failed. Reason: {$e->__full_message} \n");
		$mess = $e->__full_message;
		mail($object->contactemail, "Restore Failed..", "Restore Failed for $object->nname with the Message $mess");
		$backup->restorestage = "Restore failed due to $mess";
	}

	clearLxbackup($backup);


}



