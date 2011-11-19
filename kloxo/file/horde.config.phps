<?php
/* CONFIG START. DO NOT CHANGE ANYTHING IN OR AFTER THIS LINE. */
// $Horde: horde/config/conf.xml,v 1.74.2.39 2006/06/22 05:09:01 chuck Exp $
$conf['debug_level'] = E_ALL;
$conf['max_exec_time'] = 0;
/* --- issue #637
$conf['use_ssl'] = 1;
$conf['server']['name'] = $_SERVER['SERVER_NAME'];
$conf['server']['port'] = 443;
--- */
$conf['use_ssl'] = 2;
$conf['server']['name'] = $_SERVER['SERVER_NAME'];
$conf['server']['port'] = $_SERVER['SERVER_PORT'];

$conf['compress_pages'] = true;
$conf['umask'] = 077;
$conf['session']['name'] = 'mm';
$conf['session']['use_only_cookies'] = true;
$conf['session']['cache_limiter'] = 'nocache';
$conf['session']['timeout'] = 0;
$conf['cookie']['path'] = '/';

/* fix bug 529 */
$conf['cookie']['domain'] = '';
$conf['urls']['token_lifetime'] = 30;
$conf['urls']['hmac_lifetime'] = 30;

$conf['sql']['persistent'] = false;
$conf['sql']['hostspec'] = 'localhost';
$conf['sql']['username'] = 'horde_groupware';
$conf['sql']['password'] = '__lx_horde_pass';
$conf['sql']['port'] = 3306;
$conf['sql']['protocol'] = 'tcp';
$conf['sql']['database'] = 'horde_groupware';
$conf['sql']['charset'] = 'iso-8859-1';
$conf['sql']['phptype'] = 'mysql';
$conf['auth']['admins'] = array('Administrator');
$conf['auth']['checkip'] = true;
$conf['auth']['checkbrowser'] = true;
$conf['auth']['alternate_login'] = false;
$conf['auth']['redirect_on_logout'] = false;
$conf['auth']['params']['username'] = 'horde';
$conf['auth']['params']['requestuser'] = true;
$conf['auth']['driver'] = 'application';
$conf['auth']['params']['app'] = 'imp';
$conf['signup']['allow'] = false;
$conf['log']['priority'] = PEAR_LOG_NOTICE;
$conf['log']['ident'] = 'HORDE';
$conf['log']['params'] = array();
$conf['log']['name'] = '/tmp/horde.log';
$conf['log']['params']['append'] = true;
$conf['log']['type'] = 'file';
$conf['log']['enabled'] = true;
$conf['log_accesskeys'] = false;
$conf['prefs']['params']['driverconfig'] = 'horde';
$conf['prefs']['driver'] = 'sql';
$conf['datatree']['params']['driverconfig'] = 'horde';
$conf['datatree']['driver'] = 'sql';
$conf['group']['driver'] = 'datatree';
$conf['cache']['default_lifetime'] = 1800;
$conf['cache']['params']['dir'] = Horde::getTempDir();
$conf['cache']['params']['gc'] = 86400;
$conf['cache']['driver'] = 'file';
$conf['token']['driver'] = 'none';

// --- issue #637
$conf['mailer']['type'] = 'smtp'; // select 'smtp' or 'sendmail'; default: 'smtp'

if ($conf['mailer']['type'] === 'sendmail' ) {
	$conf['mailer']['params']['sendmail_path'] = '/usr/lib/sendmail';
	$conf['mailer']['params']['sendmail_args'] = '-oi';
	$conf['mailer']['type'] = 'sendmail';
}
else {
	$conf['mailer']['params']['host'] = 'localhost';
	$conf['mailer']['params']['port'] = 25;
	$conf['mailer']['type'] = 'smtp';
	// read http://forum.parallels.com/showthread.php?t=100576
	$conf['mailer']['params']['auth'] = true;
}

$conf['vfs']['params']['vfsroot'] = '/tmp';
$conf['vfs']['type'] = 'file';
$conf['sessionhandler']['type'] = 'none';
$conf['problems']['email'] = 'webmaster@example.com';
$conf['problems']['maildomain'] = 'example.com';
$conf['problems']['tickets'] = false;
$conf['menu']['apps'] = array();
$conf['menu']['always'] = false;
$conf['menu']['links']['help'] = 'all';
$conf['menu']['links']['help_about'] = true;
$conf['menu']['links']['options'] = 'authenticated';
$conf['menu']['links']['problem'] = 'all';
$conf['menu']['links']['login'] = 'all';
$conf['menu']['links']['logout'] = 'authenticated';
$conf['hooks']['permsdenied'] = false;
$conf['hooks']['username'] = false;
$conf['hooks']['preauthenticate'] = false;
$conf['hooks']['postauthenticate'] = false;
$conf['hooks']['authldap'] = false;
$conf['portal']['fixed_blocks'] = array();
$conf['accounts']['driver'] = 'null';
$conf['imsp']['enabled'] = false;
$conf['kolab']['enabled'] = false;
/* CONFIG END. DO NOT CHANGE ANYTHING IN OR BEFORE THIS LINE. */
