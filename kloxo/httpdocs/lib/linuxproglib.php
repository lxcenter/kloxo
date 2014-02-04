<?php 

function os_doUpdateExtraStuff()
{
// TODO: Remove empty Function
}

function os_update_server()
{
	system("yum -y install --nosig webalizer lxjailshell autorespond unzip lxlighttpd kloxo-core-php >/dev/null 2>&1 &");
	os_fix_some_permissions();
	lxfile_touch("../etc/flag/lowmem.flag");
	os_createLowMem();
}

function os_createLowMem()
{
	if (!lxfile_exists("/proc/user_beancounters") && !lxfile_exists("/proc/xen")) {
		if (!lxshell_return("diff", "../file/lowmem/my.cnf.lowmem", "/etc/my.cnf")) {
			lxfile_cp("/etc/lowmem.saved.my.cnf", "/etc/my.cnf");
			createRestartFile("mysqld");
		}
		return;
	}

	if (lxfile_exists("__path_program_etc/flag/lowmem.flag")) {

		if (!lxfile_exists("/etc/lowmem.saved.my.cnf")) {
			lxfile_cp("/etc/my.cnf", "/etc/lowmem.saved.my.cnf");
			lxfile_cp("../file/lowmem/my.cnf.lowmem", "/etc/my.cnf");
			createRestartFile('mysql');
			createRestartFile('courier-imap');
		}
		//lxfile_cp("../file/lowmem/spamassassin.lowmem", "/etc/sysconfig/spamassassin");
	}
}

function os_create_kloxo_service_once() { }

function os_set_iis_ftp_root_path() { }

function os_fix_some_permissions()
{
	// TODO: Remove empty Function
}

function remove_lighttpd_error_log()
{
	$f = "/home/kloxo/httpd/lighttpd/error.log";
	$s = lxfile_size($f);
	if ($s > 50 * 1024 * 1024) {
		lunlink($f);
		createRestartFile("lighttpd");
	}
}

function create_dev()
{
	if (lxfile_exists("/sbin/udevd")) {
		lxfile_mv("/sbin/udevd", "/sbin/udevd.back");
	}
	lxshell_return('tar', '-C', '/dev', '-xzf', '../file/centos-5/vps-dev.tgz');
	lxshell_return('/sbin/MAKEDEV', 'pty');
	lxshell_return('/sbin/MAKEDEV', 'tty');
	lxshell_return('/sbin/MAKEDEV', 'loop');
	lxshell_return('/sbin/MAKEDEV', 'random');
	lxshell_return('/sbin/MAKEDEV', 'urandom');
}

function fix_hordedb_proper()
{
//	lxshell_php("../bin/misc/lxinstall_hordegroupware_db.php");
}

function os_updateApplicableToSlaveToo()
{

	move_clients_to_client();
	$desc = uuser::getUserDescription('admin');

	// function declare in updatelib.php --> then move to lib.php
	updatecleanup();
}

function remove_test_root()
{
	$pass = slave_get_db_pass();
	$__tr = mysql_connect("localhost", "root", $pass);
	mysql_select_db("mysql", $__tr);
	mysql_query("delete from user where Host = 'test.lxlabs.com' and User = 'root'", $__tr);
}

function remove_ssh_self_host_key()
{
	# TODO: Can be removed somewhere in 6.2.x branche
	if (lxfile_exists("/root/.ssh/authorized_keys")) {
 		remove_line("/root/.ssh/authorized_keys", "root@self.lxlabs.com");
	}
        if (lxfile_exists("/root/.ssh/authorized_keys2")) {
		remove_line("/root/.ssh/authorized_keys2", "root@self.lxlabs.com");
	}
}

function remove_host_deny()
{
	system("echo > /etc/hosts.deny");
}
