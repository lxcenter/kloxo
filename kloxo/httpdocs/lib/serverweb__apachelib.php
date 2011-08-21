<?php 

class serverweb__apache extends lxDriverClass {

function dbactionUpdate($subaction)
{
	if ($this->main->php_type === 'mod_php') {
		lxfile_mv("/etc/httpd/conf.d/php.nonconf", "/etc/httpd/conf.d/php.conf");
		lxfile_mv("/etc/httpd/conf.d/suphp.conf", "/etc/httpd/conf.d/suphp.nonconf");
	} else {
		system("yum -y install mod_suphp");
		lxfile_mv("/etc/httpd/conf.d/php.conf", "/etc/httpd/conf.d/php.nonconf");
		lxfile_mv("/etc/httpd/conf.d/suphp.nonconf", "/etc/httpd/conf.d/suphp.conf");
		lxfile_cp("../file/suphp.conf", "/etc/httpd/conf.d/suphp.conf");
		lxfile_cp("../file/etc_suphp.conf", "/etc/suphp.conf");
	}
	# Fixed issue #515
	lxfile_generic_chmod("/home/admin", "0770");

	createRestartFile("httpd");
}



}
