<?php 

class serverweb__apache extends lxDriverClass {

function dbactionUpdate($subaction)
{
	// issue #571 - add httpd-worker and httpd-event for suphp
	// issue #566 - Mod_ruid2 on Kloxo
	// issue #567 - httpd-itk for kloxo
	
//	lxshell_return("service", "httpd", "stop");
// 	system("/etc/init.d/httpd stop");

	$ret = lxshell_return("service", "httpd", "stop");
	if ($ret) { throw new lxexception('httpd_stop_failed', 'parent'); }

	//-- old structure
	system("rm -rf /etc/httpd/conf/kloxo");
	system("rm -rf /home/httpd/conf");
	
	//-- new structure	
	lxfile_mkdir("/home/apache/conf");
	lxfile_mkdir("/home/apache/conf/defaults");
	lxfile_mkdir("/home/apache/conf/domains");
	lxfile_mkdir("/home/apache/conf/redirects");
	lxfile_mkdir("/home/apache/conf/webmails");
	lxfile_mkdir("/home/apache/conf/wildcards");
	lxfile_mkdir("/home/apache/conf/exclusive");
	
	//--- some vps include /etc/httpd/conf.d/swtune.conf
	system("rm -f /etc/httpd/conf.d/swtune.conf");

	if (!lfile_exists("/etc/httpd/conf.d/~lxcenter.conf")) {
		copy("/usr/local/lxlabs/kloxo/file/apache/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
		copy("/usr/local/lxlabs/kloxo/file/centos-5/httpd.conf", "/etc/httpd/conf/httpd.conf");
	}

	lxfile_rm("/etc/sysconfig/httpd");

	$t = $this->main->php_type;

	$a = $this->main->apache_optimize;
	$m = $this->main->mysql_convert;
	$f = $this->main->fix_chownchmod;

	if ($f === 'fix-ownership') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=chown");
	//	setFixChownChmod('chown');
	}
	else if ($f === 'fix-permissions') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=chmod");
	//	setFixChownChmod('chmod');
	}
	else if ($f === 'fix-ALL') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-chownchmod.php --select=all");
	//	setFixChownChmod('all');
	}
	
	if ($m === 'to-myisam') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/mysql-convert.php --engine=myisam");
	//	setMysqlConvert('myisam');
	}
	else if ($m === 'to-innodb') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/mysql-convert.php --engine=innodb");
	//	setMysqlConvert('innodb');
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
			system("yum -y update mod_ruid2");
			lxfile_mv("/etc/httpd/conf.d/ruid2.nonconf", "/etc/httpd/conf.d/ruid2.conf");
			lxfile_cp("../file/ruid2.conf", "/etc/httpd/conf.d/ruid2.conf");
		}
		else if ($t === 'mod_php_itk') {
			system("echo 'HTTPD=/usr/sbin/httpd.itk' >/etc/sysconfig/httpd");
		}
	}
	else if (strpos($t, 'suphp') !== false) {
		system("yum -y install mod_suphp");
		system("yum -y update mod_suphp");

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
	// enough handle by fix-chownchmod
//	lxfile_generic_chmod("/home/admin", "0770");

//	change to 'stop-start' instead 'restart' because problem when change prefork/worker/event/itk to other
//	createRestartFile("httpd");

//	lxshell_return("service", "httpd", "start");
//	system("/etc/init.d/httpd start");

	$ret = lxshell_return("service", "httpd", "start");
	if ($ret) { throw new lxexception('httpd_start_failed', 'parent'); }

	if ($a === 'optimize') {
		system("lphp.exe /usr/local/lxlabs/kloxo/bin/fix/apache-optimize.php --select=optimize");
	}

}

}
