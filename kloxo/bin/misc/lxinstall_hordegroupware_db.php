<?php
//
//
// Beta Version 29-jan-2011
//
//
// - It always resets the webmail-database/webmail-user passwords when running this script.
//   Just a little bit security :) 
//


print("Start fixing webmail\n\n");

print("###\nFixing Horde...\n");

include_once "htmllib/lib/include.php"; 

$pass = slave_get_db_pass();

debug_for_backend();

$user = "root";
$host = "localhost";
$database = "horde_groupware";
	
$link = mysql_connect($host, $user,$pass);
if (!$link) {
	print("Mysql root password error\n");
	exit;
}
print("Connected to MySQL\nTry to create the horde database...\n");

$result = mysql_query("CREATE DATABASE `horde_groupware`");

if (!$result)
{
 $skip = false;
 print("Database already exists. Check for tables\n");
 $result = mysql_select_db('horde_groupware');
 if (!$result) { print("Something went wrong, can not select horde database!\n"); }

 $query = "SELECT `user_uid` FROM `horde_users` WHERE `user_uid`='admin'"; 
 $result = mysql_query($query);
 if ($result) {
  print("Your Database looks fine. No need to fix\n");
  $skip = true;
  } else {
  $skip = false;
  print("Something went wrong, could not find admin user\n");
 

  print("Dropping database\n");
  $result = mysql_query("DROP DATABASE `horde_groupware`");
  if (!$result)
  {
   print("Could not drop horde database!\nScript Abort\n\n");
   exit;
  }

  print("Create database\n");
  $result = mysql_query("CREATE DATABASE `horde_groupware`");
  if (!$result) {
   print("There is REALY something very very wrong... Go to http://forum.lxcenter.org/ and report.\n\n");
   exit;
  }
 }

}

print("Generating password..\n");
$pass = randomString(8);
dprint("Generated Pass ".$pass."\n");
print("Add Password to configfile\n");
$content = lfile_get_contents("../file/horde.config.phps");
$content = str_replace("__lx_horde_pass", $pass, $content);
print("Remove system readonly attribute from configfile\n");
system("chattr -i /home/kloxo/httpd/webmail/horde/config/conf.php");

lfile_put_contents("/home/kloxo/httpd/webmail/horde/config/conf.php", $content);

print("Granting privileges\n");
$result = mysql_query("GRANT ALL ON horde_groupware.* TO horde_groupware@localhost IDENTIFIED BY '$pass'", $link);
mysql_query("flush privileges", $link);
if (!$result) { print("Could not grant privileges\nScript Abort.\n"); exit; }
print("Database connection is fixed\n");

if (!$skip) {
	
print("Fix database values in horde sql importfile\n");

$hordefile = "/home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql";
$content = lfile_get_contents($hordefile);
$content = str_replace("CREATE DATABASE horde;", "CREATE DATABASE IF NOT EXISTS horde_groupware;", $content);
lfile_put_contents($hordefile, $content);

$content = lfile_get_contents($hordefile);
$content = str_replace("USE horde;", "USE horde_groupware;", $content);
lfile_put_contents($hordefile, $content);

$content = lfile_get_contents($hordefile);
$content = str_replace(") ENGINE = InnoDB;", ");", $content);
lfile_put_contents($hordefile, $content);


	$pass = slave_get_db_pass();

	$pstring = null;
	if ($pass) { $pstring = "-p\"$pass\""; }
        print("Importing Horde database structure\n");
	system("mysql -u root $pstring < /home/kloxo/httpd/webmail/horde/scripts/sql/groupware.mysql.sql");

	$result = mysql_select_db('horde_groupware');
        if (!$result) { print("Something went wrong, can not select horde database!\n"); }
//
// TODO: Is this user realy needed!?!?
//
	$query = "INSERT INTO horde_users (user_uid, user_pass) VALUES ('admin','21232f297a57a5a743894a0e4a801fc3')" ;
	$result = mysql_query($query);
        if (!$result) { print("Something went wrong, could not add admin user into horde database\n"); }
}

print("###\nFixing RoundCube...\n");
print("Just fixing RoundCube's database connection!!\n");

$result = mysql_select_db('roundcubemail');
if (!$result) { print("Something went wrong, can not select RoundCube database!\n"); exit; }

print("Generating password..\n");
$pass = randomString(8);
dprint("Generated Pass ".$pass."\n");

print("Add Password to configfile\n");
$roundcubefileIN = "/usr/local/lxlabs/kloxo/file/webmail-chooser/db.inc.phps";
$roundcubefileOUT = "/home/kloxo/httpd/webmail/roundcube/config/db.inc.php";
$content = file_get_contents($roundcubefileIN);
$content = str_replace("mysql://roundcube:pass", "mysql://roundcube:" . $pass, $content);
print("Remove system readonly attribute from configfile\n");
system("chattr -i /home/kloxo/httpd/webmail/roundcube/config/db.inc.php");
lfile_put_contents($roundcubefileOUT, $content);

print("Granting privileges\n");
$result = mysql_query("GRANT ALL ON roundcubemail.* TO roundcube@localhost IDENTIFIED BY '$pass'", $link);
mysql_query("flush privileges", $link);
if (!$result) { print("Could not grant privileges\nScript Abort.\n"); exit; }
print("Database connection is fixed\n");

print("\n###\nDone\n");
exit;
