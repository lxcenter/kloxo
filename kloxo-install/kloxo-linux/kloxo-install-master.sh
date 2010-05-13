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

if ! [ -f /usr/sbin/yum ] && ! [ -f /usr/bin/yum ] ; then
	echo "You at least need yum installed for this to work..."
	echo "                                "
	exit
fi

yum -y install php php-mysql wget zip unzip
rm -f kloxo-install.zip
wget http://download.lxcenter.org/download/kloxo-install.zip
export PATH=/usr/sbin:/sbin:$PATH
unzip -oq kloxo-install.zip
cd kloxo-install/kloxo-linux
php lxins.php --install-type=master $* | tee kloxo_install.log
