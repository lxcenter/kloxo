#!/bin/sh
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
php lxins.php --install-type=slave $* | tee kloxo_install.log
