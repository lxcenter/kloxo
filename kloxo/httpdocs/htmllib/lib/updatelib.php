<?php
/*
 * Used by
 * /script/upcp
 * /script/cleanup
 * (Auto)Update from Kloxo GUI
 * First installs of Kloxo
 *
 */

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	debug_for_backend();
	$login = new Client(null, null, 'upgrade');
	$DoUpdate = false;

	$opt = parse_opt($argv);
	
	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		print("Install Kloxo packages at the first time...\n");
		$DoUpdate = true;
	}
	else {
		print("Getting Version Info from the LxCenter download Server...\n");
		if ((isset($opt['till-version']) && $opt['till-version']) || lxfile_exists("__path_slave_db")) {
			$sgbl->slave = true;
			$upversion = findNextVersion($opt['till-version']);
			$type = 'slave';
		} else {
			$sgbl->slave = false;
			$upversion = findNextVersion();
			$type = 'master';
		}

		if ($upversion) {
			print("Connecting LxCenter download server...\nPlease wait....\n");
			do_upgrade($upversion);
			print("Upgrade Done.\nCleanup....\n");
			flush();
		} else {
			$localversion = $sgbl->__ver_major_minor_release;
			print("Kloxo is the latest version ($localversion)\n");

			installThirdparty();
			installWebmail();
			installAwstats();

			$DoUpdate = false;
		}
	}

	if ( $DoUpdate == false ) {
		print("Run /script/cleanup if you want to fix/restore/(re)install non working components.\n");
			exit;
	}

	if (is_running_secondary()) {
		print("Not running Update Cleanup, because this is running secondary \n");
		exit;
	}
	
	//
	// Executing update/cleanup process
	//
	lxfile_cp("htmllib/filecore/php.ini", "/usr/local/lxlabs/ext/php/etc/php.ini");
	$res = pcntl_exec("/bin/sh", array("../bin/common/updatecleanup.sh", "--type=$type"));
}

