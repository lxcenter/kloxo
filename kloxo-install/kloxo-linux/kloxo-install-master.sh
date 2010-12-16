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
# TODO: Add options to reset mysql root pass, check free disk space,
#		uninstall. Maybe use "dialog" for ncurses-like functions.
#

APP_NAME=Kloxo
APP_TYPE=Master

SELINUX_CHECK=/usr/sbin/selinuxenabled
SELINUX_CFG=/etc/selinux/config
ARCH_CHECK=$(eval uname -m)

E_SELINUX=50
E_ARCH=51
E_NOYUM=52
E_NOSUPPORT=53
E_HASDB=54
E_REBOOT=55
E_NOTROOT=85

C_OK='\E[47;34m'"\033[1m OK \033[0m\n"
C_NO='\E[47;31m'"\033[1m NO \033[0m\n"
C_MISS='\E[47;33m'"\033[1m UNDETERMINED \033[0m\n"

# Reads yes|no answer from the input 
# 1 question text
# 2 default answer, yes = 1 and no = 0
function get_yes_no {
    local question=
    local input=
    case $2 in 
        1 ) question="$1 [Y/n]: "
            ;;
        0 ) question="$1 [y/N]: "
            ;;
        * ) question="$1 [y/n]: "
    esac

    while :
    do
        read -p "$question" input
        input=$( echo $input | tr -s '[:upper:]' '[:lower:]' )
        if [ "$input" = "" ] ; then
            if [ "$2" == "1" ] ; then
                return 1
            elif [ "$2" == "0" ] ; then
                return 0
            fi
        else
            case $input in
                y|yes) return 1
                    ;;
                n|no) return 0
                    ;;
            esac
        fi
    done
}

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

# Check if selinuxenabled exists
if [ ! -f $SELINUX_CHECK ] ; then
    echo -en "SELinux disabled             " $C_MISS
    echo -e "\a\nThe installer could not determine SELinux status.\nIf you are sure it is DISABLED, you may proceed."
    get_yes_no "Continue?" 0
    if [ "$?" -eq "0" ] ; then 
        echo -e "Aborting ...\n"
        exit $E_SELINUX
    fi
else
    # Check if SElinux is enabled from exit status. 0 = Enabled; 1 = Disabled;
    eval $SELINUX_CHECK
    OUT=$?
    if [ $OUT -eq "0" ] ; then
        echo -en "SELinux disabled             " $C_NO
        echo -e "\a\n$APP_NAME cannot be installed or executed with SELinux enabled. The installer can disable it, but a reboot will be required.\n"
        echo -e "You will have to restart the installer again after reboot.\n"
        get_yes_no "Do you want to disable SELinux and reboot?" 1
        if [ "$?" -eq "1" ] ; then 
            echo -e "Disabling SELinux ...\n"
            cp --backup=t $SELINUX_CFG $SELINUX_CFG.old
            echo "SELINUX=disabled" > $SELINUX_CFG
            echo -e "SELinux disabled successfully\n"
            echo -e "Rebooting ...\n"
            reboot
            exit $E_REBOOT
        else
            echo -e "Please DISABLE SELinux manually and try again.\nAborting ...\n"
            exit $E_SELINUX
        fi
    elif [ $OUT -eq "1" ] ; then
        echo -en "SELinux disabled             " $C_OK
    fi
fi

# Check if OS is 32bit and if not allow user to choose to continue or not (for devels).
# Remove this when RHEL/CENTOS x86_64 is officially supported or add the arch to prevent people from installing in ARM.
if [ "$ARCH_CHECK" != "i686" ] ; then
    echo -en "\aArchitecture supported ($ARCH_CHECK)" $C_NO "\n"
    echo -e "Your OS architecture ($ARCH_CHECK) is NOT officially supported yet and $APP_NAME may not work correctly."
    get_yes_no "Continue anyway?" 0
    if [ "$?" -eq "0" ] ; then 
        echo -e "Aborting ...\n"
        exit $E_ARCH
    fi
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
echo -e "\n\n    Note some file downloads may not show a progress bar so please, do not interrupt the process."
echo -e "    When it's finished, you will be presented with a welcome message and further instructions.\n\n"

read -n 1 -p "Press any key to continue ..."

yum -y install php php-mysql wget zip unzip
rm -f kloxo-install.zip
wget http://download.lxcenter.org/download/kloxo-install.zip
export PATH=/usr/sbin:/sbin:$PATH
unzip -oq kloxo-install.zip
cd kloxo-install/kloxo-linux
php lxins.php --install-type=master $* | tee kloxo_install.log