<?php 

include_once "htmllib/lib/include.php";

switchserver_main();

function switchserver_main()
{

	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	//sleep(60);
	initProgram("admin");

	if ($argc === 1) {
		print("Usage: $argv[0] --class= --name= --v-syncserver= \n");
		exit;
	}


	try {
		$opt = parse_opt($argv);

		$param = get_variable($opt);

		dprintr($param);

		$class = $opt['class'];
		$name = $opt['name'];

		if (lx_core_lock("$class-$name.switchserver")) {
			exit;
		}

		$object = new $class(null, 'localhost', $name);
		$object->get();
		if ($object->dbaction === 'add') {
			throw new lxException ("no_object", '', '');
			exit;
		}

		if (!$object->syncserver) {
			print("No_synserver...\n");
			throw new lxException ("no_syncserver", '', '');
			exit;
		}

		if ($param['syncserver'] === $object->syncserver) {
			print("No Change...\n");
			throw new lxException ("no_change", '', '');
			exit;
		}



		$driverapp_old = $gbl->getSyncClass('localhost', $object->syncserver, $object->get__table());
		$driverapp_new = $gbl->getSyncClass('localhost', $param['syncserver'], $object->get__table());

		if ($driverapp_new !== $driverapp_old) {
			//throw new lxException ("the_drivers_are_different_in_two_servers", '', '');
		}


		$object->doupdateSwitchserver($param);
	} catch (exception $e) {
		print($e->getMessage());
		/// hcak ahck... Chnage only the olddelete variable which is the mutex used for locking in the process of switch. The problem is we want to totally bail out if the switchserver fails. The corect way would be save after reverting the syncserve to the old value, but that's a bit risky. So we just use a hack to change only the olddeleteflag; Not a real hack.. This is the better way.

		$message = "{$e->getMessage()}";


		write_to_object($object, $message, $param['syncserver']);
		$fullmesage = "Switch of {$object->get__table()}:{$object->nname} to $object->syncserver failed due to {$e->getMessage()}";
		log_switch($fullmesage);

		mail($login->contactemail, "Switch Failed:", "$fullmesage\n");
		print("\n");
		exit;
	}
	mail($login->contactemail, "Switch Succeeded", "Switch Succeeded {$object->get__table()}:$object->nname to {$param['syncserver']}\n");

}



function write_to_object($object, $message, $syncserver)
{
	$sq = new Sqlite(null, $object->get__table());
	$sq->rawQuery("update {$object->get__table()} set olddeleteflag = 'Switch to $syncserver failed due to $message' where nname = '{$object->nname}'");
}
