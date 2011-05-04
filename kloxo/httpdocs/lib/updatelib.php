<?php 


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

	log_cleanup("Checking freshclam (virus scanner)");
	if (!isOn(db_get_value("servermail", "localhost", "virus_scan_flag"))) {
		system("chkconfig freshclam off > /dev/null 2>&1");
		system("service freshclam stop >/dev/null 2>&1");
		log_cleanup("Disabled freshclam service\n");
	}

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

function fixservice()
{
	lxshell_return("__path_php_path", "../bin/fix/fixservice.php");
}

function installinstallapp()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if ($sgbl->is_this_master()) {
		$gen = $login->getObject('general')->generalmisc_b;
		$diflag = $gen->isOn('disableinstallapp');
		dprint("Disable InstallApp flag is ON\n");
	} else {
		$diflag = false;
		dprint("Disable InstallApp flag is OFF\n");
	}

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


	if ($diflag) {
		dprint("InstallApp is turned off, remove InstallApp..\n");
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

function installWithVersion($path, $file, $ver)
{

	if (!is_numeric($ver)) { return; }

	lxfile_mkdir("/var/cache/kloxo");
	if (!lxfile_real("/var/cache/kloxo/$file$ver.tar.gz")) {
		$count = 0;
		while (1) {
			$count++;
			if ($count > 20) { return true; }
			system("cd /var/cache/kloxo/ ; rm -f $file*.tar.gz; wget download.lxcenter.org/download/$file$ver.tar.gz");
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
		system("cd /var/cache/kloxo/ ; rm -f lxwebmail*.tar.gz; wget download.lxcenter.org/download/lxwebmail$ver.tar.gz");
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
		system("cd /var/cache/kloxo/ ; rm -f lxawstats*.tar.gz; wget download.lxcenter.org/download/lxawstats$ver.tar.gz");
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
		lxfile_rm('/tmp/horde.log');
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





