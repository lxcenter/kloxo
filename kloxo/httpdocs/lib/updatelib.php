<?php 


function fixExtraDB()
{
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
	//$sq->rawQuery("update service set grepstring = 'courier' where servicename = 'courier-imap'");

	//$sq->rawQuery("update ticket set parent_clname = 'client_s_vv_p_admin' where subject = 'Welcome to Kloxo'");
	$sq->rawQuery("update ticket set parent_clname = 'client-admin' where subject = 'Welcome to Kloxo'");
	$sq->rawQuery("update domain set dtype = 'maindomain' where dtype = 'domain'");
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
	migrateResourceplan('domain');
	$sq->rawQuery("update resourceplan set realname = nname where realname = ''");
	$sq->rawQuery("update resourceplan set realname = nname where realname is null");
	db_set_default_variable_diskusage('client', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable_diskusage('domain', 'priv_q_totaldisk_usage', 'priv_q_disk_usage');
	db_set_default_variable('web', 'docroot', 'nname');
	db_set_default_variable('client', 'used_q_maindomain_num', 'used_q_domain_num');
	db_set_default_variable('client', 'priv_q_maindomain_num', 'priv_q_domain_num');
	$sq->rawQuery("alter table sslcert change text_ca_content text_ca_content longtext");
	$sq->rawQuery("alter table sslcert change text_key_content text_key_content longtext");
	$sq->rawQuery("alter table sslcert change text_csr_content text_csr_content longtext");
	$sq->rawQuery("alter table sslcert change text_crt_content text_crt_content longtext");
	$sq->rawQuery("alter table mailaccount change ser_forward_a ser_forward_a longtext");
	$sq->rawQuery("alter table dns change ser_dns_record_a ser_dns_record_a longtext");
	$sq->rawQuery("alter table installsoft change ser_installsoftmisc_b ser_installsoftmisc_b longtext");
	$sq->rawQuery("alter table web change ser_redirect_a ser_redirect_a longtext");
	initDbLoginPre();
	lxshell_php("../bin/common/fixresourceplan.php");
	db_set_default("servermail", "domainkey_flag", "on");

	critical_change_db_pass();
}




function doUpdateExtraStuff()
{
	global $gbl, $sgbl, $login, $ghtml; 

	fix_arch();
	lxfile_mkdir("__path_program_etc/flag");
	@ unlink("/home/kloxo/httpd/webmail/horde/Ligesh,");

	
	fix_dns_zones();
	lxshell_return("lphp.exe", "../bin/fixIpAddress.php");
	fixservice();


	add_domain_backup_dir();
	if (!posix_getpwnam('admin')) {
		os_create_system_user('admin', randomString(7), 'admin', '/sbin/nologin', "/home/admin");
	}

	copy_image();
	lxfile_cp("tmpimg/custom_button.gif", "img/general/default/default.gif");

	call_with_flag("fix_phpini");
	call_with_flag("fix_awstats");
	call_with_flag("fix_domainkey");

	watchdog::addDefaultWatchdog('localhost');
	$a = null;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'web');
	$a['web'] = $driverapp;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'spam');
	$a['spam'] = $driverapp;
	$driverapp = $gbl->getSyncClass(null, 'localhost', 'dns');
	$a['dns'] = $driverapp;
	slave_save_db("driver", $a);

	$a = null;
	fix_mysql_root_password('localhost');
	$dbadmin = new Dbadmin(null, 'localhost', "mysql___localhost");
	$dbadmin->get();
	$pass = $dbadmin->dbpassword;
	$a['mysql']['dbpassword'] = $pass;
	slave_save_db("dbadmin", $a);
	save_admin_email();
	lxshell_php("htmllib/lbin/getlicense.php");


	system("mysql -u kloxo -p`cat ../etc/conf/kloxo.pass` kloxo < ../file/interface/interface_template.dump");

	fix_self_ssl();
	$value = db_get_value("servermail", "localhost", "virus_scan_flag");

	if (!isOn($value)) {
		dprint("Shutting off freshclam\n");
		system("chkconfig freshclam off > /dev/null 2>&1");
		system("service freshclam stop >/dev/null 2>&1");
	}
	lxfile_rm("title.img");



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
	return;
	if (lxfile_exists("/etc/php.d/xcache.ini")) {
		return;
	}
	if (lxfile_exists("/etc/php.d/xcache.noini")) {
		return;
	}

	lxshell_return("yum", "-y", "install", "php-xcache");
	lunlink("/etc/php.d/xcache.ini");
	lxfile_cp("../file/xcache.ini", "/etc/php.d/xcache.noini");
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
	lxshell_return("__path_php_path", "../bin/misc/lxinstall_hordegroupware_db.php");
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


function installInstallSoft()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->is_this_master()) {
		$gen = $login->getObject('general')->generalmisc_b;
		$diflag = $gen->isOn('disableinstallapp');
	} else {
		$diflag = false;
	}

	if (!lxfile_exists("__path_kloxo_httpd_root/installappdata")) {
		installapp_data_update();
	}

	if (lfile_exists("../etc/remote_installapp")) {
		lxfile_rm_rec("/home/kloxo/httpd/installsoft/");
		system("cd /var/cache/kloxo/ ; rm -f installsoft*.tar.gz;");
		return;
	}


	if ($diflag) {
		lxfile_rm_rec("/home/kloxo/httpd/installsoft/");
		system("cd /var/cache/kloxo/ ; rm -f installsoft*.tar.gz;");
		return;
	}

	return;

	lxfile_mkdir("__path_kloxo_httpd_root/installsoft");

	if (!lxfile_exists("__path_kloxo_httpd_root/installsoft/wordpress")) {
		lxshell_php("../bin/installapp-update.phps");
	}
	return;
}


