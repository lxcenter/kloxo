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

APP_NAME=Kloxo
SELINUX_CHECK=/usr/sbin/selinuxenabled
ARCH_CHECK=$(eval uname -m)

E_SELINUX=50
E_ARCH=51
E_NOYUM=52
E_NOSUPPORT=53
E_NOTROOT=85

# Check if user is root.
if [ "$UID" -ne "0" ] ; then
        echo -e "\a\nYou must be root to install $APP_NAME.\nAborting ...\n"
        exit $E_NOTROOT
fi

# Check if OS is RHEL/CENTOS/FEDORA.
if [ ! -f /etc/redhat-release ] ; then
        echo -e "\a\nSorry, only Red Hat EL and CentOS are supported by $APP_NAME at this time.\nAborting ...\n"
        exit $E_NOSUPPORT
fi

# Check if SElinux is enabled from exit status. 0 = Enabled; 1 = Disabled; 127 = selinuxenabled missing
eval $SELINUX_CHECK

if [ "$?" -eq "127" ] ; then
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
        echo -e "\a\n$APP_NAME cannot be installed or executed with SELinux enabled.\nPlease DISABLE it and try again.\nAborting ...\n"
        exit $E_SELINUX
fi

# Check if OS is 32bit and if not allow user to choose to continue or not (for devels). Remove this when RHEL/CENTOS x86_64 is officially supported.
if [ "$ARCH_CHECK" != "i686" ] ; then
        while :
        do
                read -n 1 -p "Your OS architecture ($ARCH_CHECK) is NOT supported yet and $APP_NAME will not work correctly. Continue anyway? (Y/N) " arch_agree
                echo -e "\n"
                case "$arch_agree" in
                        y|Y) break;;
                        n|N) echo -e "Aborting ...\n"
                                exit $E_ARCH;;
                        *) echo -e "Invalid input. Press Y or N.\n";;
                esac
        done
fi

# Check if yum is installed.
if ! [ -f /usr/sbin/yum ] && ! [ -f /usr/bin/yum ] ; then
        echo -e "\a\nThe installer requires YUM to continue. Please install it and try again.\nAborting ...\n"
        exit $E_NOYUM
fi

# Thou shall pass!
yum -y install php php-mysql wget zip unzip
rm -f kloxo-install.zip
wget http://download.lxcenter.org/download/kloxo-install.zip
export PATH=/usr/sbin:/sbin:$PATH
unzip -oq kloxo-install.zip
cd kloxo-install/kloxo-linux
php lxins.php --install-type=master $* | tee kloxo_install.log