function updatecleanup()
{
	global $gbl, $sgbl, $login, $ghtml;
	log_cleanup("OS Create Kloxo init.d service file and copy core php.ini (lxphp)");
	os_create_program_service();

	log_cleanup("OS Fix programroot path permissions");
	os_fix_lxlabs_permission();

	log_cleanup("OS Restart Kloxo service");
	os_restart_program();

    // Fixes #303 and #304
	log_cleanup("ThirdParty Checks");
	installThirdparty();

	log_cleanup("Initialize phpMyAdmin configfile");
	lxfile_cp("../file/phpmyadmin_config.inc.phps", "thirdparty/phpMyAdmin/config.inc.php");

	log_cleanup("- phpMyAdmin: Set db password in configfile");
	$DbPass = file_get_contents("/usr/local/lxlabs/kloxo/etc/conf/kloxo.pass");
	$phpMyAdminCfg = "/usr/local/lxlabs/kloxo/httpdocs/thirdparty/phpMyAdmin/config.inc.php";
	$content = file_get_contents($phpMyAdminCfg);
	$content = str_replace("# Kloxo-Marker", "# Kloxo-Marker\n\$cfg['Servers'][\$i]['controlpass'] = '" . $DbPass . "';", $content);
	lfile_put_contents($phpMyAdminCfg, $content);
	$DbPass = "";

// TODO: Need another way to do this (use root pass)
/*
	log_cleanup("- phpMyAdmin: Import PMA Database and create tables if they do not exist");
	system("kloxodb < ../httpdocs/sql/phpMyAdmin/phpMyAdmin.sql");
*/
	

	log_cleanup("Initialize OS admin account description");
	$desc = uuser::getUserDescription('admin');
	$list = posix_getpwnam('admin');
	if ($list && ($list['gecos'] !== $desc)) {
		lxshell_return("usermod", "-c", $desc, "admin");
	}

	log_cleanup("Initialize lxphp");
	// TODO: php six four symlink remove when lxphp 64bit is ready!
	if (file_exists("/usr/lib64")) {
		installWithVersion("/usr/lib64/kloxophp", "kloxophpsixfour");
		if (!lxfile_exists("/usr/lib/kloxophp")) {
			lxfile_symlink("/usr/lib64/kloxophp", "/usr/lib/kloxophp");
		}
		if (!lxfile_exists("/usr/lib/php")) {
			lxfile_symlink("/usr/lib64/php", "/usr/lib/php");
		}
		if (!lxfile_exists("/usr/lib/httpd")) {
			lxfile_symlink("/usr/lib64/httpd", "/usr/lib/httpd");
		}
		if (!lxfile_exists("/usr/lib/lighttpd")) {
			lxfile_symlink("/usr/lib64/lighttpd", "/usr/lib/lighttpd");
		}
	} else {
		installWithVersion("/usr/lib/kloxophp", "kloxophp");
	}

	log_cleanup("Checking WebMail");
	installWebmail();

	log_cleanup("Checking awstats");
	installAwstats();

	log_cleanup("Initialize system files");
	log_cleanup("- Install RoundCube database config");
	lxfile_cp("../file/webmail-chooser/db.inc.phps", "/home/kloxo/httpd/webmail/roundcube/config/db.inc.php");

	if (!lxfile_exists("/home/lighttpd/conf")) {
		log_cleanup("- Create /home/lighttpd/conf/ dir");
		lxfile_mkdir("/home/lighttpd/conf");
		lxfile_mkdir("/home/lighttpd/conf/defaults");
		lxfile_mkdir("/home/lighttpd/conf/domains");
	}

	if (!lxfile_exists("/var/bogofilter")) {
		log_cleanup("- Create /var/bogofilter dir");
		lxfile_mkdir("/var/bogofilter");
	}

	if (!lxfile_exists("/home/kloxo/httpd/lighttpd")) {
		log_cleanup("- Create /home/kloxo/httpd/lighttpd dir");
		lxfile_mkdir("/home/kloxo/httpd/lighttpd");
	}

	if (lxfile_exists("/home/admin/domain")) {
		log_cleanup("- Remove dir /home/admin/domain/");
		rmdir("/home/admin/domain/");
	}

	if (lxfile_exists("/home/admin/old")) {
		log_cleanup("- Remove dir /home/admin/old/");
		rmdir("/home/admin/old/");
	}

	if (lxfile_exists("/home/admin/cgi-bin")) {
		log_cleanup("- Remove dir /home/admin/cgi-bin/");
		rmdir("/home/admin/cgi-bin/");
	}

	if (lxfile_exists("/etc/skel/Maildir")) {
		log_cleanup("- Remove dir /etc/skel/Maildir/");
		rmdir("/etc/skel/Maildir/new");
		rmdir("/etc/skel/Maildir/cur");
		rmdir("/etc/skel/Maildir/tmp");
		rmdir("/etc/skel/Maildir/");
	}

	if (!lxfile_exists("/usr/sbin/lxrestart")) {
		log_cleanup("- Install lxrestart binary");
		system("cp ../cexe/lxrestart /usr/sbin/");
		system("chown root:root /usr/sbin/lxrestart");
		system("chmod 755 /usr/sbin/lxrestart");
		system("chmod ug+s /usr/sbin/lxrestart");
	}

	log_cleanup("- Add symlink for qmail-sendmail");
	system("ln -sf /var/qmail/bin/sendmail /usr/sbin/sendmail");
	system("ln -sf /var/qmail/bin/sendmail /usr/lib/sendmail");

	if (!lxfile_exists("/usr/bin/lxredirecter.sh")) {
		log_cleanup("- Install lxredirector binary");
		system("cp ../file/linux/lxredirecter.sh /usr/bin/");
		system("chmod 755 /usr/bin/lxredirecter.sh");
	}

	if (!lxfile_exists("/usr/bin/php-cgi")) {
		log_cleanup("- Install php-cgi binary");
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}

	if (!lxfile_exists("/usr/local/bin/php")) {
		log_cleanup("- Create Symlink /usr/bin/php to /usr/local/bin/php");
		lxfile_symlink("/usr/bin/php", "/usr/local/bin/php");
	}
	if (lxfile_exists('kloxo.sql')) {
		log_cleanup("- Remove file kloxo.sql");
		lunlink('kloxo.sql');
	}

	log_cleanup("Remove lighttpd errorlog");
	remove_lighttpd_error_log();

	log_cleanup("Fix the secure logfile");
	call_with_flag("fix_secure_log");

	log_cleanup("Clean hosts.deny");
	call_with_flag("remove_host_deny");

	log_cleanup("Initialize InstallApp");
	installInstallApp();

	if (!lxfile_exists("/etc/pure-ftpd/pureftpd.pdb")) {
		log_cleanup("Make pure-ftpd user database");
		lxfile_touch("/etc/pure-ftpd/pureftpd.passwd");
		lxshell_return("pure-pw", "mkdb");
	}

	if (lxfile_exists("phpinfo.php")) {
		log_cleanup("Remove phpinfo.php");
		lxfile_rm("phpinfo.php");
	}

	if (!lxfile_exists("/var/named/chroot/etc/kloxo.named.conf")) {
		log_cleanup("Initialize Kloxo bind config files");
		lxfile_touch("/var/named/chroot/etc/kloxo.named.conf");
		lxfile_touch("/var/named/chroot/etc/global.options.named.conf");
	}

	log_cleanup("Killing gettraffic system process");
	lxshell_return("pkill", "-f", "gettraffic");

	log_cleanup("Checking for maildrop-toaster rpm package");
	install_if_package_not_exist("maildrop-toaster");
	log_cleanup("Checking for spamdyke rpm package");
	install_if_package_not_exist("spamdyke");
	log_cleanup("Checking for spamdyke-utils rpm package");
	install_if_package_not_exist("spamdyke-utils");
	log_cleanup("Checking for pure-ftpd rpm package");
	install_if_package_not_exist("pure-ftpd");
	log_cleanup("Checking for simscan-toaster rpm package");
	install_if_package_not_exist("simscan-toaster");
	log_cleanup("Checking for webalizer rpm package");
	install_if_package_not_exist("webalizer");
	log_cleanup("Checking for php-mcrypt rpm package");
	install_if_package_not_exist("php-mcrypt");
	log_cleanup("Checking for dos2unix rpm package");
	install_if_package_not_exist("dos2unix");
	log_cleanup("Checking for rrdtool rpm package");
	install_if_package_not_exist("rrdtool");
	log_cleanup("Checking for xinetd rpm package");
	install_if_package_not_exist("xinetd");
	log_cleanup("Checking for lxjailshell rpm package");
	install_if_package_not_exist("lxjailshell");
	log_cleanup("Checking for php-xml rpm package");
	install_if_package_not_exist("php-xml");
	log_cleanup("Checking for libmhash rpm package");
	install_if_package_not_exist("libmhash");
	log_cleanup("Checking for lxphp rpm package");
	install_if_package_not_exist("lxphp");
	log_cleanup("Checking for php-gd rpm package");
	install_if_package_not_exist("php-gd");

	log_cleanup("Checking xcache");
	install_xcache();

	log_cleanup("Install Kloxo service");
	lxfile_unix_chmod("/etc/init.d/kloxo", "0755");
	system("chkconfig kloxo on");

	log_cleanup("Initialize /script/ dir");
	copy_script();

	if (!lxfile_exists("/usr/bin/execzsh.sh")) {
		log_cleanup("Installing jailshell to system");
		addLineIfNotExistInside("/etc/shells", "/usr/bin/lxjailshell", "");
		lxfile_cp("htmllib/filecore/execzsh.sh", "/usr/bin/execzsh.sh");
		lxfile_unix_chmod("/usr/bin/execzsh.sh", "0755");
	}

	log_cleanup("Set /home permission to 0755");
	lxfile_unix_chmod("/home", "0755");

	if (is_centosfive()) {
		log_cleanup("Executing centos5-postpostupgrade script");
		lxshell_return("sh", "../pscript/centos5-postpostupgrade");
		lxfile_cp("../file/centos-5/CentOS-Base.repo", "/etc/yum.repos.d/CentOS-Base.repo");
		log_cleanup("Remove epel.repo from system");
		lxfile_rm("/etc/yum.repos.d/epel.repo");
	}

	log_cleanup("Fix RedHat NetWork Source");
	fix_rhn_sources_file();

	log_cleanup("Install/Fix Services/Permissions/Configfiles");
	if (!lxfile_exists("/usr/bin/lxphp.exe")) {
		log_cleanup("- Create lxphp.exe Symlink");
		lxfile_symlink("__path_php_path", "/usr/bin/lxphp.exe");
	}

	log_cleanup("- Install /etc/httpd/conf/httpd.conf");
	lxfile_cp("../file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");

	if (lxfile_exists("/etc/httpd/conf/kloxo")) {
		log_cleanup("- Remove dir /etc/httpd/conf/kloxo");
		passthru("rm -rf /etc/httpd/conf/kloxo");
	}
	
	if (!lxfile_exists("/etc/httpd/conf.d")) {
		log_cleanup("- Create /etc/httpd/conf.d");
		lxfile_mkdir("/etc/httpd/conf.d");
	}

	if (!lxfile_exists("/home/httpd/conf/defaults")) {
		log_cleanup("- Create /home/httpd/conf/defaults");
		lxfile_mkdir("/home/httpd/conf/defaults");
		log_cleanup("- Create /home/httpd/conf/domains");
		lxfile_mkdir("/home/httpd/conf/domains");
	}

	if (!lxfile_real("/etc/httpd/conf.d/~lxcenter.conf")) {	
		log_cleanup("- Install /etc/httpd/conf.d/~lxcenter.conf");
		lxfile_cp("../file/apache/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
	}

	if (!lxfile_real("/etc/httpd/conf.d/ssl.conf")) {
		log_cleanup("- Install /etc/httpd/conf.d/ssl.conf");
		lxfile_cp("../file/apache/default_ssl.conf", "/etc/httpd/conf.d/ssl.conf");
	}

	if (!lxfile_real("/home/httpd/conf/defaults/webmail_redirect.conf")) {
		log_cleanup("- Initialize /home/httpd/conf/defaults/webmail_redirect.conf");
		lxfile_touch("/home/httpd/conf/defaults/webmail_redirect.conf");
	}
	if (!lxfile_real("/home/httpd/conf/defaults/ssl.conf")) {
		log_cleanup("- Initialize /home/httpd/conf/defaults/ssl.conf");
		lxfile_touch("/home/httpd/conf/defaults/ssl.conf");
	}
	if (!lxfile_real("/home/httpd/conf/defaults/_default.conf")) {
		log_cleanup("- Initialize /home/httpd/conf/defaults/_default.conf");
		lxfile_touch("/home/httpd/conf/defaults/_default.conf");
	}
	if (!lxfile_real("/home/httpd/conf/defaults/cp_config.conf")) {
		log_cleanup("- Initialize /home/httpd/conf/defaults/cp_config.conf");
		lxfile_touch("/home/httpd/conf/defaults/cp_config.conf");
	}
	if (!lxfile_real("/home/httpd/conf/defaults/mimetype.conf")) {
		log_cleanup("- Initialize/home/httpd/conf/defaults/mimetype.conf");
		lxfile_touch("/home/httpd/conf/defaults/mimetype.conf");
	}

	if (lxfile_exists("/etc/init.d/pure-ftpd")) {
		log_cleanup("- Remove /etc/init.d/pure-ftpd service file");
		@lxfile_rm("/etc/init.d/pure-ftpd");
	}

	if (!lxfile_exists("/etc/xinetd.d/pureftp")) {
		log_cleanup("- Install /etc/xinetd.d/pureftp TCP Wrapper file");
		lxfile_cp("../file/xinetd.pureftp", "/etc/xinetd.d/pureftp");
	}

	if(!lxfile_real("/etc/pki/pure-ftpd/pure-ftpd.pem")) {
		log_cleanup("- Install pure-ftpd ssl/tls key");
		lxfile_mkdir("/etc/pki/pure-ftpd/");
		lxfile_cp("../file/program.pem", "/etc/pki/pure-ftpd/pure-ftpd.pem");
	}

	if (!lxfile_exists("/etc/xinetd.d/smtp_lxa")) {
		log_cleanup("- Install xinetd smtp_lxa SMTP TCP Wrapper");
		lxfile_cp("../file/xinetd.smtp_lxa", "/etc/xinetd.d/smtp_lxa");
	}

	if (!lxfile_exists("/etc/xinetd.d/pure-ftpd")) {
		log_cleanup("- Remove /etc/xinetd.d/pure-ftpd TCP Wrapper file");
		@lxfile_rm("/etc/xinetd.d/pure-ftpd");
	}

	if (!lxfile_exists("/etc/init.d/qmail")) {
		log_cleanup("- Install qmail service");
		lxfile_cp("../file/qmail.init", "/etc/init.d/qmail");
		lxfile_unix_chmod("/etc/init.d/qmail", "0755");
	}

	if (!lxfile_exists("/etc/lxrestricted")) {
		log_cleanup("- Install /etc/lxrestricted file (lxjailshell commands restrictions)");
		lxfile_cp("../file/lxrestricted", "/etc/lxrestricted");
	}

	if (!lxfile_exists("/etc/sysconfig/spamassassin")) {
		log_cleanup("- Install /etc/sysconfig/spamassassin");
		lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");
	}

	$name = trim(lfile_get_contents("/var/qmail/control/me"));
	log_cleanup("- Install qmail defaultdomain and defaulthost ($name)");
	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaultdomain");
	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaulthost");
	log_cleanup("- Install qmail SMTP Greeting ($name - Welcome to Qmail)");
	lfile_put_contents("/var/qmail/control/smtpgreeting", "$name - Welcome to Qmail");

	if (!lxfile_exists("/usr/bin/rblsmtpd")) {
		log_cleanup("- Initialize rblsmtpd binary");
		lxshell_return("ln", "-s", "/usr/local/bin/rblsmtpd", "/usr/bin/");
	}
	if (!lxfile_exists("/usr/bin/tcpserver")) {
		log_cleanup("- Initialize tcpserver binary");
		lxshell_return("ln", "-s", "/usr/local/bin/tcpserver", "/usr/bin/");
	}

	log_cleanup("- Enable xinetd service");
	call_with_flag("enable_xinetd");

	log_cleanup("- Fix suexec");
	fix_suexec();

	log_cleanup("- Restart xinetd service");
	call_with_flag("restart_xinetd_for_pureftp");

	if (!lxfile_exists("/usr/bin/php-cgi")) {
		log_cleanup("- Initialize php-cgi binary");
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}
	log_cleanup("- Set permissions for /usr/bin/php-cgi");
	lxfile_unix_chmod("/usr/bin/php-cgi", "0755");
	log_cleanup("- Set permissions for closeallinput binary");
	lxfile_unix_chmod("../cexe/closeallinput", "0755");
	log_cleanup("- Set permissions for lxphpsu binary");
	lxfile_unix_chown("../cexe/lxphpsu", "root:root");
	lxfile_unix_chmod("../cexe/lxphpsu", "0755");
	lxfile_unix_chmod("../cexe/lxphpsu", "ug+s");
	log_cleanup("- Set permissions for phpsuexec.sh script");
	lxfile_unix_chmod("../file/phpsuexec.sh", "0755");
	log_cleanup("- Set permissions for /home/kloxo/httpd/lighttpd/ dir");
	system("chown -R apache:apache /home/kloxo/httpd/lighttpd/");
	log_cleanup("- Set permissions for /var/lib/php/session/ dir");
	system("chmod 777 /var/lib/php/session/");
	system("chmod o+t /var/lib/php/session/");
	log_cleanup("- Check bogofilter data");
	install_bogofilter();
	log_cleanup("- Set permissions for /var/bogofilter/ dir");
	system("chmod 777 /var/bogofilter/");
	system("chmod o+t /var/bogofilter/");
	log_cleanup("- Kill sisinfoc system process");
	system("pkill -f sisinfoc");

	log_cleanup("- Install /etc/lighttpd/lighttpd.conf");
	lxfile_cp("../file/lighttpd/lighttpd.conf", "/etc/lighttpd/lighttpd.conf");

	if (lxfile_exists("/etc/lighttpd/conf/kloxo")) {
		log_cleanup("- Remove /etc/lighttpd/conf/kloxo if exists");
		passthru("rm -rf /etc/lighttpd/conf/kloxo");
	}

	if (!lxfile_exists("/etc/lighttpd/conf.d")) {
		log_cleanup("- Create /etc/lighttpd/conf.d");
		lxfile_mkdir("/etc/lighttpd/conf.d");
	}

	if (!lxfile_exists("/home/lighttpd/conf/defaults")) {
		log_cleanup("- Create /home/lighttpd/conf/defaults");
		lxfile_mkdir("/home/lighttpd/conf/defaults");
		log_cleanup("- Create /home/lighttpd/conf/domains");
		lxfile_mkdir("/home/lighttpd/conf/domains");
	}

	if (!lxfile_real("/etc/lighttpd/conf.d/~lxcenter.conf")) {
		log_cleanup("- Initialize /etc/lighttpd/conf.d/~lxcenter.conf");
		lxfile_cp("../file/lighttpd/~lxcenter.conf", "/etc/lighttpd/conf.d/~lxcenter.conf");
	}

	if (!lxfile_real("/etc/lighttpd/local.lighttpd.conf")) {
		log_cleanup("- Initialize /etc/lighttpd/local.lighttpd.conf");
		system("echo > /etc/lighttpd/local.lighttpd.conf");
	}
	if (!lxfile_real("/home/lighttpd/conf/defaults/webmail_redirect.conf")) {
		log_cleanup("- Initialize /home/lighttpd/conf/defaults/webmail_redirect.conf");
		system("echo > /home/lighttpd/conf/defaults/webmail_redirect.conf");
	}
	if (!lxfile_real("/home/lighttpd/conf/defaults/~virtualhost.conf")) {
		log_cleanup("- Initialize /home/lighttpd/conf/defaults/~virtualhost.conf");
		system("echo > /home/lighttpd/conf/defaults/~virtualhost.conf");
	}
	if (!lxfile_real("/home/lighttpd/conf/defaults/ssl.conf")) {
		log_cleanup("- Initialize /home/lighttpd/conf/defaults/ssl.conf");
		system("echo > /home/lighttpd/conf/defaults/ssl.conf");
	}
	if (!lxfile_real("/home/lighttpd/conf/defaults/mimetype.conf")) {
		log_cleanup("- Initialize /home/lighttpd/conf/defaults/mimetype.conf");
		system("echo > /home/lighttpd/conf/defaults/mimetype.conf");
	}

	log_cleanup("- Install /etc/init.d/lighttpd service file");
	lxfile_cp("../file/lighttpd/etc_init.d", "/etc/init.d/lighttpd");

	if (!lxfile_exists("/etc/pure-ftpd/pureftpd.passwd")) {
		log_cleanup("- Initialize /etc/pure-ftpd/pureftpd.passwd password database");
		lxfile_cp("/etc/pureftpd.passwd", "/etc/pure-ftpd/pureftpd.passwd");
		lxshell_return("pure-pw", "mkdb");
		createRestartFile("xinetd");
	}

	if (!lxfile_exists("../etc/flag/xcache_enabled.flg")) {
		if (lxfile_exists("/etc/php.d/xcache.ini")) {
			log_cleanup("- xcache flag not found, removing /etc/php.d/xcache.ini file");
			lunlink("/etc/php.d/xcache.ini");
		}
	}

	log_cleanup("- Turn off pure-ftpd service");
	@ exec("chkconfig pure-ftpd off 2>/dev/null");

	log_cleanup("- Initialize nobody.sh script");
	$string = null;
	$uid = os_get_uid_from_user("lxlabs");
	$gid = os_get_gid_from_user("lxlabs");
	$string .= "#!/bin/sh\n";
	$string .= "export MUID=$uid\n";
	$string .= "export GID=$gid\n";
	$string .= "export TARGET=/usr/bin/php-cgi\n";
	$string .= "export NON_RESIDENT=1\n";
	$string .= "exec lxsuexec $*\n";
	lfile_put_contents("/home/httpd/nobody.sh", $string);
	lxfile_unix_chmod("/home/httpd/nobody.sh", "0755");

	log_cleanup("- Execute lxpopuser.sh");
	system("sh ../bin/misc/lxpopuser.sh");

	log_cleanup("- Remove /home/kloxo/httpd/script dir");
	lxfile_rm_content("__path_home_root/httpd/script/");
	log_cleanup("- Initialize /home/kloxo/httpd/script dir");
	lxfile_mkdir("/home/kloxo/httpd/script");
	lxfile_unix_chown_rec("/home/kloxo/httpd/script", "lxlabs:lxlabs");
	log_cleanup("- Install phpinfo.php into /home/kloxo/httpd/script dir");
	lxfile_cp("../file/script/phpinfo.phps", "/home/kloxo/httpd/script/phpinfo.php");

	log_cleanup("- Install /etc/init.d/djbdns service file");
	lxfile_cp("../file/djbdns.init", "/etc/init.d/djbdns");

	log_cleanup("- Enable the correct drivers (Service daemons)");
	removeOtherDriver();

	log_cleanup("- Remove cache dir");
	lxfile_rm_rec("__path_program_root/cache");

	log_cleanup("- Restart syslog service");
	createRestartFile('syslog');

	log_cleanup("- Initialize awstats dirdata");
	lxfile_mkdir("/home/kloxo/httpd/awstats/dirdata");

	if (!lxfile_exists("/etc/logrotate.d/kloxo")) {
		log_cleanup("- Install /etc/logrotate.d/kloxo");
		lxfile_cp("../file/kloxo.logrotate", "/etc/logrotate.d/kloxo");
	}

	log_cleanup("Install RoundCube");
	installRoundCube();
	
	log_cleanup("Install Horde");
	installHorde();

	log_cleanup("Install Webmailchooser");
	installChooser();

	// Remove this after 6.2.0
	log_cleanup("Remove old lxlabs ssh key");
 	remove_ssh_self_host_key();

	# Issue #450
	log_cleanup("Running Fix #450");
	if (lxfile_exists("/proc/user_beancounters")) {
	    create_dev();
	    lxfile_cp("../file/openvz/inittab", "/etc/inittab");
	} else {
	    if (!lxfile_exists("/sbin/udevd")) {
			lxfile_mv("/sbin/udevd.back", "/sbin/udevd");
	    }
	}

	lxfile_cp("../file/skeleton.zip", "/home/kloxo/httpd/skeleton.zip");

	setDefaultPages();
	
	changeMailSoftlimit();

	log_cleanup("Finished.");
	// End of upcp / cleanup
}

function update_all_slave()
{
	$db = new Sqlite(null, "pserver");

	$list = $db->getTable(array("nname"));

	foreach($list as $l) {
		if ($l['nname'] === 'localhost') {
			continue;
		}
		try {
			log_cleanup("Contacting Slave {$l['nname']} to update...");
			rl_exec_get(null, $l['nname'], 'remotetestfunc', null);
		} catch (exception $e) {
			$message = $e->getMessage();
			if ($message === "no_socket_connect_to_server") {
			log_cleanup("!!! Could not contact Slave {$l['nname']} [offline?]");
			} else {
			log_cleanup($message);
			}
		}
	}

}

function findNextVersion($lastversion = null)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$thisversion = $sgbl->__ver_major_minor_release;

	$upgrade = null;
	$nlist = getVersionList($lastversion);
	dprintr($nlist);
	$k = 0;
	print("Found version(s):");
	foreach($nlist as $l) {
		print("- $l");
		if (version_cmp($thisversion, $l) === -1) {
			$upgrade = $l;
			break;
		}
		$k++;
	}
	print("\n");
	if (!$upgrade) {
		return 0;
	}

	print("Upgrading from $thisversion to $upgrade\n");
	return $upgrade;

}