function installWithVersion($path, $file, $ver)
{

	if (!is_numeric($ver)) { return; }

	lxfile_mkdir("/var/cache/kloxo");
	if (!lxfile_real("/var/cache/kloxo/$file$ver.tar.gz")) {
		$count = 0;
		while (1) {
			$count++;
			if ($count > 20) { return true; }
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxlabs.com/download/$file$ver.tar.gz");
			lxfile_rm_rec($path);
			lxfile_mkdir($path);
			$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/$file$ver.tar.gz");
			if (!$ret) { return true; }
		}
	}
	return false;
}

function installWebmail($ver)
{
	if (!is_numeric($ver)) { return; }

	lxfile_mkdir("/var/cache/kloxo");
	$path = "/home/kloxo/httpd/webmail";
	lxfile_mkdir($path);
	if (lxfile_real("/var/cache/kloxo/lxwebmail$ver.tar.gz")) {
		return;
	}

	$count = 0;
	while (1) {
		$count++;
		if ($count > 1) { return true; }
		system("cd /var/cache/kloxo/ ; rm -f lxwebmail*.tar.gz; wget download.lxlabs.com/download/lxwebmail$ver.tar.gz");
		if (!lxfile_exists("$path/roundcube")) {
			$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/lxwebmail$ver.tar.gz");
			if (!$ret) { return true; }
		}
		$tfile_h = lx_tmp_file("hordeconf");
		$tfile_r = lx_tmp_file("roundcubeconf");
		lxfile_cp("$path/horde/config/conf.php", $tfile_h);
		lxfile_cp("$path/roundcube/config/db.inc.php", $tfile_r);
		lxfile_rm_rec("$path/horde");
		lxfile_rm_rec("$path/roundcube");
		$ret = lxshell_unzip("__system__", $path, "/var/cache/kloxo/lxwebmail$ver.tar.gz");
		lxfile_cp($tfile_h, "$path/horde/config/conf.php");
		lxfile_cp($tfile_r, "$path/roundcube/config/db.inc.php");
		lxfile_rm($tfile_h);
		lxfile_rm($tfile_r);
		if (!$ret) { return true; }
	}
	return false;
}

function installAwstats($ver)
{
	if (!is_numeric($ver)) { return; }

	lxfile_mkdir("/var/cache/kloxo");
	lxfile_mkdir("/home/kloxo/httpd/awstats/");
	if (!lxfile_real("/var/cache/kloxo/lxawstats$ver.tar.gz")) {
		system("cd /var/cache/kloxo/ ; rm -f lxawstats*.tar.gz; wget download.lxlabs.com/download/lxawstats$ver.tar.gz");
		lxfile_rm_rec("/home/kloxo/httpd/awstats/tools/");
		lxfile_rm_rec("/home/kloxo/httpd/awstats/wwwroot/");
		system("cd /home/kloxo/httpd/awstats/ ; tar -xzf /var/cache/kloxo/lxawstats$ver.tar.gz tools wwwroot docs");
	}
}

function restart_xinetd_for_pureftp()
{
	createRestartFile("xinetd");
}

function install_bogofilter()
{
	if (lxfile_exists("/var/bogofilter/kloxo.wordlist.db")) { return; }
	lxfile_mkdir("/var/bogofilter");
	system("cd /var/bogofilter/ ; rm wordlist.db ;  wget http://download.lxlabs.com/download/wordlist.db");
	lxfile_unix_chown_rec("/var/bogofilter", "lxpopuser:lxpopgroup");
	system("cd /var/bogofilter/ ; cp wordlist.db kloxo.wordlist.db ");
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
	download_thirdparty(2012);
	os_updateApplicableToSlaveToo();
	lxfile_mkdir("__path_kloxo_httpd_root/default/");
	lxfile_cp("../file/skeleton.zip", "__path_kloxo_httpd_root/skeleton.zip");
	lxshell_unzip("__system__", "__path_kloxo_httpd_root/default/", "../file/skeleton.zip");
	lxfile_cp("../file/default_index.html", "__path_kloxo_httpd_root/default/index.html");
	lxfile_cp("tmpimg/feather.css", "img/skin/kloxo/feather/default");
	lxfile_mkdir("__path_kloxo_httpd_root/disable/");
	lxfile_cp("../file/disable.html", "__path_kloxo_httpd_root/disable/index.html");
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
	global $gbl, $sgbl, $login, $ghtml; 

	PrepareRoundCubeDb();
	if (lxfile_exists("/home/kloxo/httpd/webmail/roundcube/")) {
		system("chown -R lxlabs:lxlabs /home/kloxo/httpd/webmail/");
		@ system("rm -f /tmp/horde.log");
		return;
	}
}


function fix_suexec()
{
	system("unlink /usr/bin/lxsuexec");
	system("unlink /usr/bin/lxexec");
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
	dprint("Droping table...............\n");
	$db = new Sqlite($tbl_name);
	$db->rawQuery("drop table " . $tbl_name );
}


function dropcolumn($tbl_name, $column) 
{
	dprint("Droping Column...............\n");

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





