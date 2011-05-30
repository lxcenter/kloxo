<?php 

function update_main()
{
	global $argc, $argv;
	global $gbl, $sgbl, $login, $ghtml; 

	debug_for_backend();
	$login = new Client(null, null, 'upgrade');
	$DoUpdate = false;

	$opt = parse_opt($argv);
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

		log_cleanup("Checking for new ThirdParty package version");
		$ver = file_get_contents("http://download.lxcenter.org/download/thirdparty/kloxo-version.list");
		if ($ver != "") {
        	$ver = trim($ver);
        	$ver = str_replace("\n", "", $ver);
        	$ver = str_replace("\r", "", $ver);
			if (!lxfile_real("/var/cache/kloxo/kloxo-thirdparty.$ver.zip")) {
				$DoUpdate = true;
				log_cleanup("Found a new ThirdParty version ($ver)");
			} else {
				log_cleanup("No new ThirdParty version found (Current version $ver)");
			}
		}

		log_cleanup("Checking for new WebMail package version");
		$ver = get_package_version("lxwebmail");
		if (!lxfile_real("/var/cache/kloxo/lxwebmail$ver.tar.gz")) {
			$DoUpdate = true;
			log_cleanup("Found a new WebMail version ($ver)");
		}  else {
			log_cleanup("No new Webmail version found (Current version $ver)");
		}

		if ( $DoUpdate == false ) {
			print("Run /script/cleanup if you want to fix/restore/(re)install non working components.\n");
			exit;
		}
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
	download_thirdparty();

	log_cleanup("Check for GD");
	install_gd();

	log_cleanup("Check for bogofilter");
	install_bogofilter();

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
	

	call_with_flag('installgroupwareagain');

	log_cleanup("Initialize OS admin account description");
	$desc = uuser::getUserDescription('admin');
	$list = posix_getpwnam('admin');
	if ($list && ($list['gecos'] !== $desc)) {
		lxshell_return("usermod", "-c", $desc, "admin");
	}

	log_cleanup("Initialize lxphp");
	// TODO: php six four symlink remove when lxphp 64bit is ready!
	if (os_is_php_six_four()) {
		$ver = get_package_version("kloxophpsixfour");
		installWithVersion("/usr/lib/kloxophp", "kloxophpsixfour", $ver);
		if (!lxfile_exists("/usr/lib/php")) {
			lxfile_symlink("/usr/lib64/php", "/usr/lib/php");
		}
	} else {
		$ver = get_package_version("kloxophp");
		installWithVersion("/usr/lib/kloxophp", "kloxophp", $ver);
	}

	log_cleanup("Checking WebMail");
	$ver = get_package_version("lxwebmail");
	installWebmail($ver);

	log_cleanup("Checking awstats");
	$ver = get_package_version("lxawstats");
	installAwstats($ver);

	log_cleanup("Initialize system files...");
	log_cleanup("- Install RoundCube database config...");
	lxfile_cp("../file/webmail-chooser/db.inc.phps", "/home/kloxo/httpd/webmail/roundcube/config/db.inc.php");
	log_cleanup("- Create /etc/lighttpd/conf/kloxo dir if needed...");
	lxfile_mkdir("/etc/lighttpd/conf/kloxo");
	log_cleanup("- Create /var/bogofilter dir if needed...");
	lxfile_mkdir("/var/bogofilter");
	log_cleanup("- Create /home/kloxo/httpd/lighttpd dir if needed...");
	lxfile_mkdir("/home/kloxo/httpd/lighttpd");
	log_cleanup("- Remove dir /home/admin/domain/ if exists...");
	rmdir("/home/admin/domain/");
	log_cleanup("- Remove dir /home/admin/old/ if exists...");
	rmdir("/home/admin/old/");
	log_cleanup("- Remove dir /home/admin/cgi-bin/ if exists...");
	rmdir("/home/admin/cgi-bin/");
	log_cleanup("- Remove dir /etc/skel/Maildir/ if exists...");
	rmdir("/etc/skel/Maildir/new");
	rmdir("/etc/skel/Maildir/cur");
	rmdir("/etc/skel/Maildir/tmp");
	rmdir("/etc/skel/Maildir/");

	log_cleanup("- Install lxrestart binary...");
	system("cp ../cexe/lxrestart /usr/sbin/");
	system("chown root:root /usr/sbin/lxrestart");
	system("chmod 755 /usr/sbin/lxrestart");
	system("chmod ug+s /usr/sbin/lxrestart");

	log_cleanup("- Remove sendmail binary...");
	lunlink("/usr/sbin/sendmail");
	lunlink("/usr/lib/sendmail");
	log_cleanup("- Install qmail-sendmail binary...");
	lxfile_cp("../file/linux/qmail-sendmail", "/usr/sbin/sendmail");
	lxfile_cp("../file/linux/qmail-sendmail", "/usr/lib/sendmail");
	lxfile_unix_chmod("/usr/lib/sendmail", "0755");
	lxfile_unix_chmod("/usr/sbin/sendmail", "0755");

	log_cleanup("- Install lxredirector binary...");
	system("cp ../file/linux/lxredirecter.sh /usr/bin/");
	system("chmod 755 /usr/bin/lxredirecter.sh");

	if (!lxfile_exists("/usr/bin/php-cgi")) {
		log_cleanup("- Install php-cgi binary...");
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}

	if (!lxfile_exists("/usr/local/bin/php")) {
		log_cleanup("- Create Symlink /usr/bin/php to /usr/local/bin/php...");
		lxfile_symlink("/usr/bin/php", "/usr/local/bin/php");
	}
	if (lxfile_exists('kloxo.sql')) {
		log_cleanup("- Remove file kloxo.sql...");
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

	log_cleanup("Turn off mouse daemon");
	system("chkconfig gpm off");

	log_cleanup("Remove phpinfo.php");
	lxfile_rm("phpinfo.php");

	log_cleanup("Initialize Kloxo bind config files");
	lxfile_touch("/var/named/chroot/etc/kloxo.named.conf");
	lxfile_touch("/var/named/chroot/etc/global.options.named.conf");

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

	log_cleanup("Initialize /script/ dir");
	copy_script();

	log_cleanup("Install xcache if enabled");
	install_xcache();

	log_cleanup("Install Kloxo service");
	lxfile_unix_chmod("/etc/init.d/kloxo", "0755");
	system("chkconfig kloxo on");

	log_cleanup("Installing jailshell to system");
	addLineIfNotExistInside("/etc/shells", "/usr/bin/lxjailshell", "");
	lxfile_cp("htmllib/filecore/execzsh.sh", "/usr/bin/execzsh.sh");
	lxfile_unix_chmod("/usr/bin/execzsh.sh", "0755");

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

	log_cleanup("Install/Fix Services/Permissions/Configfiles...");
	log_cleanup("- Create lxphp.exe Symlink...");
	lxfile_symlink("__path_php_path", "/usr/bin/lxphp.exe");
	log_cleanup("- Install kloxo.conf...");
	lxfile_cp("../file/apache/kloxo.conf", "/etc/httpd/conf/kloxo/kloxo.conf");
	log_cleanup("- Install ssl.conf...");
	lxfile_cp("../file/apache/default_ssl.conf", "/etc/httpd/conf.d/ssl.conf");
	log_cleanup("- Initialize webmail_redirect.conf...");
	lxfile_touch("/etc/httpd/conf/kloxo/webmail_redirect.conf");
	log_cleanup("- Initialize ssl.conf...");
	lxfile_touch("/etc/httpd/conf/kloxo/ssl.conf");
	log_cleanup("- Initialize default.conf...");
	lxfile_touch("/etc/httpd/conf/kloxo/default.conf");
	log_cleanup("- Initialize cp_config.conf...");
	lxfile_touch("/etc/httpd/conf/kloxo/cp_config.conf");
	log_cleanup("- Remove /etc/init.d/pure-ftpd service file...");
	@lxfile_rm("/etc/init.d/pure-ftpd");
	if (!lxfile_exists("/etc/xinetd.d/pureftp")) {
		log_cleanup("- Install /etc/xinetd.d/pureftp TCP Wrapper file...");
		lxfile_cp("../file/xinetd.pureftp", "/etc/xinetd.d/pureftp");
	}

	if(!lxfile_real("/etc/pki/pure-ftpd/pure-ftpd.pem")) {
		log_cleanup("- Install pure-ftpd ssl/tls key...");
		lxfile_mkdir("/etc/pki/pure-ftpd/");
		lxfile_cp("../file/program.pem", "/etc/pki/pure-ftpd/pure-ftpd.pem");
	}

	if (!lxfile_exists("/etc/xinetd.d/smtp_lxa")) {
		log_cleanup("- Install xinetd smtp_lxa SMTP TCP Wrapper...");
		lxfile_cp("../file/xinetd.smtp_lxa", "/etc/xinetd.d/smtp_lxa");
	}

	log_cleanup("- Remove /etc/xinetd.d/pure-ftpd TCP Wrapper file...");
	@lxfile_rm("/etc/xinetd.d/pure-ftpd");

	log_cleanup("- Install qmail service...");
	lxfile_cp("../file/qmail.init", "/etc/init.d/qmail");
	lxfile_unix_chmod("/etc/init.d/qmail", "0755");

	log_cleanup("- Install /etc/lxrestricted file (lxjailshell commands restrictions)...");
	lxfile_cp("../file/lxrestricted", "/etc/lxrestricted");

	log_cleanup("- Install /etc/sysconfig/spamassassin ...");
	lxfile_cp("../file/sysconfig_spamassassin", "/etc/sysconfig/spamassassin");

	$name = trim(lfile_get_contents("/var/qmail/control/me"));
	log_cleanup("- Install qmail defaultdomain and defaulthost (" . $name . ") ...");
	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaultdomain");
	lxfile_cp("/var/qmail/control/me", "/var/qmail/control/defaulthost");
	log_cleanup("- Install qmail SMTP Greeting (" . $name . " - Welcome to Qmail) ...");
	lfile_put_contents("/var/qmail/control/smtpgreeting", "$name - Welcome to Qmail");

	if (!lxfile_exists("/usr/bin/rblsmtpd")) {
		log_cleanup("- Initialize rblsmtpd binary ...");
		lxshell_return("ln", "-s", "/usr/local/bin/rblsmtpd", "/usr/bin/");
	}
	if (!lxfile_exists("/usr/bin/tcpserver")) {
		log_cleanup("- Initialize tcpserver binary ...");
		lxshell_return("ln", "-s", "/usr/local/bin/tcpserver", "/usr/bin/");
	}

	log_cleanup("- Enable xinetd service ...");
	call_with_flag("enable_xinetd");

	log_cleanup("- Fix suexec ...");
	fix_suexec();

	log_cleanup("- Restart xinetd service ...");
	call_with_flag("restart_xinetd_for_pureftp");

	if (!lxfile_exists("/usr/bin/php-cgi")) {
		log_cleanup("- Initialize php-cgi binary ...");
		lxfile_cp("/usr/bin/php", "/usr/bin/php-cgi");
	}
	log_cleanup("- Set permissions for /usr/bin/php-cgi ...");
	lxfile_unix_chmod("/usr/bin/php-cgi", "0755");
	log_cleanup("- Set permissions for closeallinput binary ...");
	lxfile_unix_chmod("../cexe/closeallinput", "0755");
	log_cleanup("- Set permissions for lxphpsu binary ...");
	lxfile_unix_chown("../cexe/lxphpsu", "root:root");
	lxfile_unix_chmod("../cexe/lxphpsu", "0755");
	lxfile_unix_chmod("../cexe/lxphpsu", "ug+s");
	log_cleanup("- Set permissions for phpsuexec.sh script ...");
	lxfile_unix_chmod("../file/phpsuexec.sh", "0755");
	log_cleanup("- Set permissions for /home/kloxo/httpd/lighttpd/ dir ...");
	system("chown -R apache:apache /home/kloxo/httpd/lighttpd/");
	log_cleanup("- Set permissions for /var/lib/php/session/ dir ...");
	system("chmod 777 /var/lib/php/session/");
	system("chmod o+t /var/lib/php/session/");
	log_cleanup("- Set permissions for /var/bogofilter/ dir ...");
	system("chmod 777 /var/bogofilter/");
	system("chmod o+t /var/bogofilter/");
	log_cleanup("- Kill sisinfoc system process ...");
	system("pkill -f sisinfoc");

	log_cleanup("- Install lighttpd.conf ...");
	lxfile_cp("../file/lighttpd/lighttpd.conf", "/etc/lighttpd/lighttpd.conf");

	log_cleanup("- Install kloxo.conf (lighttpd)...");
	lxfile_cp("../file/lighttpd/conf/kloxo/kloxo.conf", "/etc/lighttpd/conf/kloxo/kloxo.conf");

	log_cleanup("- Initialize webmail_reditrect.conf (lighttpd) ...");
	lxfile_touch("/etc/lighttpd/conf/kloxo/webmail_redirect.conf");

	if (!lxfile_real("/etc/lighttpd/local.lighttpd.conf")) {
		log_cleanup("- Initialize local.lighttpd.conf (lighttpd) ...");
		system("echo > /etc/lighttpd/local.lighttpd.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/webmail_redirect.conf")) {
		log_cleanup("- Initialize webmail.redirect.conf (lighttpd)...");
		system("echo > /etc/lighttpd/conf/kloxo/webmail_redirect.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/virtualhost.conf")) {
		log_cleanup("- Initialize virtualhost.conf (lighttpd)...");
		system("echo > /etc/lighttpd/conf/kloxo/virtualhost.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/domainip.conf")) {
		log_cleanup("- Initialize domainip.conf (lighttpd)...");
		system("echo > /etc/lighttpd/conf/kloxo/domainip.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/ssl.conf")) {
		log_cleanup("- Initialize ssl.conf (lighttpd)...");
		system("echo > /etc/lighttpd/conf/kloxo/ssl.conf");
	}
	if (!lxfile_real("/etc/lighttpd/conf/kloxo/mimetype.conf")) {
		log_cleanup("- Initialize mimetype.conf (lighttpd)...");
		system("echo > /etc/lighttpd/conf/kloxo/mimetype.conf");
	}
	log_cleanup("- Initialize domainip.conf (apache)...");
	lxfile_touch("/etc/httpd/conf/kloxo/domainip.conf");

	log_cleanup("- Initialize mimetype.conf (apache)...");
	lxfile_touch("/etc/httpd/conf/kloxo/mimetype.conf");

	log_cleanup("- Install /etc/init.d/lighttpd service file...");
	lxfile_cp("../file/lighttpd/etc_init.d", "/etc/init.d/lighttpd");

	if (!lxfile_exists("/etc/pure-ftpd/pureftpd.passwd")) {
		log_cleanup("- Initialize pure-ftpd password database...");
		lxfile_cp("/etc/pureftpd.passwd", "/etc/pure-ftpd/pureftpd.passwd");
		lxshell_return("pure-pw", "mkdb");
		createRestartFile("xinetd");
	}

	if (!lxfile_exists("../etc/flag/xcache_enabled.flg")) {
		log_cleanup("- xcache flag not found, removing /etc/php.d/xcache.ini file...");
		lunlink("/etc/php.d/xcache.ini");
	}

	log_cleanup("- Turn off pure-ftpd service...");
	system("chmod 666 /dev/null");
	@ exec("chkconfig pure-ftpd off 2>/dev/null");

	log_cleanup("- Initialize nobody.sh script...");
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

	log_cleanup("- Execute lxpopuser.sh ...");
	system("sh ../bin/misc/lxpopuser.sh");

	log_cleanup("- Remove /home/kloxo/httpd/script dir...");
	lxfile_rm_content("__path_home_root/httpd/script/");
	log_cleanup("- Initialize /home/kloxo/httpd/script dir...");
	lxfile_mkdir("/home/kloxo/httpd/script");
	lxfile_unix_chown_rec("/home/kloxo/httpd/script", "lxlabs:lxlabs");
	log_cleanup("- Install phpinfo.php into /home/kloxo/httpd/script dir...");
	lxfile_cp("../file/script/phpinfo.phps", "/home/kloxo/httpd/script/phpinfo.php");

	log_cleanup("- Install /etc/init.d/djbdns service file ...");
	lxfile_cp("../file/djbdns.init", "/etc/init.d/djbdns");

	log_cleanup("- Enable the correct drivers (Service daemons) ...");
	removeOtherDriver();

	log_cleanup("- Remove cache dir ...");
	lxfile_rm_rec("__path_program_root/cache");

	log_cleanup("- restart syslog service ...");
	createRestartFile('syslog');

	log_cleanup("- Initialize awstats dirdata ...");
	lxfile_mkdir("/home/kloxo/httpd/awstats/dirdata");

	if (!lxfile_exists("/etc/logrotate.d/kloxo")) {
		log_cleanup("- Install Kloxo Log rotate file...");
		lxfile_cp("../file/kloxo.logrotate", "/etc/logrotate.d/kloxo");
	}

	log_cleanup("Install RoundCube");
	installRoundCube();

	log_cleanup("Install Webmailchooser");
	installChooser();

	log_cleanup("Remove old lxlabs ssh key");
 	remove_ssh_self_host_key();

	if (lxfile_exists("/etc/httpd/conf/httpd.conf")) {
		log_cleanup("Add kloxo.conf directive into httpd.conf (apache)");
		addLineIfNotExistInside("/etc/httpd/conf/httpd.conf", "Include /etc/httpd/conf/kloxo/kloxo.conf", "");
	}

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

	log_cleanup("Initialize skeleton (Default web page)");
	lxfile_mkdir("__path_kloxo_httpd_root/default/");
	lxfile_cp("../file/skeleton.zip", "__path_kloxo_httpd_root/skeleton.zip");
	lxshell_unzip("__system__", "__path_kloxo_httpd_root/default/", "../file/skeleton.zip");
	lxfile_cp("../file/default_index.html", "__path_kloxo_httpd_root/default/index.html");
	lxfile_mkdir("__path_kloxo_httpd_root/disable/");
	lxfile_cp("../file/disable.html", "__path_kloxo_httpd_root/disable/index.html");
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
			print("Upgrading Slave {$l['nname']}...\n");
			rl_exec_get(null, $l['nname'], 'remotetestfunc', null);
		} catch (exception $e) {
			print($e->getMessage());
			print("\n");
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
	print("Found version(s): ");
	foreach($nlist as $l) {
		print("- $l ");
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
	log_cleanup("Downloading $programfile ...");
	download_source("/$program/$programfile");
	log_cleanup("Download Done!\n Start unzip...");
	system("cd ../../ ; unzip -o httpdocs/download/$programfile");
	chdir($saveddir);
}



