<?php
//    Kloxo, Hosting Control Panel
//
//    Copyright (C) 2000-2009	LxLabs
//    Copyright (C) 2009-2010	LxCenter
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
//

// TODO...

// include_once "../install_common.php";

//========== lxins.php portion =================

function lxins_main()
{
	global $argv, $downloadserver;
	$opt = parse_opt($argv);
	$dir_name=dirname(__FILE__);
	$installtype = $opt['install-type'];
	$dbroot = isset($opt['db-rootuser'])? $opt['db-rootuser']: "root";
	$dbpass = isset($opt['db-rootpassword'])? $opt['db-rootpassword']: "";
	$osversion = find_os_version();
	$arch = `arch`;
	$arch = trim($arch);
	
	//--- create temporal flags for install
	system("mkdir -p /var/cache/kloxo/");
	system("echo 1 > /var/cache/kloxo/kloxo-install-firsttime.flg");

	if (!char_search_beg($osversion, "centos") && !char_search_beg($osversion, "rhel")) {
		print("Kloxo is only supported on CentOS 5 and RHEL 5\n");
		exit;
	}

	if(file_exists("/usr/local/lxlabs/kloxo")) {
		// Ask Reinstall
		if (get_yes_no("Kloxo seems already installed do you wish to continue?") == 'n') {
			print("Installation Aborted.\n");
			exit;
		}
	} else {
		// Ask License
		if (get_yes_no("Kloxo is using AGPL-V3.0 License, do you agree with the terms?") == 'n') {
			print("You did not agree to the AGPL-V3.0 license terms.\n");
			print("Installation aborted.\n\n");
			exit;
		} else {
			print("Installing Kloxo = YES\n\n");
		}
	}
	// Ask for InstallApp
	print("InstallApp: PHP Applications like PHPBB, WordPress, Joomla etc\n");
	print("When you choose Yes, be aware of downloading about 350Mb of data!\n");
	if(get_yes_no("Do you want to install the InstallAPP sotfware?") == 'n') {
		print("Installing InstallApp = NO\n");
		print("You can install it later with /script/installapp-update\n\n");
		$installappinst = false;
		//--- temporal flag for no install InstallApp
		system("echo 1 > /var/cache/kloxo/kloxo-install-disableinstallapp.flg");
	} else {
		print("Installing InstallApp = YES\n\n");
		$installappinst = true;
	}

	print("Adding System users and groups (nouser, nogroup and lxlabs, lxlabs)\n");
	system("groupadd nogroup");
	system("useradd nouser -g nogroup -s '/sbin/nologin'");
	system("groupadd lxlabs");
	system("useradd lxlabs -g lxlabs -s '/sbin/nologin'");

	print("Installing LxCenter yum repository for updates\n");
	install_yum_repo($osversion);

	$packages = array("sendmail", "sendmail-cf", "sendmail-doc", "sendmail-devel", "exim", "vsftpd", "postfix", "vpopmail", "qmail", "lxphp", "lxzend", "pure-ftpd", "imap");
	$list = implode(" ", $packages);
	print("Removing packages $list...\n");
	foreach ($packages as $package) {
		exec("rpm -e --nodeps $package > /dev/null 2>&1");
	}

	$packages = array("php-mbstring", "php-mysql", "which", "gcc-c++", "php-imap", "php-pear", "php-devel", "lxlighttpd", "httpd", "mod_ssl", "zip", "unzip", "lxphp", "lxzend", "mysql", "mysql-server", "curl","autoconf","automake","libtool", "bogofilter", "gcc", "cpp", "openssl", "pure-ftpd", "yum-protectbase");
	$list = implode(" ", $packages);
	while (true) {
		print("Installing packages $list...\n");
		system("PATH=\$PATH:/usr/sbin yum -y install $list", $return_value);
		if (file_exists("/usr/local/lxlabs/ext/php/php")) {
			break;
		} else {
			print("Yum Gave Error... Trying Again...\n");
		}
	}
	print("Prepare installation directory\n");
	
	system("mkdir -p /usr/local/lxlabs/kloxo");

	if (file_exists("../kloxo-current.zip")) {
		//--- that mean install with local copy
		@ unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
		print("Local copying Kloxo release\n");
		passthru("mkdir -p /var/cache/kloxo");
		passthru("cp -rf ../kloxo-current.zip /usr/local/lxlabs/kloxo");

		// the first step - remove 
		passthru("rm -f /var/cache/kloxo/kloxo-thirdparty*.zip");
		passthru("rm -f /var/cache/kloxo/lxawstats*.tar.gz");
		passthru("rm -f /var/cache/kloxo/lxwebmail*.tar.gz");
		passthru("rm -f /var/cache/kloxo/kloxophpsixfour*.tar.gz");
		passthru("rm -f /var/cache/kloxo/kloxophp*.tar.gz");
		passthru("rm -f /var/cache/kloxo/*-version");
		// the second step - copy from packer making if exist
		passthru("cp -rf ../kloxo-thirdparty*.zip /var/cache/kloxo");
		passthru("cp -rf ../lxawstats*.tar.gz /var/cache/kloxo");
		passthru("cp -rf ../lxwebmail*.tar.gz /var/cache/kloxo");
		passthru("cp -rf ../kloxo-thirdparty-version /var/cache/kloxo");
		passthru("cp -rf ../lxawstats-version /var/cache/kloxo");
		passthru("cp -rf ../lxwebmail-version /var/cache/kloxo"); 
//		if ( os_is_arch_sixfour() ) {
		if (file_exists("/usr/lib64")) {
			passthru("cp -rf ../../kloxophpsixfour*.tar.gz /var/cache/kloxo");
			passthru("cp -rf ../../kloxophpsixfour-version /var/cache/kloxo");
			if (!file_exists("/usr/lib64/kloxophp")) {
				passthru("mkdir -p /usr/lib64/kloxophp");
				passthru("ln -s /usr/lib64/kloxophp /usr/lib/kloxophp");
			}
			if (!file_exists("/usr/lib64/php")) {
				passthru("mkdir -p /usr/lib64/php");
				passthru("ln -s /usr/lib64/php /usr/lib/php");
			}
			if (!file_exists("/usr/lib64/httpd")) {
				passthru("mkdir -p /usr/lib64/httpd");
				passthru("ln -s /usr/lib64/httpd /usr/lib/httpd");
			}
			if (!file_exists("/usr/lib64/lighttpd")) {
				passthru("mkdir -p /usr/lib64/lighttpd");
				passthru("ln -s /usr/lib64/lighttpd /usr/lib/lighttpd");
			}
		}
		else {
			//--- use this trick because lazy to make code for version check
			passthru("rename ../kloxophpsixfour ../_kloxophpsixfour ../kloxophpsixfour*");
			passthru("cp -rf ../kloxophp*.tar.gz /var/cache/kloxo");
			passthru("rename ../_kloxophpsixfour ../kloxophpsixfour ../_kloxophpsixfour*");
			passthru("cp -rf ../kloxophp-version /var/cache/kloxo"); 
		}
		chdir("/usr/local/lxlabs/kloxo");
		passthru("mkdir -p /usr/local/lxlabs/kloxo/log");
	}
	else {
		chdir("/usr/local/lxlabs/kloxo");
		system("mkdir -p /usr/local/lxlabs/kloxo/log");
		@ unlink("kloxo-current.zip");
		print("Downloading latest Kloxo release\n");
		system("wget ".$downloadserver."download/kloxo/production/kloxo/kloxo-current.zip");
	}

	print("\n\nInstalling Kloxo.....\n\n");
	system("unzip -oq kloxo-current.zip", $return);

	if ($return) {
		print("Unzipping the core Failed.. Most likely it is corrupted. Report it at http://forum.lxcenter.org/\n");
		exit;
	}

	unlink("kloxo-current.zip");
	system("chown -R lxlabs:lxlabs /usr/local/lxlabs/");
	chdir("/usr/local/lxlabs/kloxo/httpdocs/");
	system("service mysqld start");

	if ($installtype !== 'slave') {
		check_default_mysql($dbroot, $dbpass);
	}
	$mypass = password_gen();

	print("Prepare defaults and configurations...\n");
	// --- change to execute from external to internal
//	system("/usr/local/lxlabs/ext/php/php $dir_name/installall.php");
	install_main();
	our_file_put_contents("/etc/sysconfig/spamassassin", "SPAMDOPTIONS=\" -v -d -p 783 -u lxpopuser\"");
	print("Creating Vpopmail database...\n");
	system("sh $dir_name/vpop.sh $dbroot \"$dbpass\" lxpopuser $mypass");
	system("chmod -R 755 /var/log/httpd/");
	system("chmod -R 755 /var/log/httpd/fpcgisock >/dev/null 2>&1");
	system("mkdir -p /var/log/kloxo/");
	system("mkdir -p /var/log/news");
	system("ln -sf /var/qmail/bin/sendmail /usr/sbin/sendmail");
	system("ln -sf /var/qmail/bin/sendmail /usr/lib/sendmail");
	system("echo `hostname` > /var/qmail/control/me");
	system("service qmail restart >/dev/null 2>&1 &");
	system("service courier-imap restart >/dev/null 2>&1 &");
/* --- enough execute in updatelib.php at next step
	$dbfile="/home/kloxo/httpd/webmail/horde/scripts/sql/create.mysql.sql";
	if(file_exists($dbfile)) {
		if($dbpass == "") {
			system("mysql -u $dbroot  <$dbfile");
		} else {
			system("mysql -u $dbroot -p$dbpass <$dbfile");
		}
	}
--- */
	system("mkdir -p /home/kloxo/httpd");
	chdir("/home/kloxo/httpd");
	@ unlink("skeleton-disable.zip");
	system("chown -R lxlabs:lxlabs /home/kloxo/httpd");
	system("/etc/init.d/kloxo restart >/dev/null 2>&1 &");
	chdir("/usr/local/lxlabs/kloxo/httpdocs/");
	system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");
	system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
	system("/bin/rm /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
	system("/script/centos5-postpostupgrade");
	if ($installappinst) {
		system("/script/installapp-update"); // First run (gets installappdata)
		system("/script/installapp-update"); // Second run (gets applications)
	}

	// --- remove all temporal flags because the end of install
	system("rm -rf /var/cache/kloxo/*-version");
	system("rm -rf /var/cache/kloxo/kloxo-install-*.flg");

	//--- for prevent mysql socket problem (especially on 64bit system)
	if (!file_exists("/var/lib/mysql/mysql.sock")) {
		system("/etc/init.d/mysqld stop");
		system("mksock /var/lib/mysql/mysql.sock");	
		system("/etc/init.d/mysqld start");
	}

	print("Congratulations. Kloxo has been installed succesfully on your server as $installtype \n");
	if ($installtype === 'master') {
		print("You can connect to the server at https://<ip-address>:7777 or http://<ip-address>:7778\n");
		print("Please note that first is secure ssl connection, while the second is normal one.\n");
		print("The login and password are 'admin' 'admin'. After Logging in, you will have to change your password to something more secure\n");
		print("We hope you will find managing your hosting with Kloxo refreshingly pleasurable, and also we wish you all the success on your hosting venture\n");
		print("Thanks for choosing Kloxo to manage your hosting, and allowing us to be of service\n");
	} else {
		print("You should open the port 7779 on this server, since this is used for the communication between master and slave\n");
		print("To access this slave, to go admin->servers->add server, give the ip/machine name of this server. The password is 'admin'. The slave will appear in the list of slaves, and you can access it just like you access localhost\n\n");
	}
}

lxins_main();


// ========== install_common.php portion ========

class remote { }
$downloadserver = "http://download.lxcenter.org/";

function slave_get_db_pass() {
	$file = "/usr/local/lxlabs/kloxo/etc/slavedb/dbadmin";
	if (!file_exists($file)) {
		return null;
	}
	$var = file_get_contents($file);
	$rmt = unserialize($var);
	return $rmt->data['mysql']['dbpassword'];
}

function addLineIfNotExistTemp($filename, $pattern, $comment) {
	$cont = our_file_get_contents($filename);

	if (!preg_match("+$pattern+i", $cont)) {
		our_file_put_contents($filename, "\n$comment \n\n", true);
		our_file_put_contents($filename, $pattern, true);
		our_file_put_contents($filename, "\n\n\n", true);
	} else {
		print("Pattern '$pattern' Already present in $filename\n");
	}
}

function check_default_mysql($dbroot, $dbpass) {
	system("service mysqld restart");

	if ($dbpass) {
		exec("echo \"show tables\" | mysql -u $dbroot -p\"$dbpass\" mysql", $out, $return);
	} else {
		exec("echo \"show tables\" | mysql -u $dbroot mysql", $out, $return);
	}

	if ($return) {
	/*
		print("Fatal Error: Could not connect to Mysql Localhost using user $dbroot and password \"$dbpass\"\n");
		print("If this is a brand new install, you can completely remove mysql by running the commands below\n");
		print("            rm -rf /var/lib/mysql\n");
		print("            rpm -e mysql-server\n\n");
		print("And then run the installer again\n");
		exit;
	*/
		resetDBPassword($dbroot, $dbpass);
	}
}

function parse_opt($argv) {
	unset($argv[0]);
	if (!$argv) {
		return null;
	}
	foreach ($argv as $v) {
		if (strstr($v, "=") === false || strstr($v, "--") === false) {
			continue;
		}
		$opt = explode("=", $v);
		$opt[0] = substr($opt[0], 2);
		$ret[$opt[0]] = $opt[1];
	}
	return $ret;
}

function our_file_get_contents($file) {
	$string = null;

	$fp = fopen($file, "r");

	if (!$fp) {
		return null;
	}

	while (!feof($fp)) {
		$string .= fread($fp, 8192);
	}
	fclose($fp);
	return $string;
}

function our_file_put_contents($file, $contents, $appendflag = false) {

	if ($appendflag) {
		$flag = "a";
	} else {
		$flag = "w";
	}

	$fp = fopen($file, $flag);

	if (!$fp) {
		return null;
	}

	fwrite($fp, $contents);

	fclose($fp);
}

function password_gen() {
	$data = mt_rand(2, 30);
	$pass = "lx" . $data; // lx is a indentifier
	return $pass;
}

function strtil($string, $needle) {
	if (strrpos($string, $needle)) {
		return substr($string, 0, strrpos($string, $needle));
	} else {
		return $string;
	}
}

function strtilfirst($string, $needle) {
	if (strpos($string, $needle)) {
		return substr($string, 0, strpos($string, $needle));
	} else {
		return $string;
	}
}

function strfrom($string, $needle) {
	return substr($string, strpos($string, $needle) + strlen($needle));
}

function char_search_beg($haystack, $needle) {
	if (strpos($haystack, $needle) === 0) {
		return true;
	}
	return false;
}

function install_rhn_sources($osversion) {
	global $downloadserver;
	if (!file_exists("/etc/sysconfig/rhn/sources")) {
		return;
	}

	$data = our_file_get_contents("/etc/sysconfig/rhn/sources");
	if (!preg_match('/lxcenter/i', $data)) {
		$ndata = "yum lxcenter-updates " . $downloadserver . "download/update/$osversion/\$ARCH/\nyum lxcenter-lxupdates http://download.lxcenter.org/download/update/lxgeneral";
		//append it to the file...
		our_file_put_contents("/etc/sysconfig/rhn/sources", "\n\n", true);
		our_file_put_contents("/etc/sysconfig/rhn/sources", $ndata, true);
		our_file_put_contents("/etc/sysconfig/rhn/sources", "\n\n", true);
	}
}

function install_yum_repo($osversion) {
	if (!file_exists("/etc/yum.repos.d")) {
		print("No yum.repos.d dir detected!\n");
		return;
	}
	if (file_exists("/etc/yum.repos.d/lxcenter.repo")) {
		print("LxCenter yum repository file already present.\n");
		return;
	}

	$cont = our_file_get_contents("../lxcenter.repo.template");
	$cont = str_replace("%distro%", $osversion, $cont);
	our_file_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);
}

