<?php 

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	debug_for_backend();
	$program = $sgbl->__var_program_name;
	$login = new Client(null, null, 'upgrade');


	$opt = parse_opt($argv);

	print("Getting Version Info from the Server...\n");
	if ((isset($opt['till-version']) && $opt['till-version']) || lxfile_exists("__path_slave_db")) {
		$sgbl->slave = true;
		$upversion = findNextVersion($opt['till-version']);
		$type = 'slave';
	} else {
		$sgbl->slave = false;
		$upversion = findNextVersion();
		$type = 'master';
	}

	print("Connecting LxCenter download server...\nPlease wait....\n");

	if ($upversion) {
		do_upgrade($upversion);
		print("Upgrade Done.\nCleanup....\n");
		flush();
	} else {
		print("$program is the latest version\n");
	}


	if (is_running_secondary()) {
		print("Not running Update Cleanup, because this is running secondary \n");
		exit;
	}

	lxfile_cp("htmllib/filecore/php.ini", "/usr/local/lxlabs/ext/php/etc/php.ini");
	$res = pcntl_exec("/bin/sh", array("../bin/common/updatecleanup.sh", "--type=$type"));
	print("Ready.\n");

}



function updatecleanup()
{
	global $gbl, $sgbl, $login, $ghtml; 
	os_create_program_service();
	os_fix_lxlabs_permission();
	os_restart_program();
	updateApplicableToSlaveToo();
}

function update_all_slave()
{
	$db = new Sqlite(null, "pserver");

	$list = $db->getTable(array("nname"));

	foreach($list as $l) {
		if ($l['nname'] === 'localhost') {
			continue;
		}
		try {
			print("Upgrading Slave {$l['nname']}...\n");
			rl_exec_get(null, $l['nname'], 'remotetestfunc', null);
		} catch (exception $e) {
			print($e->getMessage());
			print("\n");
		}
	}

}




function findNextVersion($lastversion = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$maj = $sgbl->__ver_major;
	$thisversion = $sgbl->__ver_major_minor_release;

	$upgrade = null;
	$nlist = getVersionList($lastversion);
	dprintr($nlist);
	$k = 0;
	foreach($nlist as $l) {
		if (version_cmp($thisversion, $l) === -1) {
			$upgrade = $l;
			break;
		}
		$k++;
	}
	if (!$upgrade) {
		return 0;
	}

	print("Upgrading from $thisversion to $upgrade\n");
	return $upgrade;

}

function do_upgrade($upversion)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$maj = $sgbl->__ver_major;
	$program = $sgbl->__var_program_name;

	$programfile = "$program-" . $upversion . ".zip";

	lxfile_rm_rec("__path_program_htmlbase/help");
	lxfile_mkdir("help");
	lxfile_rm_rec("__path_program_htmlbase/htmllib/script");
	lxfile_rm_rec("__path_program_root/pscript");
	if (file_exists(".svn")) {
		print("SVN exists... Development system.. Not upgrading...\n");
		exit;
	}


	$saveddir = getcwd();
	lxfile_rm_rec("__path_program_htmlbase/download");
	lxfile_mkdir("download");
	chdir("download");
	print("Downloading $programfile ...\n");
	download_source("/$program/$programfile");
	print("Download Done....\n");
	$host = `hostname`;
	$host = trim($host);
	lxshell_unzip("__system__", "../..", $programfile);
	chdir($saveddir);
}

function fixZshEtc()
{
	global $global_dontlogshell;
	$global_dontlogshell = true;

	$dir = os_get_home_dir("root");

	if (lxfile_exists("$dir/.etc")) {
		lxfile_cp("htmllib/filecore/lxetc/commands.shell", "$dir/.etc/");
		return;
	}

	$ret = lxshell_return("rpm", "-q", "zsh", "vim-ehhanced");
	if ($ret) {
		system("yum -y install zsh vim-enhanced");
	}

	lxfile_cp_rec("htmllib/filecore/lxetc/", "$dir/.etc");
}

function move_clients_to_client()
{
	if (lxfile_exists("__path_program_home/client")) {
		return;
	}
	lxfile_mv_rec("__path_program_home/clients", "__path_program_home/client");
}


