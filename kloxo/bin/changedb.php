<?php 

if ($sgbl->is_this_slave()) {
	exit;
}

$dbadmin = new Dbadmin(null, $server, "mysql___localhost");
$dbadmin->get();

$pass = $dbadmin->get();

$rd = mysql_connect("localhost", "root", $pass);

if (!$rd) {
	system("lphp.exe ../bin/common/misc/reset-mysql-root-password.php newpass");
}

$rd = mysql_connect("localhost", "root", $pass);




