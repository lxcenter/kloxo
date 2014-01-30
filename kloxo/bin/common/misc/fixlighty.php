<?php 

include_once "htmllib/lib/displayinclude.php";


$nonsslhash = "#";
$sslport = $sgbl->__var_prog_ssl_port;
$nonsslport = $sgbl->__var_prog_port;

$list = parse_opt($argv);
if (!isset($list['default-port']) && !lxfile_exists("__path_slave_db")) {

	initProgram('admin');
	$gen = $login->getObject('general')->portconfig_b;
	if (isset($gen)) {
		if ($gen->isOn('nonsslportdisable_flag')) {
			$nonsslhash = "";
		}
		if (isset($gen->sslport)) {
			$sslport = $gen->sslport;
		}
		if (isset($gen->nonsslport)) {
			$nonsslport = $gen->nonsslport;
		}
	}
}

$list = lfile("htmllib/filecore/lighttpd.conf");

$ret = lxshell_return("__path_php_path", "../bin/common/misc/checktotalmemory.php");

if ($ret === 15) {
	$user = "###username..";
} else {
	$user = "server.username            = \"lxlabs\"";
}

$phpcgi_num = 1;

$out = lxshell_output("/usr/local/lxlabs/ext/php/bin/php_cgi", "-v");
$lightout = lxshell_output("/usr/local/lxlabs/ext/lxlighttpd/sbin/kloxo.httpd", "-v");

$php_st = null;
if (csa($lightout, "1.4.") && csa($out, "cgi-fcgi") && $sgbl->isKloxo() && trim(`hostname`) !== 'support.lxlabs.com' && $ret === 15) {
	$php_st .= "fastcgi.server  = (\".php\" => \n";
	$php_st .= "		(( \"socket\" => \"/usr/local/lxlabs/$sgbl->__var_program_name/etc/php_socket.socket\",\n";
	$php_st .= "		   \"bin-path\" => \"/usr/local/lxlabs/ext/php/bin/php_cgi\",\n";
	$php_st .= "		   \"min-procs\" => 0,\n";
	$php_st .= "		   \"max-procs\" => $phpcgi_num,\n";
	$php_st .= "		   \"bin-environment\" => (\n";
	$php_st .= "			   \"PHP_FCGI_CHILDREN\" => \"0\",\n";
	$php_st .= "			   \"PHP_FCGI_MAX_REQUESTS\" => \"100000000\" ),\n";
	$php_st .= "		   \"max-load-per-proc\" => 10000,\n";
	$php_st .= "		   \"idle-timeout\" => 3\n";
	$php_st .= "		 ))\n";
	$php_st .= "		)\n";
} 
// This makes kloxo run as root! 
/*
else if ( $sgbl->isKloxo() && !$sgbl->isDebug()) {
//this one: 	$php_st .= "cgi.assign	=						   (\".php\" => \"/usr/local/lxlabs/kloxo/file/phpsuexec.sh\" )\n";
} 
else 
{
	$php_st .= "cgi.assign	=						   (\".php\" => \"/usr/local/lxlabs/ext/php/bin/php_cgi\" )\n";
}
*/

$php_st .= "cgi.assign	=						   (\".php\" => \"/usr/local/lxlabs/ext/php/bin/php_cgi\" )\n";


foreach($list as &$l) {

	$l = preg_replace("/__cgi_or_fcgi__/", $php_st, $l);
	$l = preg_replace("/__program_name__/", $sgbl->__var_program_name, $l);
	$l = preg_replace("/__program_disable_nonssl__/", $nonsslhash, $l);
	$l = preg_replace("/__program_port__/", $nonsslport, $l);
	$l = preg_replace("/__program_sslport__/", $sslport, $l);
	$l = preg_replace("/__program_user__/", $user, $l);
}

lfile_put_contents("../file/lighttpd.conf", implode("", $list));

$pemfile = "__path_program_root/etc/program.pem";
$cafile = "__path_program_root/etc/program.ca";

if (!lxfile_exists($pemfile)) {
	lxfile_cp("__path_program_htmlbase/htmllib/filecore/program.pem", $pemfile);
	lxfile_generic_chown($pemfile, "lxlabs");
}

lxfile_touch("__path_program_root/log/lighttpd_error.log");
lxfile_touch("__path_program_root/log/access_log");
lxfile_generic_chmod("__path_program_root/log", "0700");
lxfile_generic_chown("__path_program_root/log", "lxlabs:lxlabs");
lxfile_generic_chmod("__path_program_root/log/lighttpd_error.log", "0644");
lxfile_generic_chmod("__path_program_root/log/access_log", "0644");
lxfile_generic_chown("__path_program_root/log/lighttpd_error.log", "lxlabs:root");
lxfile_generic_chown("__path_program_root/log/access_log", "lxlabs:root");

if (!lxfile_exists($cafile)) {
	lxfile_cp("__path_program_htmlbase/htmllib/filecore/program.ca", $cafile);
	lxfile_generic_chown($cafile, "lxlabs");
}
