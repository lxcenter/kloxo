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
#
# This file creates kloxo-thirdparty.[version].zip for distribution from SVN.
# 
#
# - create zip package
######
printUsage() {
 echo "Usage:"
 echo "make-distribution.sh --version number (Where number is the version to be created)"
 echo "make-distribution.sh --help (To show this help)"
return
}

StartPackaging() {
	echo "################################"
	echo "### Start packaging Kloxo Thirdparty tools."
	echo "### Read version..."
	VERSION=$1
	if [ $VERSION == "" ] ; then
	 echo "## Could not read version, please add a number after --version"
	 exit 1
	fi
	echo "### Packaging version: kloxo-thirdparty.$VERSION.zip"
	rm -f kloxo-thirdparty.$VERSION.zip

	echo "### Create zip package...";
	zip -qr9 kloxo-thirdparty.$VERSION.zip ./httpdocs \
	-x \
	"*/CVS/*" \
	"*/.svn/*"
	echo "### Finished!"
	echo "################################"
	ls -lh kloxo-thirdparty.*.zip
return
}

if [ $# -eq 0 ] ; then
printUsage
exit 1
fi

case $1 in
--version) StartPackaging $2;;
--help) printUsage; exit 1;;
*) printUsage; exit 1;;
esac