function do_upgrade($upversion)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$program = $sgbl->__var_program_name;

	if (file_exists(".svn")) {
		log_cleanup("BREAK - Development version found");
		exit;
	}

	$programfile = "$program-" . $upversion . ".zip";

	lxfile_rm_rec("__path_program_htmlbase/help");
	lxfile_mkdir("help");
	lxfile_rm_rec("__path_program_htmlbase/htmllib/script");
	lxfile_rm_rec("__path_program_root/pscript");

	$saveddir = getcwd();
	lxfile_rm_rec("__path_program_htmlbase/download");
	lxfile_mkdir("download");
	chdir("download");
	log_cleanup("Downloading $programfile");
	download_source("/$program/$programfile");
	log_cleanup("Download Done!\n Start unzip");
	system("cd ../../ ; unzip -o httpdocs/download/$programfile");
	chdir($saveddir);
}

// Kloxo 6.2.x
// --- moved from kloxo/httpdocs/lib/updatelib.php
// old name is fixExtraDB() without log_cleanup() call
function fixDataBaseIssues()
{
	log_cleanup("Fix admin account database settings");
	$sq = new Sqlite(null, 'domain');
	$sq->rawQuery("update domain set priv_q_php_flag = 'on'");
	$sq->rawQuery("update web set priv_q_php_flag = 'on'");
	$sq->rawQuery("update client set priv_q_php_flag = 'on'");
	$sq->rawQuery("update client set priv_q_addondomain_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_rubyrails_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_rubyfcgiprocess_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_mysqldb_usage = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_phpfcgi_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_phpfcgiprocess_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_subdomain_num = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_totaldisk_usage = 'Unlimited' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_php_manage_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_installapp_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_cron_minute_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_document_root_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_runstats_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update client set priv_q_webhosting_flag = 'on' where nname = 'admin'");
	$sq->rawQuery("update ticket set parent_clname = 'client-admin' where subject = 'Welcome to Kloxo'");
	$sq->rawQuery("update domain set dtype = 'maindomain' where dtype = 'domain'");

	log_cleanup("Set default database settings");
	db_set_default('mmail', 'remotelocalflag', 'local');
	db_set_default('mmail', 'syncserver', 'localhost');
	db_set_default('dns', 'syncserver', 'localhost');
	db_set_default('pserver', 'coma_psrole_a', ',web,dns,mmail,mysqldb,');
	db_set_default('web', 'syncserver', 'localhost');
	db_set_default('uuser', 'syncserver', 'localhost');
	db_set_default('client', 'syncserver', 'localhost');
	db_set_default('addondomain', 'mail_flag', 'on');
	db_set_default('client', 'priv_q_can_change_limit_flag', 'on');
	db_set_default('web', 'priv_q_installapp_flag', 'on');
	db_set_default('client', 'priv_q_installapp_flag', 'on');
	db_set_default('client', 'websyncserver', 'localhost');
	db_set_default('client', 'mmailsyncserver', 'localhost');
	db_set_default('client', 'mysqldbsyncserver', 'localhost');
	db_set_default('client', 'priv_q_can_change_password_flag', 'on');
	db_set_default('client', 'coma_dnssyncserver_list', ',localhost,');
	db_set_default('domain', 'priv_q_installapp_flag', 'on');
	db_set_default('domain', 'dtype', 'domain');
	db_set_default('domain', 'priv_q_php_manage_flag', 'on');
	db_set_default('web', 'priv_q_php_manage_flag', 'on');
	db_set_default('client', 'priv_q_php_manage_flag', 'on');
	db_set_default('client', 'priv_q_webhosting_flag', 'on');
	db_set_default_variable_diskusage('client', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable_diskusage('domain', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable('web', 'docroot', 'nname');
	db_set_default_variable('client', 'used_q_maindomain_num', 'used_q_domain_num');
	db_set_default_variable('client', 'priv_q_maindomain_num', 'priv_q_domain_num');
	db_set_default("servermail", "domainkey_flag", "on");

	log_cleanup("Fix resourceplan settings in database");
	migrateResourceplan('domain');
	$sq->rawQuery("update resourceplan set realname = nname where realname = ''");
	$sq->rawQuery("update resourceplan set realname = nname where realname is null");
	lxshell_php("../bin/common/fixresourceplan.php");

	log_cleanup("Alter some database tables");
	// TODO: Check if this is still longer needed!
	$sq->rawQuery("alter table sslcert change text_ca_content text_ca_content longtext");
	$sq->rawQuery("alter table sslcert change text_key_content text_key_content longtext");
	$sq->rawQuery("alter table sslcert change text_csr_content text_csr_content longtext");
	$sq->rawQuery("alter table sslcert change text_crt_content text_crt_content longtext");
	$sq->rawQuery("alter table mailaccount change ser_forward_a ser_forward_a longtext");
	$sq->rawQuery("alter table dns change ser_dns_record_a ser_dns_record_a longtext");
	$sq->rawQuery("alter table installsoft change ser_installappmisc_b ser_installappmisc_b longtext");
	$sq->rawQuery("alter table web change ser_redirect_a ser_redirect_a longtext");

	log_cleanup("Set default welcome text at Kloxo login page");
	initDbLoginPre();

	log_cleanup("Remove default db password if exists");
	critical_change_db_pass();
}

// Kloxo 6.2.x
// old name is doUpdateExtraStuff() without log_cleanup() call
function doUpdates()
{
	global $gbl, $sgbl, $login, $ghtml;

	log_cleanup("Create flag dir");
	lxfile_mkdir("__path_program_etc/flag");

	log_cleanup("Fix IP Address");
	lxshell_return("lphp.exe", "../bin/fixIpAddress.php");

	log_cleanup("Fix Services");
	fixservice();

	log_cleanup("Create domain backup dirs");
	add_domain_backup_dir();

	log_cleanup("Create OS system user admin...");
	if (!posix_getpwnam('admin')) {
		os_create_system_user('admin', randomString(7), 'admin', '/sbin/nologin', "/home/admin");
		log_cleanup("...account admin created");
	} else {
		log_cleanup("..admin account exists");
	}

	log_cleanup("Fix php.ini");
	call_with_flag("fix_phpini");

	log_cleanup("Fix awstats");
	call_with_flag("fix_awstats");

	log_cleanup("Fix Domainkeys");
	call_with_flag("fix_domainkey");

	log_cleanup("Set Watchdog defaults");
	watchdog::addDefaultWatchdog('localhost');
	$a = null;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');
	$a['web'] = $driverapp;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'spam');
	$a['spam'] = $driverapp;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'dns');
	$a['dns'] = $driverapp;
	slave_save_db("driver", $a);

	log_cleanup("Fix MySQL root password");
	$a = null;
	fix_mysql_root_password('localhost');
	$dbadmin = new Dbadmin(null, 'localhost', "mysql___localhost");
	$dbadmin->get();
	$pass = $dbadmin->dbpassword;
	$a['mysql']['dbpassword'] = $pass;
	slave_save_db("dbadmin", $a);

	log_cleanup("Set admin contact email");
	save_admin_email();

	log_cleanup("Get Kloxo License info");
	lxshell_php("htmllib/lbin/getlicense.php");

	log_cleanup("Create database interface_template (Forced)");
	system("mysql -u kloxo -p`cat ../etc/conf/kloxo.pass` kloxo < ../file/interface/interface_template.dump");

	log_cleanup("Fix Self SSL");
	fix_self_ssl();

	/*
	 * Function duisabled. It freezes the update/cleanup process
	 * Disabled by Danny Terweij aug 22 2011
	log_cleanup("Checking freshclam (virus scanner)");
	setFreshClam();
	*/

	// Install/Update installapp if needed or remove installapp when installapp is disabled.
	// Added in Kloxo 6.1.4
	log_cleanup("Install/Update InstallApp");
	installinstallapp();

}

