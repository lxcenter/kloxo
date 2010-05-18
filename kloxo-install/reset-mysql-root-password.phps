<?php 

if (isset($argv[1])) {
	$pass = $argv[1];
} else {
	$pass = "";
}


print("Stopping MySQL\n");
shell_exec("service mysqld stop");
print("Start MySQL with skip grant tables\n");
shell_exec("su mysql -c \"/usr/libexec/mysqld --skip-grant-tables\" >/dev/null 2>&1 &");
print("Using MySQL to flush privileges and reset password\n");
//system("mysqladmin flush-privileges password \"$pass\" >/dev/null 2>&1", $return);
sleep(10);
system("echo \"update user set password = Password('$pass') where User = 'root'\" | mysql -u root mysql ", $return);

while($return) {
	print("MySQL could not connect, will sleep and try again\n");
	sleep(10);
	system("echo \"update user set password = Password('$pass') where User = 'root'\" | mysql -u root mysql", $return);
}

print("Password reset succesfully. Now killing MySQL softly\n");
shell_exec("killall mysqld");
print("Sleeping 10 seconds\n");
shell_exec("sleep 10");
print("Restarting the actual MySQL service\n");
system("service mysqld restart");
print("Password successfully reset to \"$pass\"\n");

