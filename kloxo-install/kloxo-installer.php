<?php
//    Kloxo, Hosting Control Panel
//
//    Copyright (C) 2000-2009	LxLabs
//    Copyright (C) 2009-2011	LxCenter
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

// ==== kloxo_installer portion ===

$downloadserver = "http://download.lxcenter.org/";

function lxins_main()
{
	global $argv, $downloadserver;
	$opt = parse_opt($argv);
	$dir_name=dirname(__FILE__);
	$installtype = $opt['install-type'];
	$installversion = (isset($opt['version'])) ? $opt['version'] : null;
	$dbroot = "root";
	$dbpass = "";
	$osversion = find_os_version();
	$arch = `arch`;
	$arch = trim($arch);
	
	//--- Create temporary flags for install
	system("mkdir -p /var/cache/kloxo/");
	system("echo 1 > /var/cache/kloxo/kloxo-install-firsttime.flg");

	if (!char_search_beg($osversion, "centos") && !char_search_beg($osversion, "rhel")) {
		print("Kloxo is only supported on CentOS 5 and RHEL 5\n");
		exit;
	}

	if(file_exists("/usr/local/lxlabs/kloxo")) {
		//--- Ask Reinstall
		if (get_yes_no("Kloxo seems already installed do you wish to continue?") == 'n') {
			print("Installation Aborted.\n");
			exit;
		}
	} else {
		//--- Ask License
		if (get_yes_no("Kloxo is using AGPL-V3.0 License, do you agree with the terms?") == 'n') {
			print("You did not agree to the AGPL-V3.0 license terms.\n");
			print("Installation aborted.\n\n");
			exit;
		} else {
			print("Installing Kloxo = YES\n\n");
		}
	}
	//--- Ask for InstallApp
	print("InstallApp: PHP Applications like PHPBB, WordPress, Joomla etc\n");
	print("When you choose Yes, be aware of downloading about 350Mb of data!\n");
	if(get_yes_no("Do you want to install the InstallAPP sotfware?") == 'n') {
		print("Installing InstallApp = NO\n");
		print("You can install it later with /script/installapp-update\n\n");
		$installappinst = false;
		//--- Temporary flag so InstallApp won't be installed
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

	$packages = array("sendmail", "sendmail-cf", "sendmail-doc", "sendmail-devel", "exim", "vsftpd", "postfix", "vpopmail", "qmail", "kloxo-core-php", "lxphp" ,"lxzend", "pure-ftpd", "imap");

	$list = implode(" ", $packages);
	print("Removing packages $list...\n");
	foreach ($packages as $package) {
		exec("rpm -e --nodeps $package > /dev/null 2>&1");
	}

	$packages = array("php-mbstring", "php-mysql", "which", "gcc-c++", "php-imap", "php-pear", "php-devel", "lxlighttpd", "httpd", "mod_ssl", "zip", "unzip", "kloxo-core-php", "mysql", "mysql-server", "curl", "autoconf", "automake", "libtool", "bogofilter", "gcc", "cpp", "openssl", "pure-ftpd", "yum-protectbase");

	$list = implode(" ", $packages);

	while (true) {
		print("Installing packages $list...\n");
		system("PATH=\$PATH:/usr/sbin yum -y install $list", $return_value);

		if (file_exists("/usr/local/lxlabs/ext/php/php")) {
			break;
		}
		else {
			print("YUM Gave Error... Trying Again...\n");
			if (get_yes_no("Try again?") == 'n') {
				print("- EXIT: Fix the problem and install Kloxo again.\n");
				exit;
			}
		}
	}

	print("Prepare installation directory\n");
	
	system("mkdir -p /usr/local/lxlabs/kloxo");
	
	if ($installversion) {
		if (substr($installversion, 0, 4) == '6.0.') {
			print("\n*** Need additional files installing $installversion (less then 6.1.0)***\n");
			print("      Run 'sh /script/kloxo-installer.sh' (without argument)\n\n");		
			exit;
		}
		chdir("/usr/local/lxlabs/kloxo");
		system("mkdir -p /usr/local/lxlabs/kloxo/log");
		@ unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
		print("Downloading Kloxo {$installversion} release\n");
		system("wget {$downloadserver}download/kloxo/production/kloxo/kloxo-{$installversion}.zip");
		system("mv -f ./kloxo-{$installversion}.zip ./kloxo-current.zip");
	}
	else {
		if (file_exists("../kloxo-current.zip")) {
			//--- Install from local file if exists
			@ unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
			print("Local copying Kloxo release\n");
			system("mkdir -p /var/cache/kloxo");
			system("cp -rf ../kloxo-current.zip /usr/local/lxlabs/kloxo");

			//--- The first step - Remove packages
			system("rm -f /var/cache/kloxo/kloxo-thirdparty*.zip");
			system("rm -f /var/cache/kloxo/lxawstats*.tar.gz");
			system("rm -f /var/cache/kloxo/lxwebmail*.tar.gz");
			system("rm -f /var/cache/kloxo/kloxophpsixfour*.tar.gz");
			system("rm -f /var/cache/kloxo/kloxophp*.tar.gz");
			system("rm -f /var/cache/kloxo/*-version");
			//--- The second step - copy from packer script if exist
			system("cp -rf ../kloxo-thirdparty*.zip /var/cache/kloxo");
			system("cp -rf ../lxawstats*.tar.gz /var/cache/kloxo");
			system("cp -rf ../lxwebmail*.tar.gz /var/cache/kloxo");
			system("cp -rf ../kloxo-thirdparty-version /var/cache/kloxo");
			system("cp -rf ../lxawstats-version /var/cache/kloxo");
			system("cp -rf ../lxwebmail-version /var/cache/kloxo"); 
			if (file_exists("/usr/lib64")) {
				if (!is_link("/usr/lib/kloxophp")) {
					system("rm -rf /usr/lib/kloxophp");
				}
				system("cp -rf ../kloxophpsixfour*.tar.gz /var/cache/kloxo");
				system("cp -rf ../kloxophpsixfour-version /var/cache/kloxo");
				system("mkdir -p /usr/lib64/kloxophp");
				system("ln -s /usr/lib64/kloxophp /usr/lib/kloxophp");
				system("mkdir -p /usr/lib64/php");
				system("ln -s /usr/lib64/php /usr/lib/php");
				system("mkdir -p /usr/lib64/httpd");
				system("ln -s /usr/lib64/httpd /usr/lib/httpd");
				system("mkdir -p /usr/lib64/lighttpd");
				system("ln -s /usr/lib64/lighttpd /usr/lib/lighttpd");
			}
			else {
				//--- Needs version checks in the future
				system("rename ../kloxophpsixfour ../_kloxophpsixfour ../kloxophpsixfour*");
				system("cp -rf ../kloxophp*.tar.gz /var/cache/kloxo");
				system("rename ../_kloxophpsixfour ../kloxophpsixfour ../_kloxophpsixfour*");
				system("cp -rf ../kloxophp-version /var/cache/kloxo"); 
			}
			chdir("/usr/local/lxlabs/kloxo");
			system("mkdir -p /usr/local/lxlabs/kloxo/log");
		}
		else {
			chdir("/usr/local/lxlabs/kloxo");
			system("mkdir -p /usr/local/lxlabs/kloxo/log");
			if (file_exists("../.git")) {
				print("Development GIT version found skiping downloading kloxo sources");
			} else {
				@ unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
				print("Downloading latest Kloxo release\n");
				system("wget {$downloadserver}download/kloxo/production/kloxo/kloxo-current.zip");	
			}
			
		}
	}

	print("\n\nInstalling Kloxo.....\n\n");
	system("unzip -oq kloxo-current.zip", $return);

	if ($return) {
		print("Unzipping the core Failed.. Most likely it is corrupted. Report it at http://community.lxcenter.org/\n");
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
	install_main();

	file_put_contents("/etc/sysconfig/spamassassin", "SPAMDOPTIONS=\" -v -d -p 783 -u lxpopuser\"");
	print("\nCreating Vpopmail database...\n");
	if (file_exists("/usr/local/lxlabs/kloxo-install/kloxo-linux")) {
		system("sh $dir_name/kloxo-linux/vpop.sh $dbroot \"$dbpass\" lxpopuser $mypass");
	} else {
		system("sh $dir_name/vpop.sh $dbroot \"$dbpass\" lxpopuser $mypass");	
	}
	system("chmod -R 755 /var/log/httpd/");
	system("chmod -R 755 /var/log/httpd/fpcgisock >/dev/null 2>&1");
	system("mkdir -p /var/log/kloxo/");
	system("mkdir -p /var/log/news");
	system("ln -sf /var/qmail/bin/sendmail /usr/sbin/sendmail");
	system("ln -sf /var/qmail/bin/sendmail /usr/lib/sendmail");
	system("echo `hostname` > /var/qmail/control/me");
	system("service qmail restart >/dev/null 2>&1 &");
	system("service courier-imap restart >/dev/null 2>&1 &");

	print("Prepare /home/kloxo/httpd...\n");
	system("mkdir -p /home/kloxo/httpd");
	chdir("/home/kloxo/httpd");
	@ unlink("skeleton-disable.zip");
	system("chown -R lxlabs:lxlabs /home/kloxo/httpd");
	system("/etc/init.d/kloxo restart >/dev/null 2>&1 &");
	chdir("/usr/local/lxlabs/kloxo/httpdocs/");
	system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");

	if ($installappinst) {
		print("Install InstallApp...\n");
		system("/script/installapp-update"); // First run (gets installappdata)
		system("/script/installapp-update"); // Second run (gets applications)
	}

	//--- Remove all temporary flags because the end of install
	print("\nRemove Kloxo install flags...\n");
	system("rm -rf /var/cache/kloxo/*-version");
	system("rm -rf /var/cache/kloxo/kloxo-install-*.flg");

	//--- Prevent mysql socket problem (especially on 64bit system)
	if (!file_exists("/var/lib/mysql/mysql.sock")) {
		print("Create mysql.sock...\n");
		system("/etc/init.d/mysqld stop");
		system("mksock /var/lib/mysql/mysql.sock");	
		system("/etc/init.d/mysqld start");
	}
	
	//--- Prevent for Mysql not start after reboot for fresh kloxo slave install
	print("Setting Mysql for always running after reboot and restart now...\n");
	system("chkconfig mysqld on");
	system("service mysqld restart");	

	//--- Fix for old thirdparty version
	if (!file_exists("/usr/local/lxlabs/kloxo/httpdocs/thirdparty")) {
		system("cp -rf /var/cache/kloxo/kloxo-thirdparty*.zip /usr/local/lxlabs/kloxo");
		system("cd /usr/local/lxlabs/kloxo; unzip -oq kloxo-thirdparty*.zip");
		system("chown -R lxlabs:lxlabs /usr/local/lxlabs/kloxo/httpdocs/thirdparty");
		system("chown -R lxlabs:lxlabs /usr/local/lxlabs/kloxo/httpdocs/htmllib");
		system("rm -f /usr/local/lxlabs/kloxo/kloxo-thirdparty*.zip");
	}

	//--- Set ownership for Kloxo httpdocs dir
	system("chown -R lxlabs:lxlabs /usr/local/lxlabs/kloxo/httpdocs");
	
	print("\nCongratulations. Kloxo has been installed succesfully on your server as $installtype\n\n");
	if ($installtype === 'master') {
		print("You can connect to the server at:\n");
		print("	https://<ip-address>:7777 - secure ssl connection, or\n");
		print("	http://<ip-address>:7778 - normal one.\n\n");
		print("The login and password are 'admin' 'admin'. After Logging in, you will have to\n");
		print("change your password to something more secure\n\n");
		print("We hope you will find managing your hosting with Kloxo\n");
		print("refreshingly pleasurable, and also we wish you all the success\n");
		print("on your hosting venture\n\n");
		print("Thanks for choosing Kloxo to manage your hosting, and allowing us to be of\n");
		print("service\n");
	} else {
		print("You should open the port 7779 on this server, since this is used for\n");
		print("the communication between master and slave\n\n");
		print("To access this slave, to go admin->servers->add server,\n");
		print("give the ip/machine name of this server. The password is 'admin'.\n\n");
		print("The slave will appear in the list of slaves, and you can access it\n");
		print("just like you access localhost\n\n");
	}
	print("\n");
	print("---------------------------------------------\n");
}

// ==== kloxo_all portion ===

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
    system("pear upgrade --force --alldeps"); // force is needed, update all what is possible.
    print ("You can ignore the above warnings from PEAR.");
}

function install_main() {

	$installcomp['mail'] = array("vpopmail", "courier-imap-toaster", "courier-authlib-toaster", "qmail", "safecat", "httpd", "spamassassin", "ezmlm-toaster", "autorespond-toaster");
	$installcomp['web'] = array("httpd", "pure-ftpd");
	$installcomp['dns'] = array("bind", "bind-chroot");
	$installcomp['database'] = array("mysql");

	global $argv;
	$comp = array("web", "mail", "dns", "database");

	$serverlist = $comp;

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

	$options_file = "/var/named/chroot/etc/global.options.named.conf";

	$example_options  = "acl \"lxcenter\" {\n";
	$example_options .= "\tlocalhost;\n";
	$example_options .= "};\n\n";
	$example_options .= "options {\n";
	$example_options .= "\tmax-transfer-time-in 60;\n";
	$example_options .= "\ttransfer-format many-answers;\n";
	$example_options .= "\ttransfers-in 60;\n";
	$example_options .= "\tauth-nxdomain yes;\n";
	$example_options .= "\tallow-transfer { \"lxcenter\"; };\n";
	$example_options .= "\tallow-recursion { \"lxcenter\"; };\n";
	$example_options .= "\trecursion no;\n";
	$example_options .= "\tversion \"LxCenter-1.0\";\n";
	$example_options .= "};\n\n";
	$example_options .= "# Remove # to see all DNS queries\n";
	$example_options .= "# logging {\n";
	$example_options .= "#\t channel query_logging {\n";
	$example_options .= "#\t\t file \"/var/log/named_query.log\";\n";
	$example_options .= "#\t\t versions 3 size 100M;\n";
	$example_options .= "#\t\t print-time yes;\n";
	$example_options .= "#\t };\n\n";
	$example_options .= "#\t category queries {\n";
	$example_options .= "#\t\t query_logging;\n";
	$example_options .= "#\t };\n";
	$example_options .= "# };\n";

	if (!file_exists($options_file)) {
		touch($options_file);
		chown($options_file, "named");
	}

	$cont = file_get_contents($options_file);
	$pattern = "options";

	if (!preg_match("+$pattern+i", $cont)) {
		file_put_contents($options_file, "$example_options\n");
	}

	$pattern = 'include "/etc/global.options.named.conf";';
	$file = "/var/named/chroot/etc/named.conf";
	$comment = "//Kloxo global config (stop being open recursors)";
	addLineIfNotExist($file, $pattern, $comment);


	$pattern = 'include "/etc/kloxo.named.conf";';
	$file = "/var/named/chroot/etc/named.conf";
	$comment = "//Kloxo";
	addLineIfNotExist($file, $pattern, $comment);
	touch("/var/named/chroot/etc/kloxo.named.conf");
	chown("/var/named/chroot/etc/kloxo.named.conf", "named");

}

// ==== kloxo_common portion ===

class remote { }

function slave_get_db_pass() {
	$file = "/usr/local/lxlabs/kloxo/etc/slavedb/dbadmin";
	if (!file_exists($file)) {
		return null;
	}
	$var = file_get_contents($file);
	$rmt = unserialize($var);
	return $rmt->data['mysql']['dbpassword'];
}

function check_default_mysql($dbroot, $dbpass) {
	system("service mysqld restart");

	if ($dbpass) {
		exec("echo \"show tables\" | mysql -u $dbroot -p\"$dbpass\" mysql", $out, $return);
	} else {
		exec("echo \"show tables\" | mysql -u $dbroot mysql", $out, $return);
	}

	if ($return) {
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

function password_gen() {
	$data = mt_rand(2, 30);
	$pass = "lx" . $data; // lx is a indentifier
	return $pass;
}

function char_search_beg($haystack, $needle) {
	if (strpos($haystack, $needle) === 0) {
		return true;
	}
	return false;
}

function install_yum_repo($osversion) {

	global $dirpath;

	if (!file_exists("/etc/yum.repos.d")) {
		print("No yum.repos.d dir detected!\n");
		return;
	}
	if (file_exists("/etc/yum.repos.d/lxcenter.repo")) {
		print("LxCenter yum repository file already present.\n");
		return;
	}

	$cont = file_get_contents("lxcenter.repo.template");
	$cont = str_replace("%distro%", $osversion, $cont);
	file_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);
}

function find_os_version() {
	// list os support
	$ossup = array('redhat' => 'rhel', 'fedora' => 'fedora', 'centos' => 'centos');
	
	foreach(array_keys($ossup) as $k) {
		$osrel = file_get_contents("/etc/{$k}-release");
		if ($osrel) {
				$osrel = strtolower(trim($osrel));
				break;
		}
	}
	
	// specific for 'red hat'
	$osrel = str_replace('red hat', 'redhat', $osrel);

	$osver = explode(" ", $osrel);

	$verpos = sizeof($osver) - 2;

	if (array_key_exists($osver[0], $ossup)) {
		// specific for 'red hat'
		if ($osrel === 'redhat') {
			$oss = $osver[$verpos];
		}
		else {
			$mapos = explode(".", $osver[$verpos]);
			$oss = $mapos[0];
		}
		return $ossup[$osver[0]]."-".$oss;
	}
	else {
		print("This Operating System is currently *NOT* supported.\n");
		exit;
	}

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

function addLineIfNotExist($filename, $pattern, $comment) {

	if (file_exists($filename)) {
		$cont = file_get_contents($filename);
	} else {
		$cont = '';
	}

	if (!preg_match("+$pattern+i", $cont)) {
		file_put_contents($filename, "\n$comment \n\n", FILE_APPEND);
		file_put_contents($filename, $pattern, FILE_APPEND);
		file_put_contents($filename, "\n\n\n", FILE_APPEND);
	} else {
		print("Pattern '$pattern' Already present in $filename\n");
	}


}

lxins_main();
