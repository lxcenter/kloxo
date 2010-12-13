#!/bin/sh
#
# This file creates kloxo-[version].zip for distribution from SVN.
# 
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
"*httpdocs/thirdparty/*" \
"*httpdocs/htmllib/extjs/*" \
"*httpdocs/htmllib/fckeditor/*" \
"*httpdocs/htmllib/yui-dragdrop/*"
#
echo "### Finished"
echo "################################"
ls -lh kloxo-*.zip
#

