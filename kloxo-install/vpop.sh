#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2014	LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
# LxCenter note: uses an lxadmin path!
#
# TODO: can this be implemented into kloxo-installer.php?
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
echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name"
else
echo "CREATE DATABASE IF NOT EXISTS popuser; GRANT ALL PRIVILEGES ON popuser.* TO $dbuser@localhost   IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" -p"$pass"
echo "CREATE DATABASE IF NOT EXISTS vpopmail;GRANT ALL PRIVILEGES ON vpopmail.* TO $dbuser@localhost IDENTIFIED BY '$dbpass'" | "$MYSQLPR" -u"$name" -p"$pass"
 fi
 fi
 echo "localhost|0|$dbuser|$dbpass|vpopmail">/home/lxadmin/mail/etc/vpopmail.mysql
