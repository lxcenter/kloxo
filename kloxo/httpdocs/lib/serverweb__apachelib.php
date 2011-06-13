<?php 

class serverweb__apache extends lxDriverClass {

function dbactionUpdate($subaction)
{
    // issue 571 - add httpd-worker and httpd-event for suphp
	
    lxshell_return("service", "httpd", "stop");

    if ($this->main->php_type === 'mod_php') {
        lxfile_mv("/etc/httpd/conf.d/php.nonconf", "/etc/httpd/conf.d/php.conf");
        lxfile_mv("/etc/httpd/conf.d/suphp.conf", "/etc/httpd/conf.d/suphp.nonconf");
        lxfile_cp("../file/httpd.prefork", "/etc/sysconfig/httpd");
    } else {
        system("yum -y install mod_suphp");
        lxfile_mv("/etc/httpd/conf.d/php.conf", "/etc/httpd/conf.d/php.nonconf");
        lxfile_mv("/etc/httpd/conf.d/suphp.nonconf", "/etc/httpd/conf.d/suphp.conf");
        lxfile_cp("../file/suphp.conf", "/etc/httpd/conf.d/suphp.conf");
        lxfile_cp("../file/etc_suphp.conf", "/etc/suphp.conf");

        // suphp_event_test become suphp_event if status on apache.org no longer as 'experimental'
        if ($this->main->php_type === 'suphp_worker') {
            lxfile_cp("../file/httpd.worker", "/etc/sysconfig/httpd");
        }
        else if ($this->main->php_type === 'suphp_event_test') {
            lxfile_cp("../file/httpd.event", "/etc/sysconfig/httpd");
        } else {
            lxfile_cp("../file/httpd.prefork", "/etc/sysconfig/httpd");
        }
    }
	
	// no overwrite while exist so ~lxcenter.conf can use as 'user-defined' config
	// use prefix tilde to make sure the last file read/execute by httpd
	if (!file_exists("/etc/httpd/conf.d/~lxcenter.conf")) {
        lxfile_cp("../file/~lxcenter.conf", "/etc/httpd/conf.d/~lxcenter.conf");
	}

    # Fixed issue #515 - returned due to accidentally deleted
    lxfile_generic_chmod("/home/admin", "0770");

//  change to 'stop' and 'start' because problem if choose htttp-worker and httpd-event
//  createRestartFile("httpd");

    lxshell_return("service", "httpd", "start");
}

}