function fix_domainkey()
{
	$svm = new ServerMail(null, null, "localhost");
	$svm->get();
	$svm->domainkey_flag = 'on';
	$svm->setUpdateSubaction('update');
	$svm->was();
}

function fix_move_to_client()
{
	lxshell_php("../bin/fix/fixmovetoclient.php");
}

function addcustomername()
{
	lxshell_return("__path_php_path", "../bin/misc/addcustomername.php");
}

function fix_phpini()
{
	lxshell_return("__path_php_path", "../bin/fix/fixphpini.php", "--server=localhost");
}

function switchtoaliasnext()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');

	if ($driverapp !== 'lighttpd') {
		return;
	}

	lxfile_cp("../file/lighttpd/lighttpd.conf", "/etc/lighttpd/lighttpd.conf");
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
	
}

function fix_awstats()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function install_xcache()
{
    if (lxfile_exists("../etc/flag/xcache_enabled.flg")) {
		log_cleanup("xcache enabled flag found");
        if (!strpos(lxshell_output("yum","list","|","grep","php-xcache"),"installed")) {
			log_cleanup("Installing php-xcache");
            install_if_package_not_exist("php-xcache");
            // for customize?
            lxfile_cp("../file/xcache.ini", "/etc/php.d/xcache.ini");
        } else {
			log_cleanup("xcache already installed");
		}
    }
}

