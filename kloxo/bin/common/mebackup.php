<?php 

include_once "htmllib/lib/displayinclude.php";

mebackup_main();

function mebackup_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);
	initProgram('admin');
	lxfile_mkdir("__path_program_home/selfbackup/self/__backup");

	$backup = $login->getObject('general')->selfbackupparam_b;
	$dbf = $sgbl->__var_dbf;
	$pass = trim(lfile_get_contents("__path_program_root/etc/conf/$progname.pass"));

	$vd = createTempDir("/tmp", "mebackup");
	$docf = "$vd/mebackup.dump";

	// Issue #671 - Fixed backup-restore issue
//	exec("exec mysqldump --add-drop-table -u $progname -p$pass $dbf > $docf");

	try {
		system("mysqldump --add-drop-table -u $progname -p$pass $dbf > $docf");
	}
	catch (Exception $e) {
		print('Error: ' . $e->getMessage());
	}

	$string = @ date('Y-M-d'). '-' . time(); 
	$bfile = "$sgbl->__path_program_home/selfbackup/self/__backup/$progname-scheduled-masterselfbackup-$string.zip";
	lxshell_zip($vd, $bfile, array("mebackup.dump"));
	lxfile_tmp_rm_rec($vd);

	if ($backup && $backup->isOn('selfbackupflag')) {
		try {
			lxbackup::upload_to_server($bfile, basename($bfile), $backup);
		} catch (Exception $e) {
			print("Sending warning to $login->contactemail ..\n");
			lx_mail(null, $login->contactemail, "$cprogname Self Database Backup Upload Failed on " . date('Y-M-d') . " at " . date('H') ." Hours" , "$cprogname Backup upload Failed due to {$e->getMessage()}\n");  
		}
	}
	$backup->rm_last_number = 20;
	$backup->nname = 'masterselfbackup';
	lxbackup::clear_extra_backups('selfbackup', 'self', $backup);
}


