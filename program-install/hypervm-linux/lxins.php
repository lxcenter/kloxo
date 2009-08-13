<?php

// PHp4, without the lxlabs infrastructure... The code is very bad, primarily because it falls outside the lxlabs framework, and has to work on php4 too, which is something we have no experience in. We need to move as much of the activities to code after the installation of the lxlabs framework.

include_once "../install_common.php";
lxins_main();




function lxins_main()
{
	global $argv;

	$opt = parse_opt($argv);


	$installtype = $opt['install-type'];

	$highmem = false;
	if (isset($opt['has-highmem'])) {
		$highmem = true;
	}

	if (!isset($opt['virtualization-type'])) {
		print("Need virtualization type --virtualization-type=xen/openvz/NONE\n");
		exit;
	} else {
		$virtualization = $opt['virtualization-type'];
	}

	$skipostemplate = false;
	if (isset($opt['skip-ostemplate'])) { $skipostemplate = true; }

	if (array_search($virtualization, array("xen", "openvz", "NONE")) === false) {
		print("Only xen/openvz/NONE are curently supported\n");
		exit;
	}
	
	$dbroot = isset($opt['db-rootuser'])? $opt['db-rootuser']: "root";
	$dbpass = isset($opt['db-rootpassword'])? $opt['db-rootpassword']: "";

	if (!$dbpass) {
		//$dbpass = slave_get_db_pass("hypervm");
	}
	$osversion = find_os_version();

	if(file_exists("/usr/local/lxlabs/hypervm")) {
		print("hyperVM is installed do you wish to continue?(No/Yes):\n");
		flush();
		$stdin = fopen('php://stdin','r');
		$argq = fread($stdin, 5);
		$arg=trim($argq);
		if(!($arg=='y' ||$arg=='yes'||$arg=='Yes'||$arg=='Y'||$arg=='YES')) {
			print("Exiting.....\n");
			exit;
		}
	}


	if ($virtualization === 'xen') {
		if (!char_search_beg($osversion, "fedora-9") && !char_search_beg($osversion, "centos-5") && !char_search_beg($osversion, "rhel-5")) {
			print("Xen is only supported on Fedora-9 or Centos-5\n");
			exit;
		}
	}


	if ($virtualization === 'openvz') {
		if (!char_search_beg($osversion, "centos") && !char_search_beg($osversion, "rhel")) {
			print("Openvz is only supported on centos, rhel 4/5\n");
			exit;
		}
	}


	/*
	$file = "http://download.lxlabs.com/download/update/$osversion/headers/header.info";
	$cont = @file_get_contents($file);
	if (!$cont) {
		print("This OS is not suported at this moment.... Please contact our Support personnel\n");
		exit;
	}
*/

	//install_rhn_sources($osversion);
	install_yum_repo($osversion);

	exec("groupadd lxlabs");
	exec("useradd lxlabs -g lxlabs -s '/sbin/nologin'");

	$list = array("which",  "lxlighttpd", "zip","unzip", "lxphp", "lxzend", "curl");

	if ($installtype !== 'slave') {
		$mysql = array("mysql", "mysql-server", "mysqlclient10");
		$list = array_merge($list, $mysql);
	}

	while (true) {
		run_package_installer($list);
		if (file_exists("/usr/local/lxlabs/ext/php/php")) {
			break;
		} else {
			print("Yum Gave Error... Trying Again...\n");
		}
	}


	if ($installtype !== 'slave') {
		check_default_mysql($dbroot, $dbpass);
	}


	$xenfailed = false;

	exec("killall wget");



	system("mkdir -p /usr/local/lxlabs/hypervm");
	chdir("/usr/local/lxlabs/hypervm");
	system("mkdir -p /usr/local/lxlabs/hypervm/log");
	@ unlink("hypervm-current.zip");
	system("wget http://download.lxlabs.com/download/hypervm/production/hypervm/hypervm-current.zip");
    system("unzip -oq hypervm-current.zip", $return); 

	if ($return) {
		print("Unzipping the core Failed.. Most likely it is corrupted. Please contact the support personnel\n");
		exit;
	}
	unlink("hypervm-current.zip");
	system("chown -R lxlabs:lxlabs /usr/local/lxlabs/");
	$dir_name=dirname(__FILE__);

	fix_network_forwarding();

	system("mkdir -p /usr/local/lxlabs/hypervm/etc/");
	@ unlink("/usr/local/lxlabs/hypervm/etc/install_xen");
	@ unlink("/usr/local/lxlabs/hypervm/etc/install_openvz");
	touch("/usr/local/lxlabs/hypervm/etc/install_$virtualization");
	chdir("/usr/local/lxlabs/hypervm/httpdocs/");
	system("/bin/cp /usr/local/lxlabs/hypervm/httpdocs/htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/php.ini");
	system("/usr/local/lxlabs/ext/php/php ../bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");
	//@ unlink("/usr/local/lxlabs/lxadmin/bin/install/create.php");

	system("chmod 755 /etc/init.d/hypervm");
	system("/sbin/chkconfig hypervm on");
	system("/sbin/chkconfig iptables off");
	//system("service hypervm start");

	$skiparg = null;
	if ($skipostemplate) { $skiparg = "--skipostemplate=true"; }

	if ($virtualization === "NONE") {
		print("No Virtualization has been chosen. It is assumed that it is an existing installation\n");
	} else {
		print("Virtualization is $virtualization. Installing $virtualization Components\n");
	}

	passthru("/usr/local/lxlabs/ext/php/php ../bin/install/virt-install.php --install-type=$installtype --virtualization-type=$virtualization $skiparg");


	print("Congratuations. hyperVM has been installed succesfully on your server as $installtype \n");

	if ($installtype === 'master') {
		print("You can connect to the server at https://<ip-address>:8887 or http://<ip-address>:8888\n");
		print("Please note that first is secure ssl connection, while the second is normal one.\n");
		print("The login and password are 'admin' 'admin'. After Logging in, you will have to change your password to something more secure\n");
		print("Thanks for choosing hyperVM to manage your Server, and allowing us to be of service\n");
	} else {
		print("You should open the port 8889 on this server, since this is used for the communication between master and slave\n");
		print("To access this slave, go admin->slaves->add slave, give the ip/machine name of this server. The password is 'admin'. The slave will appear in the list of slaves, and you can access it just like you access localhost\n");
	}

	if ($virtualization === 'openvz') {
		print("\n***There is one more step you have to do to make this complete. Open /etc/grub.conf, and change the 'default=1' line to 'default=0', and reboot this machine. You will be rebooted into the openvz kernel and will able to manage vpses from the hyperVM interface\n");
	} else if ($virtualization === 'xen'){
		print("\n**** You will have to reboot for the xen kernel to take effect. Once rebooted, you will able to manage xen virtual machines using the hyperVM interface\n");
	}
}

function fix_network_forwarding()
{
	$list = file("/etc/sysctl.conf");

	foreach($list as $__l) {
		if (strstr($__l, "net.ipv4.ip_forward") !== false) {
			$newlist[] = "net.ipv4.ip_forward = 1\n";
		} else {
			$newlist[] = $__l;
		}
	}

	our_file_put_contents("/etc/sysctl.conf", implode("", $newlist));
	shell_exec("sysctl -p");
}


function run_package_installer($list)
{
	$package = implode(" ", $list);
	print("Installing packages $package...\n");
	if (file_exists("/usr/bin/yum")) {
		system("yum -y install $package", $return_value);
	} else {
		system("PATH=\$PATH:/usr/sbin up2date --nosig $package", $return_value);
	}
}



