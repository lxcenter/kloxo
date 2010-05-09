<?php 
// LxCenter:
// Migrate LxAdmin to Kloxo script
//
include_once "htmllib/lib/include.php"; 

if (!$sgbl->is_this_slave()) {
	$pass = slave_get_db_pass();
	$res = mysql_connect("localhost", "root", $pass);
	if (!$res) {
		print("Could not connect to MySQL as root. Please login via webinterface, go to admin home -> reset mysql password and reset the password and run this again.\n");
		exit;
	}
}


lxshell_return("service", "lxadmin", "stop");
lxshell_return("chkconfig", "lxadmin", "off");
lxfile_rm("/etc/init.d/lxadmin");
lxfile_mkdir("/usr/local/lxlabs/kloxo");
chdir("/usr/local/lxlabs/kloxo/");
lxfile_rm("kloxo-current.zip");
system("wget http://download.lxcenter.org/download/kloxo/production/kloxo/kloxo-current.zip");
system("unzip -oq kloxo-current.zip");
lxfile_rm_rec("/usr/local/lxlabs/kloxo/etc/");
lxfile_cp_rec("/usr/local/lxlabs/lxadmin/etc/", "/usr/local/lxlabs/kloxo/etc/");



if (!$sgbl->is_this_slave()) {
	system("service mysqld stop");
	if (!lxfile_exists("/var/lib/mysql/kloxo/")) {
		lxfile_cp_rec("/var/lib/mysql/lxadmin4_2", "/var/lib/mysql/kloxo");
	}
	lxfile_unix_chown_rec("/var/lib/mysql/kloxo", "mysql:mysql");
	system("service mysqld start");
	sleep(10);
	chdir("/usr/local/lxlabs/lxadmin/httpdocs/");
	passthru("lphp.exe ../bin/kloxo-db.php");
}
lxfile_cp("/usr/local/lxlabs/kloxo/httpdocs/htmllib/filecore/init.program", "/etc/init.d/kloxo");
lxfile_cp("/usr/local/lxlabs/lxadmin/etc/conf/lxadmin.pass", "/usr/local/lxlabs/kloxo/etc/conf/kloxo.pass");
lxfile_unix_chmod("/etc/init.d/kloxo", "0755");
lxshell_return("chkconfig", "kloxo", "on");
chdir("/usr/local/lxlabs/kloxo/httpdocs/");
passthru("lphp.exe ../bin/kloxo-migrate.php");

print("\n\n\nMigration to Kloxo Succesful. Please reboot the server for everything to take effect.\n\n");

