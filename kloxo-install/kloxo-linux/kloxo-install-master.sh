#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2010	LxCenter
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
# LxCenter - Kloxo Installer
#
# TODO: Add options to reset mysql root pass, disable selinux, check free disk space,
#		uninstall. Maybe use "dialog" for ncurses-like functions.
#

APP_NAME=Kloxo
APP_TYPE=Master

SELINUX_CHECK=/usr/sbin/selinuxenabled
ARCH_CHECK=$(eval uname -m)

E_SELINUX=50
E_ARCH=51
E_NOYUM=52
E_NOSUPPORT=53
E_HASDB=54
E_NOTROOT=85

C_OK='\E[47;34m'"\033[1m OK \033[0m\n"
C_NO='\E[47;31m'"\033[1m NO \033[0m\n"
C_MISS='\E[47;33m'"\033[1m UNDETERMINED \033[0m\n"

clear

# Check if user is root.
if [ "$UID" -ne "0" ] ; then
		echo -en "Installing as \"root\"         " $C_NO
		echo -e "\a\nYou must be \"root\" to install $APP_NAME.\n\nAborting ...\n"
        exit $E_NOTROOT
else
		echo -en "Installing as \"root\"         " $C_OK
fi

# Check if OS is RHEL/CENTOS/FEDORA.
if [ ! -f /etc/redhat-release ] ; then
		echo -en "Operating System supported   " $C_NO
        echo -e "\a\nSorry, only Red Hat EL and CentOS are supported by $APP_NAME at this time.\n\nAborting ...\n"
        exit $E_NOSUPPORT
else
		echo -en "Operating System supported   " $C_OK
fi

# Check if SElinux is enabled from exit status. 0 = Enabled; 1 = Disabled; 127 = selinuxenabled missing
eval $SELINUX_CHECK

if [ "$?" -eq "127" ] ; then
		echo -en "SELinux disabled             " $C_MISS
        echo -e "\a\nThe installer could not determine SELinux status.\nIf you are sure it is DISABLED, you may proceed.\n"
        while :
        do
                read -n 1 -p "Continue? (Y/N) " se_agree
                echo -e "\n"
                case $se_agree in
                        y|Y) break;;
                        n|N) echo -e "Aborting ...\n"
                                exit $E_SELINUX;;
                        *) echo -e "Invalid input. Press Y or N.\n";;
                esac
        done
elif [ "$?" -eq "0" ] ; then
		echo -en "SELinux disabled             " $C_NO
        echo -e "\a\n$APP_NAME cannot be installed or executed with SELinux enabled.\nIf you followed the instructions, a reboot may be necessary.\nPlease DISABLE it and try again.\n\nAborting ...\n"
        exit $E_SELINUX
elif [ "$?" -eq "1" ] ; then
		echo -en "SELinux disabled             " $C_OK
fi

# Check if OS is 32bit and if not allow user to choose to continue or not (for devels).
# Remove this when RHEL/CENTOS x86_64 is officially supported or add the arch to prevent people from installing in ARM.
if [ "$ARCH_CHECK" != "i686" ] ; then
        echo -en "\aArchitecture supported ($ARCH_CHECK)" $C_NO "\n"
		while :
        do
                read -n 1 -p "Your OS architecture ($ARCH_CHECK) is NOT officially supported yet and $APP_NAME may not work correctly. Continue anyway? (Y/N) " arch_agree
                echo -e "\n"
                case "$arch_agree" in
                        y|Y) break;;
                        n|N) echo -e "Aborting ...\n"
                                exit $E_ARCH;;
                        *) echo -e "Invalid input. Press Y or N.\n";;
                esac
        done
else
		echo -en "Architecture supported ($ARCH_CHECK)" $C_OK
fi

# Check for mysql databases and arguments.
if  [ -d /var/lib/mysql ] && [ -z "$1" ] ; then
		echo -en "Database and arguments check " $C_NO
		echo -e "\a\nIt seems you already have databases in this system but did not provide the MySQL root pass. If you are reinstalling, remove mysql-server and databases stored at /var/lib/mysql. Otherwise, you must provide the password.\n\nUsage: sh $0 --db-rootpassword=PASSWORD\n\nAborting ...\n"
		exit $E_HASDB
else
		echo -en "Database and arguments check " $C_OK
fi

# Check if yum is installed.
if ! [ -f /usr/sbin/yum ] && ! [ -f /usr/bin/yum ] ; then
		echo -en "Yum installed                " $C_NO
        echo -e "\a\nThe installer requires YUM to continue. Please install it and try again.\nAborting ...\n"
        exit $E_NOYUM
else
		echo -en "Yum installed                " $C_OK
fi

echo
echo -e '\E[37;44m'"\033[1m Ready to begin $APP_NAME ($APP_TYPE) install. \033[0m"
echo -e "\n\n	Note some file downloads may not show a progress bar so please, do not interrupt the process."
echo -e "	When it's finished, you will be presented with a welcome message and further instructions.\n\n"

read -n 1 -p "Press any key to continue ..."

yum -y install php php-mysql wget zip unzip
rm -f kloxo-install.zip
wget http://download.lxcenter.org/download/kloxo-install.zip
export PATH=/usr/sbin:/sbin:$PATH
unzip -oq kloxo-install.zip
cd kloxo-install/kloxo-linux
php lxins.php --install-type=master $* | tee kloxo_install.log