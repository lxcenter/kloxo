#/bin/sh
#
# Restore core php.ini (lxphp) to make sure everything runs fine.
#
/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/
#
# Executing update/cleanup process
#
exec /usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php "$@"
#
# chmoding sbin binaries to 755
#
chmod 755 ../sbin/lxrestart
chmod ug+s ../sbin/lxrestart
#
# Finished
#