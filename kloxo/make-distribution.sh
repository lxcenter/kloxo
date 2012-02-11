#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2011	LxCenter
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
# This file creates kloxo-[version].zip for distribution from SVN.
# This file creates kloxo-[version].tar.gz for distribution from SVN.
# 
#
# - read version
# - compile c files
# - create zip package
# - create tar.gz package
#
# Changelog
# may.30.2011 - Added tar.gz creation for better compression. .zip can be removed if kloxo installer/source is modified aswell.
# dec.10.2011 - Change fckeditor to ckeditor
#
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

echo "### Compile c files..."

	/bin/sh ./development-create-binaries.sh

echo "### Create zip package..."

	zip -r9y kloxo-$version.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src -x \
	"*httpdocs/commands.php" \
	"*httpdocs/newpass" \
	"*httpdocs/.php.err" \
	"*/CVS/*" \
	"*/.svn/*" \
	"*httpdocs/thirdparty/*" \
	"*httpdocs/htmllib/extjs/*" \
	"*httpdocs/htmllib/ckeditor/*" \
	"*httpdocs/htmllib/yui-dragdrop/*"

echo "### Created ZIP package"

echo "### Create TAR.GZ package"

        tar cvfz kloxo-$version.tar.gz \
        ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO ./src \
	--exclude="commands.php" \
	--exclude="newpass" \
	--exclude=".php.err" \
	--exclude="thirdparty" \
	--exclude="extjs" \
	--exclude="ckeditor" \
	--exclude="yui-dragdrop" \
        --exclude="CVS" \
        --exclude=".svn"

echo "### Created TAR.GZ package"
echo "### Finished"
echo "################################"
ls -lh kloxo-*