function fixdomainipissue()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function fixrootquota()
{
	system("setquota -u root 0 0 0 0 -a");
}

function fixtotaldiskusageplan()
{
	global $gbl, $sgbl, $login, $ghtml; 
	initProgram('admin');
	$login->loadAllObjects('resourceplan');

	$list = $login->getList('resourceplan');
	
	foreach($list as $l) {
		if (!$l->priv->totaldisk_usage || $l->priv->totaldisk_usage === '-') {
			$l->priv->totaldisk_usage = $l->priv->disk_usage;
			$l->setUpdateSubaction();
			$l->write();
		}
	}
}

function fixcmlistagain()
{
	lxshell_return("__path_php_path", "../bin/common/generatecmlist.php");
}
function fixcmlist()
{
	lxshell_return("__path_php_path", "../bin/common/generatecmlist.php");
}

function fixcgibin()
{
	lxshell_return("__path_php_path", "../bin/fix/fixcgibin.php");
}

function fixsimpledocroot()
{
	lxshell_return("__path_php_path", "../bin/fix/fixsimpldocroot.php");
}

function installSuphp()
{
	lxshell_return("__path_php_path", "../bin/misc/installsuphp.php");
}

function fixadminuser()
{
	lxshell_return("__path_php_path", "../bin/fix/fixadminuser.php");
}

function install_gd()
{
// TODO: Function can be removed (using now install_if_package_not_exist()) (6.2.x)

	global $global_dontlogshell;
	$global_dontlogshell = true;
	$ret = lxshell_return("rpm", "-q", "php-gd");
	
	if ($ret) {
		system("yum -y install php-gd");
	}
	
	$global_dontlogshell = false;
}

function fixphpinfo()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function fixdirprotectagain()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function fixdomainhomepermission()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function installgroupwareagain()
{
	//TODO: remove empty function
}

function fixservice()
{
	lxshell_return("__path_php_path", "../bin/fix/fixservice.php");
}
function fixsslca()
{
	lxshell_return("__path_php_path", "../bin/fix/fixweb.php");
}

function dirprotectfix()
{
	lxshell_return("__path_php_path", "../bin/fix/fixdirprotect.php");
}

function cronfix()
{
	lxshell_return("__path_php_path", "../bin/cronfix.php");
}

function changetoclient()
{
	global $gbl, $sgbl, $login, $ghtml; 
	system("service xinetd stop");
	lxshell_return("__path_php_path", "../bin/changetoclientlogin.phps");
	lxshell_return("__path_php_path", "../bin/misc/fixftpuserclient.phps");
	restart_service("xinetd");
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');
	createRestartFile($driverapp);
}

function fix_dns_zones()
{
	global $gbl, $sgbl, $login, $ghtml; 
	return;

	initProgram('admin');
	$flag = "__path_program_root/etc/flag/dns_zone_fix.flag";
	
	if (lxfile_exists($flag)) {
		return;
	}
	
	lxfile_touch($flag);

	$login->loadAllObjects('dns');
	$list = $login->getList('dns');

	foreach($list as $l) {
		fixupDnsRec($l);
	}
	
	$login->loadAllObjects('dnstemplate');
	$list = $login->getList('dnstemplate');
	
	foreach($list as $l) {
		fixupDnsRec($l);
	}
}

function fixupDnsRec($l)
{
	$l->dns_record_a = null;
	
	foreach($l->cn_rec_a as $k => $v) {
		$tot = new dns_record_a(null, null, "cn_$v->nname");
		$tot->ttype = "cname";
		$tot->hostname = $v->nname;
		$tot->param = $v->param;
		$l->dns_record_a["cn_$v->nname"] = $tot;
	}

	foreach($l->mx_rec_a as $k => $v) {
		$tot = new dns_record_a(null, null, "mx_$v->nname");
		$tot->ttype = "mx";
		$tot->hostname = $l->nname;
		$tot->param = $v->param;
		$tot->priority = $v->nname;
		$l->dns_record_a["mx_$v->nname"] = $tot;
	}
	
	foreach($l->ns_rec_a as $k => $v) {
		$tot = new dns_record_a(null, null, "ns_$v->nname");
		$tot->ttype = "ns";
		$tot->hostname = $v->nname;
		$tot->param = $v->nname;
		$l->dns_record_a["ns_$v->nname"] = $tot;
	}

	foreach($l->txt_rec_a as $k => $v) {
		$tot = new dns_record_a(null, null, "txt_$v->nname");
		$tot->ttype = "txt";
		$tot->hostname = $v->nname;
		$tot->param = $v->param;
		$l->dns_record_a["txt_$v->nname"] = $tot;
	}

	foreach($l->a_rec_a as $k => $v) {
		$tot = new dns_record_a(null, null, "a_$v->nname");
		$tot->ttype = "a";
		$tot->hostname = $v->nname;
		$tot->param = $v->param;
		$l->dns_record_a["a_$v->nname"] = $tot;
	}

	$l->setUpdateSubaction();
	$l->write();
}

function installinstallapp()
{
	global $gbl, $sgbl, $login, $ghtml; 

	//--- trick for no install on kloxo install process
	if (lxfile_exists("/var/cache/kloxo/kloxo-install-disableinstallapp.flg")) {
		passthru("echo 1 > /usr/local/lxlabs/kloxo/etc/flag/disableinstallapp.flg");
		return;
	}

	if ($sgbl->is_this_master()) {
		$gen = $login->getObject('general')->generalmisc_b;
		$diflag = $gen->isOn('disableinstallapp');
		dprint("Disable InstallApp flag is ON\n");
		passthru("echo 1 > /usr/local/lxlabs/kloxo/etc/flag/disableinstallapp.flg");
	} else {
		$diflag = false;
		dprint("Disable InstallApp flag is OFF\n");
		lxfile_rm("/usr/local/lxlabs/kloxo/etc/flag/disableinstallapp.flg");
	}

	if (lxfile_exists("/usr/local/lxlabs/kloxo/etc/flag/disableinstallapp.flg")) {
		dprint("InstallApp is turned off, remove InstallApp..\n");
		lxfile_rm_rec("/home/kloxo/httpd/installapp/");
		lxfile_rm_rec("/home/kloxo/httpd/installappdata/");
		system("cd /var/cache/kloxo/ ; rm -f installapp*.tar.gz;");
		return;
	}
	else {
		if (!lxfile_exists("__path_kloxo_httpd_root/installappdata")) {
			dprint("Running InstallApp data update..\n");
			installapp_data_update();
		}

		if (lfile_exists("../etc/remote_installapp")) {
			dprint("Hosting Remote InstallApp detected, remove InstallApp..\n");
			lxfile_rm_rec("/home/kloxo/httpd/installapp/");
			system("cd /var/cache/kloxo/ ; rm -f installapp*.tar.gz;");
			return;
		}

		// Line below Removed in Kloxo 6.1.4
		// return;

		dprint("Creating installapp dir\n");
		lxfile_mkdir("__path_kloxo_httpd_root/installapp");

		if (!lxfile_exists("__path_kloxo_httpd_root/installapp/wordpress")) {
			dprint("Installing/Updating InstallApp..\n");
			lxshell_php("../bin/installapp-update.phps");
		}
		return;
	}
}

