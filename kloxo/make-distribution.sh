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
# This file creates kloxo-[version].zip for distribution.
#
# Version 1.1 DT04022014
# * Added a few dir exclusions, makes package smaller.
#
# - read version
# - compile c files
# - create zip package
######
echo "################################"
echo "### Start packaging"
echo "### read version..."
# Read version
# Please note, this must be a running machine with SVN version!
if ! [ -f /script/version ] ; then
        echo "## Packaging failed. No /script/version found."
	echo "## Are you sure you are running a development version?"
	echo "### Aborted."
	echo "################################"
        exit
fi
version=`/script/version`
rm -f kloxo-$version.zip
#
echo "### Compile c files..."
/bin/sh ./development-create-binaries.sh
#
echo "### Create zip package..."
# Package part
zip -r9y kloxo-$version.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src -x \
"*httpdocs/commands.php" \
"*httpdocs/newpass" \
"*httpdocs/.php.err" \
"*/CVS/*" \
"*/.svn/*" \
"*/.git/*" \
"*/.etc/*" \
"*file/cache/*" \
"*httpdocs/download/*" \
"*httpdocs/help/*" \
"*httpdocs/webdisk/*" \
"*httpdocs/img/installapp/*" \
"*httpdocs/thirdparty/*" \
"*httpdocs/htmllib/extjs/*" \
"*httpdocs/htmllib/fckeditor/*" \
"*httpdocs/htmllib/yui-dragdrop/*"
#
echo "### Finished"
echo "################################"
ls -lh kloxo-*.zip
#

