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
#
#
# This file creates other thirdparty files for distribution from SVN.
# 
#
# - create zip packages
######
#
# Pack:
# -lxawstats		(AWStats)
# -lxwebmail		(Roundcube/Horde)
# -kloxophp		(Zend/Ioncube loaders 32 bit)
# -kloxophpsixfour	(Zend/Ioncube loaders 64 bit)
#
###############################

	echo "################################"
	echo "### Start packaging Kloxo Other Thirdparty tools."
	echo "### Read current versions from download center..."
	kloxophp=`curl --silent http://download.lxcenter.org/download/version/kloxophp`
        kloxophpsixfour=`curl --silent http://download.lxcenter.org/download/version/kloxophpsixfour`
        lxawstats=`curl --silent http://download.lxcenter.org/download/version/lxawstats`
        lxwebmail=`curl --silent http://download.lxcenter.org/download/version/lxwebmail`

         if [ "$kloxophp" == "" ] || [ "$kloxophpsixfour" == "" ] || [ "$lxawstats" == "" ] || [ "$lxwebmail" == "" ] ; then
         echo "## Could not read versions from download center. Aborted!"
	 exit 1
         fi
	
	# Set versions + 1

        ((kloxophp++))
        ((kloxophpsixfour++))
        ((lxawstats++))
        ((lxwebmail++))

	echo "Packaging kloxophp $kloxophp"
	echo "Packaging kloxophpsixfour $kloxophpsixfour"
	echo "Packaging lxawstats $lxawstats"
	echo "Packaging lxwebmail $lxwebmail"

	echo "### Packaging version: kloxophp$kloxophp.tar.gz"
	rm -f kloxophp$kloxophp.tar.gz
	echo "### Create package...";
        
	echo "## Zend 32";
	cd ./other-thirdparty/zend-32
	tar cvfz ../../kloxophp$kloxophp.tar.gz \
       	. \
	--exclude="CVS" \
	--exclude=".svn"

	echo "## Ioncube 32";
	cd ../ioncube-32
	tar rvfz ../../kloxophp$kloxophp.tar.gz \
       	. \
	--exclude="CVS" \
	--exclude=".svn"

        echo "### Packaging version: kloxophpsixfour$kloxophpsixfour.tar.gz"
        rm -f kloxophpsixfour$kloxophpsixfour.tar.gz
        echo "### Create package...";

        echo "## Zend 64";
        cd ../zend-64
        tar cvfz ../../kloxophpsixfour$kloxophpsixfour.tar.gz \
        . \
        --exclude="CVS" \
        --exclude=".svn"

        echo "## Ioncube 64";
        cd ../ioncube-64
        tar rvfz ../../kloxophpsixfour$kloxophpsixfour.tar.gz \
        . \
        --exclude="CVS" \
        --exclude=".svn"

        echo "### Packaging version: lxwebmail$lxwebmail.tar.gz"
        rm -f lxwebmail$lxwebmail.tar.gz
        echo "### Create package...";

        echo "## Webmail apps";
        cd ../webmail
        tar cvfz ../../lxwebmail$lxwebmail.tar.gz \
        . \
        --exclude="CVS" \
        --exclude=".svn"

        echo "## Awstats";
        cd ../awstats
        tar cvfz ../../lxawstats$lxawstats.tar.gz \
        . \
        --exclude="CVS" \
        --exclude=".svn"

        cd ../../

	echo "### Finished!"
	echo "################################"
	ls -lh *.tar.gz