function installWithVersion($path, $file, $ver = null)
{
	if (!$ver) {
		$ver = getVersionNumber(get_package_version($file));
		log_cleanup("- $file version is $ver");
	}

	lxfile_mkdir("/var/cache/kloxo");

	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		//--- WARNING: don't use filename like kloxophp_version because problem with $file_version alias
		$locverpath = "/var/cache/kloxo/$file-version";
		$locver = getVersionNumber(file_get_contents($locverpath));
		log_cleanup("- $file local copy version is $locver");		
		if (lxfile_exists("/var/cache/kloxo/$file$locver.tar.gz")) {
			log_cleanup("- Use $file version $locver local copy for installing");
			$ver = $locver;
		}
		else {
			log_cleanup("- Download and use $file version $ver for installing");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz ; wget download.lxcenter.org/download/$file$ver.tar.gz");
		}
		$DoUpdate = true;
	}
	else {
		if (!lxfile_exists("/var/cache/kloxo/$file$ver.tar.gz")) {
			log_cleanup("- Download and use $file version $ver for updating");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz ; wget download.lxcenter.org/download/$file$ver.tar.gz");
			$DoUpdate = true;
		}
		else {
			log_cleanup("- No update and stay $file at version $ver");
			$DoUpdate = false;
		}
	}
	
	$ret = null;

	if ($DoUpdate) {
		lxfile_rm_rec("$path");
		lxfile_mkdir($path);
		$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/$file$ver.tar.gz");
		if (!$ret) { return true; }
	}
	else {
		return false;
	}
}

//--- new function for replace download_thirdparty() in kloxo/httpdocs/htmllib/lib/lib.php
function installThirdparty($ver = null)
{
	global $sgbl;

	$prgm = $sgbl->__var_program_name;

	if (!$ver) {
		$ver = file_get_contents("http://download.lxcenter.org/download/thirdparty/$prgm-version.list");
		$ver = getVersionNumber($ver);
		log_cleanup("- $prgm-thirdparty version is $ver");
	}

	$path = "/usr/local/lxlabs/$prgm";

	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		$locverpath = "/var/cache/kloxo/$prgm-thirdparty-version";
		$locver = getVersionNumber(file_get_contents($locverpath));
		log_cleanup("- $prgm-thirdparty local copy version is $locver");
		if (lxfile_exists("/var/cache/kloxo/$prgm-thirdparty.$locver.zip")) {
			log_cleanup("- Use $prgm-thirdparty version $locver local copy for installing");
			$ver = $locver;
		}
		else {
			log_cleanup("- Download and use $prgm-thirdparty version $ver for installing");
			system("cd /var/cache/kloxo/ ; rm -f $prgm-thirdparty.*.zip ; wget download.lxcenter.org/download/$prgm-thirdparty.$ver.zip");
		}
		$DoUpdate = true;
	}
	else {
		if (!lxfile_exists("/var/cache/kloxo/$prgm-thirdparty.$ver.zip")) {
			log_cleanup("- Download and use $prgm-thirdparty version $ver for updating");
			system("cd /var/cache/kloxo/ ; rm -f $prgm-thirdparty.*.zip ; wget download.lxcenter.org/download/$prgm-thirdparty.$ver.zip");
			$DoUpdate = true;
		}
		else {
			log_cleanup("- No update and stay at $prgm-thirdparty version $ver");
			$DoUpdate = false;
		}
	}

	$ret = null;

	if ($DoUpdate) {
		$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/$prgm-thirdparty.$ver.zip");
		lxfile_unix_chmod("/usr/local/lxlabs/$prgm/httpdocs/thirdparty/phpMyAdmin/config.inc.php","0644");
	}
	
	if (!$ret) { return true; }
}

function installWebmail($ver = null)
{
	$file = "lxwebmail";

	if (!$ver) {
		$ver = getVersionNumber(get_package_version($file));
		log_cleanup("- $file version is $ver");
	}

	lxfile_mkdir("/var/cache/kloxo");
	$path = "/home/kloxo/httpd/webmail";
	lxfile_mkdir($path);

	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		$locverpath = "/var/cache/kloxo/$file-version";
		$locver = getVersionNumber(file_get_contents($locverpath));
		log_cleanup("- $file local copy version is $locver");
		if (lxfile_exists("/var/cache/kloxo/$file$locver.tar.gz")) {
			log_cleanup("- Use $file version $locver local copy for installing");
			$ver = $locver;
		}
		else {
			log_cleanup("- Download and use $file version $ver for installing");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxcenter.org/download/$file$ver.tar.gz");
		}
		$DoUpdate = true;
	}
	else {
		if (!lxfile_exists("/var/cache/kloxo/$file$ver.tar.gz")) {
			log_cleanup("- Download and use $file version $ver for updating");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxcenter.org/download/$file$ver.tar.gz");
			$DoUpdate = true;
		}
		else {
			log_cleanup("- No update and stay at $file version $ver");
			$DoUpdate = false;
		}
	}

	$ret = null;

	if ($DoUpdate) {
		$tfile_h = lx_tmp_file("hordeconf");
		$tfile_r = lx_tmp_file("roundcubeconf");
		if (lxfile_exists("$path/horde/config/conf.php")) {
			lxfile_cp("$path/horde/config/conf.php", $tfile_h);
		}
		if (lxfile_exists("$path/roundcube/config/db.inc.php")) {
			lxfile_cp("$path/roundcube/config/db.inc.php", $tfile_r);
		}
		lxfile_rm_rec("$path/horde");
		lxfile_rm_rec("$path/roundcube");
		$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/$file$ver.tar.gz");
		lxfile_cp($tfile_h, "$path/horde/config/conf.php");
		lxfile_cp($tfile_r, "$path/roundcube/config/db.inc.php");
		lxfile_rm($tfile_h);
		lxfile_rm($tfile_r);
	}

	if (!$ret) { return true; }
}

function installAwstats($ver = null)
{

	$file = "lxawstats";

	if (!$ver) {
		$ver = getVersionNumber(get_package_version($file));
		log_cleanup("- $file version is $ver");
	}

	lxfile_mkdir("/var/cache/kloxo");
	lxfile_mkdir("/home/kloxo/httpd/awstats/");
	
	$path = "/home/kloxo/httpd/awstats";

	if (lxfile_exists("/var/cache/kloxo/kloxo-install-firsttime.flg")) {
		$locverpath = "/var/cache/kloxo/$file-version";
		$locver = getVersionNumber(file_get_contents($locverpath));
		log_cleanup("- $file local copy version is $locver");
		if (lxfile_exists("/var/cache/kloxo/$file$locver.tar.gz")) {
			log_cleanup("- Use $file version $locver local copy for installing");
			$ver = $locver;
		}
		else {
			log_cleanup("- Download and use $file version $ver for installing");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxcenter.org/download/$file$ver.tar.gz");
		}
		$DoUpdate = true;
	}
	else {
		if (!lxfile_exists("/var/cache/kloxo/$file$ver.tar.gz")) {
			log_cleanup("- Download and use $file version $ver for updating");
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxcenter.org/download/$file$ver.tar.gz");
			$DoUpdate = true;
		}
		else {
			log_cleanup("- No update and stay at $file version $ver");
			$DoUpdate = false;
		}
	}

	$ret = null;

	if ($DoUpdate) {
		lxfile_rm_rec("$path/tools/");
		lxfile_rm_rec("$path/wwwroot/");
		$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/$file$ver.tar.gz");
	}

	if (!$ret) { return true; }
}

