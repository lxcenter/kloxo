<?php 
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2010     LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

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