function find_os_version() {
	if (file_exists("/etc/fedora-release")) {
		$release = trim(file_get_contents("/etc/fedora-release"));
		$osv = explode(" ", $release);
		if (strtolower($osv[1]) === 'core') {
			$osversion = "fedora-" . $osv[3];
		} else {
			$osversion = "fedora-" . $osv[2];
		}

		return $osversion;
	}

	if (file_exists("/etc/redhat-release")) {
		$release = trim(file_get_contents("/etc/redhat-release"));
		$osv = explode(" ", $release);
		if (isset($osv[6])) {
			$osversion = "rhel-" . $osv[6];
		} else {
			$oss = explode(".", $osv[2]);
			$osversion = "centos-" . $oss[0];
		}
		return $osversion;
	}

	print("This Operating System is currently *NOT* supported.\n");
	exit;
}

/**
 * Get Yes/No answer from stdin
 * @param string $question question text
 * @param char $default default answer (optional)
 * @return char 'y' for Yes or 'n' for No
 */
function get_yes_no($question, $default = 'n') {
	if ($default != 'y') {
		$default = 'n';
		$question .= ' [y/N]: ';
	} else {
		$question .= ' [Y/n]: ';
	}
	for (;;) {
		print $question;
		flush();
		$input = fgets(STDIN, 255);
		$input = trim($input);
		$input = strtolower($input);
		if ($input == 'y' || $input == 'yes' || ($default == 'y' && $input == '')) {
			return 'y';
		}
		else if ($input == 'n' || $input == 'no' || ($default == 'n' && $input == '')) {
			return 'n';
		}
	}
}

