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
# LxCenter - Kloxo Packer
#
# Version: 1.0 (2011-08-02 - by mustafa.ramadhan@lxcenter.org)
#

if [ "$#" == 0 ] ; then
	echo
	echo " -------------------------------------------------------------------"
	echo "  format: sh $0 --svnpath=[]"
	echo " -------------------------------------------------------------------"
	echo "  --svnpath - ex: tags/6.1.7 or branches/6.1.x or trunk"
	echo
	echo "  * Browse http://svn.lxcenter.org/svn/kloxo/ to find kloxo version"
	echo "  * This packer only pack main kloxo package from svn"
	echo "  * Thirdparty packages download directly for latest version"
	echo "  * Run kloxo-installer.sh for kloxo install and must be the same"
	echo "       place with local copy"
	echo
	exit;
fi

echo "Start pack..."

request1=$1
kloxo_path=${request1#--svnpath\=}

echo "download kloxo svn from "$kloxo_path

mkdir -p ./ready
mkdir -p ./combo

mkdir -p ./current
cd ./current

svn checkout http://svn.lxcenter.org/svn/kloxo/$kloxo_path/kloxo
svn checkout http://svn.lxcenter.org/svn/kloxo/$kloxo_path/kloxo-install

cd ../

cp -rf ./current/* ./combo

cp -rf ./patch/* ./combo

rm -rf `find ./combo -type d -name .svn`
rm -rf `find ./combo -type d -name CVS`

cd ./combo

zip -r9y kloxo-install.zip ./kloxo-install

mv -f kloxo-install.zip ../ready

cd ./kloxo

cd ./src/
make
cd ../

zip -r9y kloxo-current.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src -x \
	"*httpdocs/commands.php" \
	"*httpdocs/newpass" \
	"*httpdocs/.php.err" \
	"*/CVS/*" \
	"*/.svn/*" \
	"*httpdocs/thirdparty/*" \
	"*httpdocs/htmllib/extjs/*" \
	"*httpdocs/htmllib/fckeditor/*" \
	"*httpdocs/htmllib/yui-dragdrop/*"

mv -f kloxo-current.zip ../../ready

cd ../../ready

thirdpartyver=$(curl -L http://download.lxcenter.org/download/thirdparty/kloxo-version.list)
if [ ! -f kloxo-thirdparty.$thirdpartyver.zip ] ; then
	echo $thirdpartyver > kloxo-thirdparty-version
	wget http://download.lxcenter.org/download/kloxo-thirdparty.$thirdpartyver.zip
fi

kloxophpver=$(curl -L http://download.lxcenter.org/download/version/kloxophp)
if [ ! -f kloxophp$kloxophpver.tar.gz ] ; then
	echo $kloxophpver > kloxophp-version
	wget http://download.lxcenter.org/download/kloxophp$kloxophpver.tar.gz
fi

kloxophpsixfourver=$(curl -L http://download.lxcenter.org/download/version/kloxophpsixfour)
if [ ! -f kloxophpsixfour$kloxophpsixfourver.tar.gz ] ; then
	echo $kloxophpsixfourver > kloxophpsixfour-version
	wget http://download.lxcenter.org/download/kloxophpsixfour$kloxophpsixfourver.tar.gz
fi

lxwebmailver=$(curl -L http://download.lxcenter.org/download/version/lxwebmail)
if [ ! -f lxwebmail$lxwebmailver.tar.gz ] ; then
	echo $lxwebmailver > lxwebmail-version
	wget http://download.lxcenter.org/download/lxwebmail$lxwebmailver.tar.gz
fi

lxawstatsver=$(curl -L http://download.lxcenter.org/download/version/lxawstats)
if [ ! -f lxawstats$lxawstatsver.tar.gz ] ; then
	echo $lxawstatsver > lxawstats-version
	wget http://download.lxcenter.org/download/lxawstats$lxawstatsver.tar.gz
fi

cp ../combo/kloxo-install/kloxo-installer.sh ./

echo "... the end"
echo
