#/bin/sh
/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/
exec /usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php "$@"
# i am chmoding sbin to 755 inside updatecleanup so needs to do this here.
chmod 755 ../sbin/lxrestart
chmod ug+s ../sbin/lxrestart
