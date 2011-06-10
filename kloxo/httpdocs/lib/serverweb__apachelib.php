<?php 

class serverweb__apache extends lxDriverClass {

function dbactionUpdate($subaction)
{
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

    lxfile_cp("../file/mpm.conf", "/etc/httpd/conf.d/mpm.conf");

//    createRestartFile("httpd");

    lxshell_return("service", "httpd", "start");
}

}
