<?php 
include_once "htmllib/lib/include.php"; 

setupsecondary_main();


function setupsecondary_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;
	$dbf = $sgbl->__var_dbf;
	$prgm = $sgbl->__var_program_name;

	$list = parse_opt($argv);

	if (!isset($list['primary-master'])) {
		print("need --primary-master=\n");
		exit;
	}
	if (!isset($list['sshport'])) {
		print("need --sshport=\n");
		exit;
	}

	$master = $list['primary-master'];
	$sshport = $list['sshport'];

	print("Taking backup of the current database anyway...\n");
	lxshell_php("../bin/common/mebackup.php");


	$slavepass = randomString(7);
	print("Setting up mysql to receive data from master\n");
	add_line_to_secondary_mycnf($master, $slavepass);
	$pass = slave_get_db_pass();
	mysql_connect("localhost", "root", $pass);
	mysql_query("stop slave");
	print("Getting initial data from the master\n");
	system("ssh -p $sshport $master \"(cd /usr/local/lxlabs/$prgm/httpdocs ; lphp.exe ../bin/common/setupprimarymaster.php --slavepass=$slavepass)\" | mysql -u root -p$pass $dbf");
	print("starting mysql data getting process\n");
	mysql_query("change master to master_host='$master', master_password='$slavepass'");
	mysql_query("start slave");
	lxfile_touch("../etc/secondary_master");
	lxfile_touch("../etc/running_secondary");
}

function check_if_skip($l)
{
	$vlist = array("server-id", "master-host", "master-user", "master-password");
	foreach($vlist as $v) {
		if (csb($l, $v)) {
			return true;
		}
	}
	return false;

}
function add_line_to_secondary_mycnf($master, $slavepass)
{
	global $gbl, $sgbl, $login, $ghtml; 
	if (!lxfile_exists("/etc/secondary_master.copy.my.cnf")) {
		lxfile_cp("/etc/my.cnf", "/etc/secondary_master.copy.my.cnf");
	}

	$list = lfile_trim("/etc/my.cnf");

	foreach($list as $k => $l) {
		if (check_if_skip($l)) {
			continue;
		}
		$ll[] = $l;
		if ($l == '[mysqld]') {
			$ll[] = "server-id=2";
			$ll[] = "master-host=$master";
			$ll[] = "master-user=lxlabsslave";
			$ll[] = "master-password=$slavepass";
		}
	}

	lfile_put_contents("/etc/my.cnf", implode("\n", $ll));
	system("service mysqld restart");

}