// --- taken from reset-mysql-root-password.phps
function resetDBPassword($user, $pass)
{
	print("Stopping MySQL\n");
	shell_exec("service mysqld stop");
	print("Start MySQL with skip grant tables\n");
	shell_exec("su mysql -c \"/usr/libexec/mysqld --skip-grant-tables\" >/dev/null 2>&1 &");
	print("Using MySQL to flush privileges and reset password\n");
	sleep(10);
	system("echo \"update user set password = Password('{$pass}') where User = '{$user}'\" | mysql -u [$user} mysql ", $return);

	while($return) {
		print("MySQL could not connect, will sleep and try again\n");
		sleep(10);
		system("echo \"update user set password = Password('{$pass}') where User = '{$user}'\" | mysql -u {$user} mysql", $return);
	}

	print("Password reset succesfully. Now killing MySQL softly\n");
	shell_exec("killall mysqld");
	print("Sleeping 10 seconds\n");
	shell_exec("sleep 10");
	print("Restarting the actual MySQL service\n");
	system("service mysqld restart");
	print("Password successfully reset to \"$pass\"\n");
}

// --- taken from linuxlib.php with modified
function os_is_arch_sixfour()
{
	if (!file_exists("/proc/xen")) {
		$arch = trim(`arch`);
		return $arch === 'x86_64';
	} else {
		$q = system("cat /etc/rpm/platform");
		if ($q === "i686-redhat-linux") {
			return false;
		}
		return true;
	}
}

