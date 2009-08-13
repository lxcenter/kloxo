#!/bin/sh

if ! [ -f /usr/sbin/yum ] && ! [ -f /usr/bin/yum ] ; then
      echo You at least need yum installed for this to work...
	  echo Please contact our support personnel
	  echo "                                "
	  exit
fi

yum -y install php php-mysql wget zip unzip
rm -f program-install.zip
wget http://download.lxlabs.com/download/program-install.zip

export PATH=/usr/sbin:/sbin:$PATH
unzip -oq program-install.zip
cd program-install/kloxo-linux
php lxins.php --install-type=master $* | tee kloxo_install.log