// new function for set default pages
function setDefaultPages()
{
	log_cleanup("Initialize some skeletons");

	$httpdpath = "/home/kloxo/httpd";

	$sourcezip = realpath("../file/skeleton.zip");
	$targetzip = "$httpdpath/skeleton.zip";

	if (file_exists($sourcezip)) {
		if (!checkIdenticalFile($sourcezip, $targetzip)) {

			echo shell_exec("cp -rf $sourcezip $targetzip");

			log_cleanup("- Initialize skeleton (Default web page)");
			lxfile_mkdir("/home/kloxo/httpd/default");
			lxshell_unzip("__system__", "$httpdpath/default/", $targetzip);
			if (!lxfile_exists("$httpdpath/default/inc.php")) {
				lxfile_cp("../file/default_inc.php", "$httpdpath/default/inc.php");
			}
			lxfile_cp("../file/default_index.php", "$httpdpath/default/index.php");
			passthru("chown -R lxlabs:lxlabs $httpdpath/default/");
			passthru("find $httpdpath/default/ -type f -name \"*.php*\" -exec chmod 644 {} \;");
			passthru("find $httpdpath/default/ -type d -exec chmod 755 {} \;");

			log_cleanup("- Initialize skeleton (Disable web page)");
			lxfile_mkdir("$httpdpath/disable");
			lxshell_unzip("__system__", "$httpdpath/disable/", $targetzip);
			if (!lxfile_exists("$httpdpath/disable/inc.php")) {
				lxfile_cp("../file/disable_inc.php", "$httpdpath/disable/inc.php");
			}
			lxfile_cp("../file/default_index.php", "$httpdpath/disable/index.php");
			passthru("chown -R lxlabs:lxlabs $httpdpath/disable/");
			passthru("find $httpdpath/disable/ -type f -name \"*.php*\" -exec chmod 644 {} \;");
			passthru("find $httpdpath/disable/ -type d -exec chmod 755 {} \;");
	
			log_cleanup("- Initialize skeleton (Webmail web page)");
			lxfile_mkdir("$httpdpath/webmail");
			lxshell_unzip("__system__", "$httpdpath/webmail/", $targetzip);
			if (!lxfile_exists("$httpdpath/webmail/inc.php")) {
				lxfile_cp("../file/webmail_inc.php", "$httpdpath/webmail/inc.php");
			}
			lxfile_cp("../file/default_index.php", "$httpdpath/webmail/index.php");
			passthru("chown -R lxlabs:lxlabs $httpdpath/webmail/");
			passthru("find $httpdpath/webmail/ -type f -name \"*.php*\" -exec chmod 644 {} \;");
			passthru("find $httpdpath/webmail/ -type d -exec chmod 755 {} \;");

			//--- issue #597 - Use cp. to redirect :7778 or :7777
			log_cleanup("- Initialize skeleton (CP web page)");
			lxfile_mkdir("$httpdpath/cp");
			lxshell_unzip("__system__", "$httpdpath/cp/", $targetzip);
			if (!lxfile_exists("$httpdpath/cp/inc.php")) {
				lxfile_cp("../file/cp_config_inc.php", "$httpdpath/cp/inc.php");
			}
			lxfile_cp("../file/default_index.php", "$httpdpath/cp/index.php");
			passthru("chown -R lxlabs:lxlabs $httpdpath/cp/");
			passthru("find $httpdpath/cp/ -type f -name \"*.php*\" -exec chmod 644 {} \;");
			passthru("find $httpdpath/cp/ -type d -exec chmod 755 {} \;");	
		}
		else {
			log_cleanup("- No initialize skeleton (already exist)");
		}
	}
	else {
		log_cleanup("- No initialize user-logo (no exist) - MUST exist!");
	}

	$usersourcezip = realpath("../file/user-skeleton.zip");
	$usertargetzip = "/home/kloxo/user-httpd/user-skeleton.zip";

	if (lxfile_exists($usersourcezip)) {
		if (!checkIdenticalFile($usersourcezip, $usertargetzip)) {
			log_cleanup("- Initialize user-skeleton");
			passthru("cp -rf $usersourcezip $usertargetzip");
		}
		else {
			log_cleanup("- No initialize user-skeleton (already exist)");
		}
	}
	else {
		log_cleanup("- No initialize user-skeleton (no exist)");
	}

	$sourcelogo = realpath("../file/user-logo.png");
	$targetlogo = "$httpdpath/user-logo.png";

	if (lxfile_exists($sourcelogo)) {
		if (!checkIdenticalFile($sourcelogo, $targetlogo)) {
			log_cleanup("- Initialize user-logo");
			lxfile_cp($sourcelogo, $targetlogo);
			lxfile_cp($targetlogo, "$httpdpath/default/images/logo.png");
			lxfile_cp($targetlogo, "$httpdpath/disable/images/logo.png");
			lxfile_cp($targetlogo, "$httpdpath/webmail/images/logo.png");
			lxfile_cp($targetlogo, "$httpdpath/cp/images/logo.png");
		}
		else {
			log_cleanup("- No initialize user-logo (already exist)");
		}
	}
	else {
		log_cleanup("- No initialize user-logo (no exist)");
	}

/* --- pending
	log_cleanup("Initialize skeleton (Login web page)");
	lxshell_unzip("__system__", "/usr/local/lxlabs/kloxo/httpdocs/login", "../file/skeleton.zip");
	lxfile_cp("../file/login_index.php", "/usr/local/lxlabs/kloxo/httpdocs/login/index.php");
	lxfile_unix_chown("/usr/local/lxlabs/kloxo/httpdocs/login/index.php", "lxlabs:lxlabs");
	lxfile_unix_chmod("/usr/local/lxlabs/kloxo/httpdocs/login/index.php", "0644");
--- */
}

// new function
function setFreshClam()
{
	// EXPERIMENTAL (6.2.x code)
	// not called by default until the problem is fixed.
	if (!isOn(db_get_value("servermail", "localhost", "virus_scan_flag")))
	{
		passthru("chkconfig freshclam off > /dev/null 2>&1");
		passthru("service freshclam stop >/dev/null 2>&1");
		log_cleanup("Disabled freshclam service\n");
		// Stop clamd
		passthru("svc -d /var/qmail/supervise/clamd /var/qmail/supervise/clamd/log > /dev/null 2>&1");
		passthru("mv -f /var/qmail/supervise/clamd/run /var/qmail/supervise/clamd/run.stop > /dev/null 2>&1");
		passthru("mv -f /var/qmail/supervise/clamd/log/run /var/qmail/supervise/clamd/log/run.stop > /dev/null 2>&1");
		passthru("service qmail restart");
	} else {
		passthru("chkconfig freshclam on > /dev/null 2>&1");
		passthru("service freshclam start >/dev/null 2>&1");
		log_cleanup("Enabled freshclam service\n");
		// Start clamd
		passthru("svc -u /var/qmail/supervise/clamd /var/qmail/supervise/clamd/log > /dev/null 2>&1");
		passthru("mv -f /var/qmail/supervise/clamd/run.stop /var/qmail/supervise/clamd/run > /dev/null 2>&1");
		passthru("mv -f /var/qmail/supervise/clamd/log/run.stop /var/qmail/supervise/clamd/log/run > /dev/null 2>&1");
		passthru("service qmail restart");
	}
}

// solve imap problem when running webmail on 64bit
// thanks semir - http://forum.lxcenter.org/index.php?t=msg&th=14394&goto=79404&#msg_79404
function changeMailSoftlimit()
{
	log_cleanup("Change softlimit for imap4 and pop3");

	log_cleanup("- Change softlimit for imap4");
	$imap4file = "/var/qmail/supervise/imap4/run";
	$imap4content = file_get_contents($imap4file);
	$imap4content = str_replace("9000000", "18000000", $imap4content);
	lfile_put_contents($imap4file, $imap4content);

	log_cleanup("- Change softlimit for imap4-ssl");
	$imap4sslfile = "/var/qmail/supervise/imap4-ssl/run";
	$imap4sslcontent = file_get_contents($imap4sslfile);
	$imap4sslcontent = str_replace("9000000", "18000000", $imap4sslcontent);
	lfile_put_contents($imap4sslfile, $imap4sslcontent);

	log_cleanup("- Change softlimit for pop3");
	$pop3file = "/var/qmail/supervise/pop3/run";
	$pop3content = file_get_contents($pop3file);
	$pop3content = str_replace("9000000", "18000000", $pop3content);
	lfile_put_contents($pop3file, $pop3content);

	log_cleanup("- Change softlimit for pop3-ssl");
	$pop3sslfile = "/var/qmail/supervise/pop3-ssl/run";
	$pop3sslcontent = file_get_contents($pop3sslfile);
	$pop3sslcontent = str_replace("9000000", "18000000", $pop3sslcontent);
	lfile_put_contents($pop3sslfile, $pop3sslcontent);

	passthru("/etc/init.d/qmail restart");
}

function restart_xinetd_for_pureftp()
{
	createRestartFile("xinetd");
}

function install_bogofilter()
{
	$dir = "/var/bogofilter";
	$wordlist = "$dir/wordlist.db";
	$kloxo_wordlist = "$dir/kloxo.wordlist.db";

	if (lxfile_exists($kloxo_wordlist)) {
		return;
	}
	lxfile_mkdir($dir);

	lxfile_rm($wordlist);
	$content = file_get_contents("http://download.lxcenter.org/download/wordlist.db");
	file_put_contents($wordlist, $content);
	lxfile_unix_chown_rec($dir, "lxpopuser:lxpopgroup");
	lxfile_cp($wordlist, $kloxo_wordlist);
}

function removeOtherDriver()
{
	$list = array("web", "spam", "dns");
	foreach($list as $l) {
		$driverapp = slave_get_driver($l);
		if (!$driverapp) { continue; }
		$otherlist = get_other_driver($l, $driverapp);
		if ($otherlist) {
			foreach($otherlist as $o) {
				if (class_exists("{$l}__$o")) {
					exec_class_method("{$l}__$o", "uninstallMe");
				}
			}
		}
	}
}

function updateApplicableToSlaveToo()
{
	// Fixes #303 and #304
	installThirdparty();

	os_updateApplicableToSlaveToo();
	
	setDefaultPages();
}

function fix_secure_log()
{
	lxfile_mv("/var/log/secure", "/var/log/secure.lxback");
	lxfile_cp("../file/linux/syslog.conf", "/etc/syslog.conf");
	createRestartFile('syslog');
}

function fix_cname()
{
	lxshell_return("__path_php_path", "../bin/fix/fixdns.php");
}

