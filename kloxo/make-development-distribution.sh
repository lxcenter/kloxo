#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2012	LxCenter
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
# This file creates kloxo-[version]-R[svn].zip for development testing
# 
#
# - read version
# - compile c files
# - create zip package
######
#
# Script is broken since the move to GitHub. Need to be rewritten.
#
#
printUsage() {
 echo "Usage:"
 echo "make-development-distribution.sh <n> <n> <n> ( Where n are numbers.   Example: 6 1 0 )"
return
}

if [ $# -eq 0 ] ; then
	printUsage
	exit 1
fi

VMAJOR="$1"
VMINOR="$2"
VRELEASE="$3"

# create a working file
rm -f sgbl-work.temp
rm -f sgbl-work.org
cp /usr/local/lxlabs/kloxo/httpdocs/lib/sgbl.php sgbl-work.temp
cp /usr/local/lxlabs/kloxo/httpdocs/lib/sgbl.php sgbl-work.org

# Replace information

# MAJOR
sed -i "s/__ver_major = \"6\";/__ver_major = \"$VMAJOR\";/g" sgbl-work.temp

# MINOR
sed -i "s/__ver_minor = \"0\";/__ver_minor = \"$VMINOR\";/g" sgbl-work.temp
# RELEASE
sed -i "s/__ver_release = \"2086\";/__ver_release = \"$VRELEASE\";/g" sgbl-work.temp

# TYPE
sed -i "s/__ver_type = \"production\";/__ver_type = \"development\";/g" sgbl-work.temp

# Get SVN Revision
SVNVERSION=`svn info -R | grep "Revision\:" | sort -k2nr | head -n1 | awk '{print $2}'`

# EXTRA
sed -i "s/__ver_extra = \"Stable\";/__ver_extra = \"SVN-R$SVNVERSION\";/g" sgbl-work.temp

# Copy temp file into real path
cp sgbl-work.temp /usr/local/lxlabs/kloxo/httpdocs/lib/sgbl.php

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
KVERSION=`/script/version`
rm -f kloxo-$KVERSION-R$SVNVERSION.zip
#
echo "### Compile c files..."
/bin/sh ./development-create-binaries.sh
#
echo "### Create zip package..."
# Package part
zip -r9yq kloxo-$KVERSION-R$SVNVERSION.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src -x \
"*httpdocs/commands.php" \
"*httpdocs/newpass" \
"*httpdocs/.php.err" \
"*/CVS/*" \
"*/.svn/*" \
"*/.git/*" \
"*httpdocs/thirdparty/*" \
"*httpdocs/htmllib/extjs/*" \
"*httpdocs/htmllib/fckeditor/*" \
"*httpdocs/htmllib/yui-dragdrop/*"

# Move back original file.
cp sgbl-work.org /usr/local/lxlabs/kloxo/httpdocs/lib/sgbl.php

# Clean up

rm -f sgbl-work.temp
rm -f sgbl-work.org

echo "### Finished"
echo "################################"
ls -lh kloxo-*.zip
#


