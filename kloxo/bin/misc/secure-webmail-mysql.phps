<?php
$pass = makeRandomPass();
$hordePass = makeRandomPass();

// SET THESE VARIABLES:

$resetRoundcube = true; // reset RC password
$resetHorde = true; // reser Horde password

function resetRCPassInConfigFile($password) {
	file_put_contents("/home/kloxo/httpd/webmail/roundcube/config/db.inc.php", "");
	$newHorde = file_get_contents("/usr/local/lxlabs/kloxo/file/webmail-chooser/db.inc.phps");
	return file_put_contents("/home/kloxo/httpd/webmail/roundcube/config/db.inc.php", str_replace("mysql://roundcube:pass", "mysql://roundcube:" . $password, $newHorde));
}

function resetHordePassInConfigFile($password) {
	file_put_contents("/home/kloxo/httpd/webmail/horde/config/conf.php", "");
	$newHorde = file_get_contents("/usr/local/lxlabs/kloxo/file/horde.config.phps");
	return file_put_contents("/home/kloxo/httpd/webmail/horde/config/conf.php", str_replace("__lx_horde_pass", $password, $newHorde));
}

function make_seed() {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
}

function makeRandomPass() {
        $chars = array('A','a','B','b','C','c','D','d','E','e','F','f','G','g','H','h','J','j','K','k','L','l','M','m','N','n','O','o','P','p','Q','q','R','r','S','s','T','t','U','u','V','v','W','w','X','x','Y','y','Z','z','0','1','2','3','4','5','6','7','8','9','!','@','#','$','%','^','&','*','(',')','-','_','+');
        srand(make_seed());
        $randStr = "";
        for ($i = 0; $i < 10; $i++) {
                $randNum = ceil(rand()%73);
                $randStr .= $chars[$randNum];
        }
        return $randStr;
}

if ($resetRoundcube === true) {
	print("Resetting the password for the roundcube user in MySQL.\n");
}
if ($resetHorde === true) {
	print("Resetting the password for the horde_groupware user in MySQL.\n");
}
if ($resetRoundcube === true || $resetHorde === true) {
	print("Stopping MySQL daemon\n");
	shell_exec("/sbin/service mysqld stop");
	print("Starting MySQL daemon with \"skip-grant-tables\"\n");
	shell_exec("su mysql -c \"/usr/libexec/mysqld --skip-grant-tables\" >/dev/null 2>&1 &");
	sleep(10);
	if ($resetRoundcube === true && resetRCPassInConfigFile($pass) !== FALSE) {
		system("echo \"update user set Password = Password('".$pass."') where User = 'roundcube'\" | mysql -u root mysql ", $returnRoundcube);
		while($returnRoundcube) {
				print("Could not connect to MySQL. Will try again shortly.\n");
				sleep(10);
				system("echo \"update user set Password = Password('".$pass."') where User = 'roundcube'\" | mysql -u root mysql ", $returnRoundcube);
		}
		print("Roundcube's MySQL account is now secured.\n");
	}
	if ($resetHorde === true && resetHordePassInConfigFile($hordePass) !== FALSE) {
		system("echo \"update user set Password = Password('".$hordePass."') where User = 'horde_groupware'\" | mysql -u root mysql ", $returnHorde);
		while($returnHorde) {
				print("Could not connect to MySQL. Will try again shortly.\n");
				sleep(10);
				system("echo \"update user set Password = Password('".$hordePass."') where User = 'horde_groupware'\" | mysql -u root mysql ", $returnHorde);
		}
		print("Horde's MySQL account is now secured.\n");
	}
	print("Resetting MySQL\n");
	shell_exec("killall mysqld");
	shell_exec("sleep 10");
	system("/sbin/service mysqld restart");
	system("/bin/touch /usr/local/lxlabs/kloxo/file/webmailReset");
	print("Password successfully reset.\n");
} else {
	print ("No user was selected to have their password reset.\n");
}
?>