// ref: http://ideone.com/JWKIf
function is_64bit()
{
	$int = "9223372036854775807";
	$int = intval($int);

	if ($int == 9223372036854775807) {
		return true; // 64bit
	}
	elseif ($int == 2147483647) {
		return false; // 32bit
	}
	else {
		return "error"; // error
	}
}


// ========== installall.php portion ========

if (!function_exists('install_main')) {

	//	include_once "htmllib/lib/include.php";

	$installcomp['mail'] = array("vpopmail", "courier-imap-toaster", "courier-authlib-toaster", "qmail", "safecat", "httpd", "spamassassin", "ezmlm-toaster", "autorespond-toaster");
	$installcomp['web'] = array("httpd", "pure-ftpd");
	$installcomp['dns'] = array("bind", "bind-chroot");
	$installcomp['database'] = array("mysql");


	function install_general_mine($value) {
	    $value = implode(" ", $value);
	    print("Installing $value ....\n");
	    system("PATH=\$PATH:/usr/sbin yum -y install $value");
	}


	function installcomp_mail() {
	    system('pear channel-update "pear.php.net"'); // to remove old channel warning
	    system("pear upgrade --force pear"); // force is needed
	    system("pear upgrade --force Archive_Tar"); // force is needed
	    system("pear upgrade --force structures_graph"); // force is needed
 	   system("pear install log");
	}


	function install_main() {

		global $installcomp;
		global $argv;
		$comp = array("web", "mail", "dns", "database");

		$list = parse_opt($argv);

		if ($list['server-list']) {
			$serverlist = implode(",", $list['server-list']);
		} else {
			$serverlist = $comp;
		}

		foreach ($comp as $c) {
			flush();
			if (array_search($c, $serverlist) !== false) {
				print("Installing $c Components....");
				$req = $installcomp[$c];
				$func = "installcomp_$c";
				if (function_exists($func)) {
					$func();
				}
				install_general_mine($req);
				print("\n");
			}
		}

/* ---issue #589 - no need for new structure
		$pattern = "Include /etc/httpd/conf/kloxo/kloxo.conf";
		$file = "/etc/httpd/conf/httpd.conf";
		$comment = "#Kloxo";
		addLineIfNotExist($file, $pattern, $comment);
		mkdir("/etc/httpd/conf/kloxo/");
		$dir_path = dirname(__FILE__);
		copy("$dir_path/kloxo.conf", "/etc/httpd/conf/kloxo/kloxo.conf");
		touch("/etc/httpd/conf/kloxo/virtualhost.conf");
		touch("/etc/httpd/conf/kloxo/webmail.conf");
		touch("/etc/httpd/conf/kloxo/init.conf");
		mkdir("/etc/httpd/conf/kloxo/forward/");
		touch("/etc/httpd/conf/kloxo/forward/forwardhost.conf");
--- */
/* --- enoguh execute in updatelib.php at next step
		mkdir("/etc/httpd/conf.d");
		copy("/usr/local/lxlabs/kloxo/file/apache/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
		mkdir("/home/httpd/conf");
		mkdir("/home/httpd/conf/defaults");
		mkdir("/home/httpd/conf/domains");
		touch("/home/httpd/conf/defaults/_default.conf");
		touch("/home/httpd/conf/defaults/cp_config.conf");
		touch("/home/httpd/conf/defaults/init.conf");
		touch("/home/httpd/conf/defaults/webmail.conf");
		touch("/home/httpd/conf/defaults/~virtualhost.conf");

		//--- for cp
		if (!file_exists("/home/kloxo/httpd/cp")) {
			mkdir("/home/kloxo/httpd/cp");
			copy("/usr/local/lxlabs/kloxo/file/cp_config_index.php", "/home/kloxo/httpd/cp/index.php");
			system("unzip -oq /usr/local/lxlabs/kloxo/file/skeleton.zip -d /home/kloxo/httpd/cp");
			system("chown -R lxlabs:lxlabs /home/kloxo/httpd/cp");
		}
	
		//--- for default
		if (!file_exists("/home/kloxo/httpd/default")) {
			mkdir("/home/kloxo/httpd/default");
			copy("/usr/local/lxlabs/kloxo/file/default_index.php", "/home/kloxo/httpd/default/index.php");
			system("unzip -oq /usr/local/lxlabs/kloxo/file/skeleton.zip -d /home/kloxo/httpd/default");
			system("chown -R lxlabs:lxlabs /home/kloxo/httpd/default");
		}
	
		//--- for disable
		if (!file_exists("/home/kloxo/httpd/disable")) {
			mkdir("/home/kloxo/httpd/disable");
			copy("/usr/local/lxlabs/kloxo/file/disable_index.php", "/home/kloxo/httpd/disable/index.php");
			system("unzip -oq /usr/local/lxlabs/kloxo/file/skeleton.zip -d /home/kloxo/httpd/disable");
			system("chown -R lxlabs:lxlabs /home/kloxo/httpd/disable");
		}
	
		//--- for webmail
		if (!file_exists("/home/kloxo/httpd/webmail")) {
			mkdir("/home/kloxo/httpd/webmail");
			copy("/usr/local/lxlabs/kloxo/file/webmail_index.php", "/home/kloxo/httpd/webmail/index.php");
			system("unzip -oq /usr/local/lxlabs/kloxo/file/skeleton.zip -d /home/kloxo/httpd/webmail");
			system("chown -R lxlabs:lxlabs /home/kloxo/httpd/webmail");
		}
	
		//--- some vps include /etc/httpd/conf.d/swtune.conf in system
		system("rm -f /etc/httpd/conf.d/swtune.conf");
*/
		$options_file = "/var/named/chroot/etc/global.options.named.conf";

		$example_options  = "acl \"lxcenter\" {\n";
		$example_options .= " localhost;\n";
		$example_options .= "};\n\n";
		$example_options .= "options {\n";
		$example_options .= " max-transfer-time-in 60;\n";
		$example_options .= " transfer-format many-answers;\n";
		$example_options .= " transfers-in 60;\n";
		$example_options .= " auth-nxdomain yes;\n";
		$example_options .= " allow-transfer { \"lxcenter\"; };\n";
		$example_options .= " allow-recursion { \"lxcenter\"; };\n";
		$example_options .= " recursion no;\n";
		$example_options .= " version \"LxCenter-1.0\";\n";
		$example_options .= "};\n\n";
		$example_options .= "# Remove # to see all DNS queries\n";
		$example_options .= "#logging {\n";
		$example_options .= "# channel query_logging {\n";
		$example_options .= "# file \"/var/log/named_query.log\";\n";
		$example_options .= "# versions 3 size 100M;\n";
		$example_options .= "# print-time yes;\n";
		$example_options .= "# };\n\n";
		$example_options .= "# category queries {\n";
		$example_options .= "# query_logging;\n";
		$example_options .= "# };\n";
		$example_options .= "#};\n";

		if (!lfile_exists($options_file)) {
			touch($options_file);
			chown($options_file, "named");
		}

		$cont = lfile_get_contents($options_file);
		$pattern = "options";

		if (!preg_match("+$pattern+i", $cont)) {
			file_put_contents($options_file, "$example_options\n");
		}

		$pattern = 'include "/etc/kloxo.named.conf";';
		$file = "/var/named/chroot/etc/named.conf";
		$comment = "//Kloxo";
		addLineIfNotExist($file, $pattern, $comment);
		touch("/var/named/chroot/etc/kloxo.named.conf");
		chown("/var/named/chroot/etc/kloxo.named.conf", "named");
	}

	function addLineIfNotExist($filename, $pattern, $comment) {
		$cont = lfile_get_contents($filename);

		if (!preg_match("+$pattern+i", $cont)) {
			file_put_contents($filename, "\n$comment \n\n", FILE_APPEND);
			file_put_contents($filename, $pattern, FILE_APPEND);
			file_put_contents($filename, "\n\n\n", FILE_APPEND);
		} else {
			print("Pattern '$pattern' Already present in $filename\n");
		}
	}

	function checkIfYes($arg) {
		return ($arg == 'y' || $arg == 'yes' || $arg == 'Y' || $arg == 'YES');
	}

	function getAcceptValue($soft) {
		print("Do you want me to install $soft Components? (YES/no):");
		flush();
		$argq = fread(STDIN, 5);
		$arg = trim($argq);

		if (!$arg) {
		$arg = 'yes';
		}
		return $arg;
	}

	// install_main();
	
}