function installChooser()
{
	$path = "/home/kloxo/httpd/webmail/";
	lxfile_mkdir("/home/kloxo/httpd/webmail/img");
	lxfile_cp_rec("../file/webmail-chooser/header/", "/home/kloxo/httpd/webmail/img");
	lxfile_cp("../file/webmail-chooser/webmail_chooser.phps", "/home/kloxo/httpd/webmail/index.php");
	lxfile_cp("../file/webmail-chooser/roundcube-config.phps", "/home/kloxo/httpd/webmail/roundcube/config/main.inc.php");
	$list = array("horde", "roundcube");
	foreach($list as $l) {
		lfile_put_contents("$path/redirect-to-$l.php", "<?php\nheader(\"Location: /$l\");\n");
	}
	lfile_put_contents("$path/disabled/index.html", "Disabled\n");
}

function installRoundCube()
{
	global $sgbl;

	$path_webmail = "$sgbl->__path_kloxo_httpd_root/webmail";
	$path_roundcube = "$sgbl->__path_kloxo_httpd_root/webmail/roundcube";

	PrepareRoundCubeDb();

	if (lxfile_exists($path_webmail)) {
		lxfile_generic_chown_rec($path_webmail, 'lxlabs:lxlabs');
		lxfile_generic_chown_rec("$path_roundcube/logs", 'apache:apache');
		lxfile_generic_chown_rec("$path_roundcube/temp", 'apache:apache');
		lxfile_rm('/var/cache/kloxo/roundcube.log');
	}
}

function installHorde()
{
	global $sgbl;

	$path_webmail = "$sgbl->__path_kloxo_httpd_root/webmail";
	$path_horde = "$sgbl->__path_kloxo_httpd_root/webmail/horde";

	PrepareHordeDb();

	if (lxfile_exists($path_webmail)) {
		lxfile_generic_chown_rec($path_webmail, 'lxlabs:lxlabs');
		lxfile_generic_chown_rec("$path_horde/logs", 'apache:apache');
		lxfile_generic_chown_rec("$path_horde/temp", 'apache:apache');
		lxfile_rm('/var/cache/kloxo/horde.log');
	}
}

function fix_suexec()
{
	lxfile_rm("/usr/bin/lxsuexec");
	lxfile_rm("/usr/bin/lxexec");
	lxfile_cp("../cexe/lxsuexec", "/usr/bin");
	lxfile_cp("../cexe/lxexec", "/usr/bin");
	lxshell_return("chmod", "755", "/usr/bin/lxsuexec");
	lxshell_return("chmod", "755", "/usr/bin/lxexec");
	lxshell_return("chmod", "ug+s", "/usr/bin/lxsuexec");
}

function enable_xinetd()
{
	createRestartFile("qmail");
	@ system("service pure-ftpd stop");
	createRestartFile("xinetd");
}

function fix_mailaccount_only()
{
	global $gbl, $sgbl, $login, $ghtml; 
	lxfile_unix_chown_rec("/var/bogofilter", "lxpopuser:lxpopgroup");
	$login->loadAllObjects('mailaccount');
	$list = $login->getList('mailaccount');
	foreach($list as $l) {
		$l->setUpdateSubaction('full_update');
		$l->was();
	}
}

function change_spam_to_bogofilter_next_next()
{
	global $gbl, $sgbl, $login, $ghtml; 
	system("rpm -e --nodeps spamassassin");
	system("yum -y install bogofilter");

	$drv = $login->getFromList('pserver', 'localhost')->getObject('driver');
	$drv->driver_b->pg_spam = 'bogofilter';
	$drv->setUpdateSubaction();
	$drv->write();

	$login->loadAllObjects('mailaccount');
	$list = $login->getList('mailaccount');
	foreach($list as $l) {
		$s = $l->getObject('spam');
		$s->setUpdateSubaction('update');
		$s->was();
		$l->setUpdateSubaction('full_update');
		$l->was();
	}
}

function fix_mysql_name_problem()
{
	$sq = new Sqlite(null, 'mysqldb');
	$res = $sq->getTable();

	foreach($res as $r) {
		if (!csa($r['nname'], "___")) {
			return;
		}
		$sq->rawQuery("update mysqldb set nname = '{$r['dbname']}' where dbname = '{$r['dbname']}'");
	}
}

function fix_mysql_username_problem()
{
	$sq = new Sqlite(null, 'mysqldbuser');
	$res = $sq->getTable();

	foreach($res as $r) {
		if (!csa($r['nname'], "___")) {
			return;
		}
		$sq->rawQuery("update mysqldbuser set nname = '{$r['username']}' where username = '{$r['username']}'");
	}
}

function add_domain_backup_dir()
{
	lxfile_generic_chown("__path_program_home/domain", "lxlabs");
	if (lxfile_exists("__path_program_home/domain")) {
		dprint("Domain backupdir exists... returning\n");
		return;
	}

	$sq = new Sqlite(null, 'domain');

	$res = $sq->getTable(array('nname'));
	foreach($res as $r) {
		lxfile_mkdir("__path_program_home/domain/{$r['nname']}/__backup");
		lxfile_generic_chown("__path_program_home/domain/{$r['nname']}/", "lxlabs");
		lxfile_generic_chown("__path_program_home/domain/{$r['nname']}/__backup", "lxlabs");
	}
}

function changeColumn($tbl_name, $changelist)
{
	dprint("Changing Column.............\n");
	$db = new Sqlite($tbl_name);
	$columnold  = $db->getColumnTypes();
	$oldcolumns = array_keys($columnold);
	$conlist = array_flip($changelist);
	$query= "select * from" . " " . $tbl_name;
	$res =$db->rawQuery($query);
	
	foreach($columnold as $l) {
		$check = array_search($l , $conlist);
		if($check) {
			$newcollist[] = $changelist[$l];
		}
		else {
			$newcollist[] = $l;
		}
	}
	$newfields = implode(",", $newcollist);
	changeValues($res, $tbl_name, $db, $newfields);
}

function changeValues($res, $tbl_name, $db, $newfields)
{

	dprint("$newfields");
	dprint("\n\n");
	$query = "create table lxt_" . $tbl_name . "(" . $newfields . ")";
	$db->rawQuery($query);
	
	foreach($res as $r) {
		$newtemp  = ""; 
		foreach($r as $r1) {
			$newtemp[] = "'" . $r1 . "'";
		}
		$t = implode("," , $newtemp);
		$db->rawQuery("insert into lxt_" . $tbl_name . " values" . "(" . $t . ")");
	}
	$db->rawQuery("drop table " . $tbl_name );
	$db->rawQuery("create table " .  $tbl_name . " as select * from lxt_" . "$tbl_name");
	$db->rawQuery("drop table lxt_" . $tbl_name );
	dprint("Table Information of $tbl_name  Updated with New Fields\n\n");
}

function droptable($tbl_name) 
{ 
	dprint("Dropping table...............\n");
	$db = new Sqlite($tbl_name);
	$db->rawQuery("drop table " . $tbl_name );
}

function dropcolumn($tbl_name, $column) 
{
	dprint("Dropping Column...............\n");

	$db = new Sqlite($tbl_name);
	$columnold  = $db->getColumnTypes();
	$oldcolumns = array_keys($columnold);

	foreach($oldcolumns as $key=>$l) {
		$t= array_search(trim($l), $column);
		if(!empty($t)) {
			dprint("value $oldcolumns[$key] has deleted\n"); 
			unset($oldcolumns[$key]);
		}else {
			$newcollist[] = $l;
		}
	}
	$newfields = implode("," , $newcollist);
	dprint("New fields are \n");
	$query= "select " . $newfields . " from" . " " . $tbl_name;
	$res =$db->rawQuery($query);
	changeValues($res, $tbl_name, $db, $newfields);
}

function getTabledetails($tbl_name){

	dprint("table. values are ..........\n");
	$db = new Sqlite($tbl_name);
	$res =  $db->rawQuery("select * from " . $tbl_name );
	print_r($res);
}

function construct_uuser_nname($list)
{
	global $gbl, $sgbl, $login, $ghtml; 
	return $list['nname'] . $sgbl->__var_nname_impstr . $list['servername'];
}

function getVersionNumber($ver)
{
		$ver = trim($ver);
		$ver = str_replace("\n", "", $ver);
		$ver = str_replace("\r", "", $ver);
		return $ver;
}

// ref: http://ideone.com/JWKIf
function is_64bit()
{
	$int = "9223372036854775807";
	$int = intval($int);

	if ($int == 9223372036854775807) {
		return true; /* 64bit */
	}
	elseif ($int == 2147483647) {
		return false; /* 32bit */
	}
	else {
		return "error"; /* error */
	}
}

function checkIdenticalFile($file1, $file2)
{
	$ret = false;

	if (!file_exists($file1)) {
		return false;
	}

	if (!file_exists($file2)) {
		return false;
	}	

	if (filesize($file1) === filesize($file2)) {
		$ret = true;
	}
	else {
		return false;
	}

	if (md5_file($file1) === md5_file($file2)) {
		$ret = true;
	}
	else {
		return false;
	}

	return $ret;
}
