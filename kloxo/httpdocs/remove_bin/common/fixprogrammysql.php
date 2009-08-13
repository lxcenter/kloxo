<?php 

include_once "htmllib/lib/include.php"; 

if ($argv[1]) {
	$mysqlpass = $argv[1];
} else {
	$mysqlpass = slave_get_db_pass();
}

$db = $sgbl->__var_dbf;
$username = $sgbl->__var_program_name;
$program = $username;
$newpass = randomString(9);
$newpass = client::createDbPass($newpass);
mysql_connect("localhost", "root", $mysqlpass);
$cmd = "grant all on $db.* to $username@localhost identified by '$newpass'";
print("$cmd\n");
mysql_query($cmd);
lfile_put_contents("../etc/conf/$program.pass", $newpass);



