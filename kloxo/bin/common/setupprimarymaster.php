<?php 

include_once "htmllib/lib/include.php"; 

error_reporting(0);
pmaster_main();

function pmaster_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $argv;
	ob_start();
	$pass = slave_get_db_pass();
	$tfile = ltempnam("/tmp", "mastertmp");
	$dbf = $sgbl->__var_dbf;
	$list = parse_opt($argv);
	$slavepass = $list['slavepass'];
	add_line_to_master_mycnf();
	mysql_connect("localhost", "root", $pass);
	mysql_query("grant replication slave on *.* to lxlabsslave@'%' identified by '$slavepass'");
	system("mysqldump --master-data -u root '-p$pass' $dbf > $tfile");
	ob_clean();
	readfile($tfile);
	ob_start();
	unlink($tfile);
	ob_clean();
}


function add_line_to_master_mycnf()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$dbf = $sgbl->__var_dbf;
	if (!lxfile_exists("/etc/primary_master.copy.my.cnf")) {
		lxfile_cp("/etc/my.cnf", "/etc/primary_master.copy.my.cnf");
	}

	$v = lfile_get_contents("/etc/my.cnf");
	if (csa($v, "binlog-do-db")) {
		print("Line already exists in /etc/my.cnf\n");
		return;
	}

	$list = lfile_trim("/etc/my.cnf");

	foreach($list as $k => $l) {
		$ll[] = $l;
		if ($l == '[mysqld]') {
			$ll[] = "log-bin=mysql-bin";
			$ll[] = "binlog-do-db=$dbf";
			$ll[] = "server-id=1";
		}
	}

	lfile_put_contents("/etc/my.cnf", implode("\n", $ll));
	system("service mysqld restart");

}
