#!/bin/sh
#
# LxCenter note: uses an lxadmin path!
#
name=$1
pass=$2
dbuser=$3
dbpass=$4
MYSQLPR=`which mysql`
if [ ! -f "$MYSQLPR" ]; then
echo "FATAL ERROR: MySQL client is not there. MySQL not installed?"
exit 1
fi
if [ -f /var/lock/subsys/mysqld ] ;then
if [ -z $pass ] ; then
echo "CREATE DATABASE IF NOT EXISTS popuser; GRANT ALL PRIVILEGES ON popuser.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" 
#"$MYSQLPR" -u"$name"  < /home/lxadmin/mail/doc/finaldelivery-additions.sql
echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name"
else
echo "CREATE DATABASE IF NOT EXISTS popuser; GRANT ALL PRIVILEGES ON popuser.* TO $dbuser@localhost   IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" -p"$pass"
# "$MYSQLPR" -u"$name" -p"$pass" < /home/lxadmin/mail/doc/finaldelivery-additions.sql
echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" -p"$pass"
 fi
 fi
 echo "localhost|0|$dbuser|$dbpass|vpopmail">/home/lxadmin/mail/etc/vpopmail.mysql
