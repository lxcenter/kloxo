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
# LxCenter - Kloxo Patcher
#
# Version: 1.0 (2011-08-02 - by mustafa.ramadhan@lxcenter.org)
#

if [ "$#" == 0 ] ; then
	echo
	echo " -------------------------------------------------------------------"
	echo "  format: sh $0 --type=[]"
	echo " -------------------------------------------------------------------"
	echo "  --type - master or slave"
	echo
	echo " * Run kloxo-packer.sh to make kloxo packs (local copy)"
	echo
	exit;
fi

patchver=`cat ./patch/patch-version`
kloxover=`sh /script/version`

if [ "$patchver" ==  "$kloxover" ] ; then
	echo "Kloxo version $kloxover equal to patch version $patchver"
	echo
else
	echo "Kloxo version $kloxover but patch version $patchver"
	echo "... the end"
	echo
	exit;
fi

echo "Start patch..."
echo

echo "- Set ownership and permissions"
chown -R lxlabs:lxlabs ./patch/kloxo/
find ./patch/kloxo/ -type f -name \"*.php*\" -exec chmod 644 {} \;
find ./patch/kloxo/ -type d -exec chmod 755 {} \;

echo "- Copy patch file"
cp -rf ./patch/kloxo/* /usr/local/lxlabs/kloxo
cp -rf /usr/local/lxlabs/kloxo/pscript /script
cp -rf /usr/local/lxlabs/kloxo/httpdocs/htmllib/script /script


if [ -f /usr/lib64 ] ; then
	echo "- Set symlink for 64bit version"
	mkdir -p /usr/lib64/php
	ln -s /usr/lib64/php /usr/lib/php
	mkdir -p /usr/lib64/httpd
	ln -s /usr/lib64/httpd /usr/lib/httpd
	mkdir -p /usr/lib64/lighttpd
	ln -s /usr/lib64/lighttpd /usr/lib/lighttpd
	mkdir -p /usr/lib64/kloxophp
	ln -s /usr/lib64/kloxophp /usr/lib/kloxophp
fi

echo "- Run some script files for fixed"
sh /script/upcp
sh /script/cleanup
sh /script/fixweb
sh /script/fixdns
sh /script/fixmail
sh /script/fixwebmail

echo
echo "... the end"
echo