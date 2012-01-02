#!/bin/sh
program=$1
shift
if [ $program = 'kloxo' ] ; then
	db="kloxo4_2"
else 
	db="hypervm1_0"
fi

echo -n "Taking backup of the current databse...   "
/usr/local/lxlabs/ext/php/php ../bin/common/mebackup.php >/dev/null
echo "done.."

if [ -z $1 ] ; then
	echo need the secondary slave address
	exit;
fi
echo "Syncing database from $@ to this server"
ssh "$@" sh /usr/local/lxlabs/$program/bin/common/databasedump $program | mysql -u $program -p`cat /usr/local/lxlabs/$program/etc/conf/$program.pass` $db
echo "done"
