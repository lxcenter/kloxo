<?php 

class serverweb__apache extends lxDriverClass {

function dbactionUpdate($subaction)
{
	// issue #571 - add httpd-worker and httpd-event for suphp
	// issue #566 - Mod_ruid2 on Kloxo
	// issue #567 - httpd-itk for kloxo
	
	lxshell_return("service", "httpd", "stop");

	//-- old structure
	system("rm -rf /etc/httpd/conf/kloxo");

	//-- new structure	
	lxfile_mkdir("/home/httpd/conf");
	lxfile_mkdir("/home/httpd/conf/defaults");
	lxfile_mkdir("/home/httpd/conf/domains");

	lxfile_rm("/etc/sysconfig/httpd");

	$t = $this->main->php_type;

	$a = $this->main->apache_optimize;
	$m = $this->main->mysql_optimize;
	$f = $this->main->fix_chownchmod;

	if ($m === 'skip-innodb') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/mysql-skipinnodb.php --innodb=skip");
	}

	if ($a === 'optimize') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/apache-optimize.php --select=optimize");
	}

	if ($f === 'fix-ownership') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --type=chown");
	}
	else if ($f === 'fix-permissions') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --type=chmod");
	}
	else if ($f === 'fix-ALL') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --type=all");
	}

	//--- don't use '=== true' but '!== false'
	if (strpos($t, 'mod_php') !== false) {
		lxfile_mv("/etc/httpd/conf.d/php.nonconf", "/etc/httpd/conf.d/php.conf");
		lxfile_mv("/etc/httpd/conf.d/fastcgi.conf", "/etc/httpd/conf.d/fastgi.nonconf");
		lxfile_mv("/etc/httpd/conf.d/fcgid.conf", "/etc/httpd/conf.d/fcgid.nonconf");
		lxfile_mv("/etc/httpd/conf.d/ruid2.conf", "/etc/httpd/conf.d/ruid2.nonconf");
		lxfile_mv("/etc/httpd/conf.d/suphp.conf", "/etc/httpd/conf.d/suphp.nonconf");

	//	lxfile_cp("../file/httpd.prefork", "/etc/sysconfig/httpd");
	//	lxfile_rm("/etc/sysconfig/httpd");
		// use > that equal to lxfile_rm + echo >>
		system("echo 'HTTPD=/usr/sbin/httpd' >/etc/sysconfig/httpd");

		if ($t === 'mod_php') {
			// nothing
		}
		else if ($t === 'mod_php_ruid2') {
			system("yum -y install mod_ruid2");
			lxfile_mv("/etc/httpd/conf.d/ruid2.nonconf", "/etc/httpd/conf.d/ruid2.conf");
		}
		else if ($t === 'mod_php_itk') {
			system("yum -y install httpd-itk");
			lxfile_rm("/etc/httpd/conf.d/itk.conf");
			system("echo 'HTTPD=/usr/sbin/httpd.itk' >/etc/sysconfig/httpd");
		}
	}
	else if (strpos($t, 'suphp') !== false) {
		system("yum -y install mod_suphp");

		lxfile_mv("/etc/httpd/conf.d/php.conf", "/etc/httpd/conf.d/php.nonconf");
		lxfile_mv("/etc/httpd/conf.d/fastcgi.conf", "/etc/httpd/conf.d/fastgi.nonconf");
		lxfile_mv("/etc/httpd/conf.d/fcgid.conf", "/etc/httpd/conf.d/fcgid.nonconf");
		lxfile_mv("/etc/httpd/conf.d/ruid2.conf", "/etc/httpd/conf.d/ruid2.nonconf");
		lxfile_mv("/etc/httpd/conf.d/suphp.nonconf", "/etc/httpd/conf.d/suphp.conf");

		lxfile_cp("../file/suphp.conf", "/etc/httpd/conf.d/suphp.conf");
		lxfile_cp("../file/etc_suphp.conf", "/etc/suphp.conf");

//		lxfile_rm("/etc/sysconfig/httpd");

		if ($t === 'suphp') {
			system("echo 'HTTPD=/usr/sbin/httpd' >/etc/sysconfig/httpd");
		}
		else if ($t === 'suphp_worker') {
			system("echo 'HTTPD=/usr/sbin/httpd.worker' >/etc/sysconfig/httpd");
		}
		else if ($t === 'suphp_event') {
			system("echo 'HTTPD=/usr/sbin/httpd.event' >/etc/sysconfig/httpd");
		}
	}
	else if (strpos($t, 'suexec') !== false) {
		// work in progress...
	}

	//--- change to ~lxcenter.conf from the first idea
//	lxfile_cp("../file/mpm.conf", "/etc/httpd/conf.d/mpm.conf");

	// Fixed issue #515 - returned due to accidentally deleted
	lxfile_generic_chmod("/home/admin", "0770");

//	change to 'stop-start' instead 'restart' because problem when change prefork/worker/event/itk to other
//	createRestartFile("httpd");

	lxshell_return("service", "httpd", "start");
}

}

