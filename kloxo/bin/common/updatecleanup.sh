#/bin/sh
<<<<<<< HEAD
/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/
exec /usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php "$@"
# i am chmoding sbin to 755 inside updatecleanup so needs to do this here.
chmod 755 ../sbin/lxrestart
chmod ug+s ../sbin/lxrestart
=======
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
>>>>>>> upstream/dev
