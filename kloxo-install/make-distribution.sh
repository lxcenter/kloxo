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
# This file creates kloxo-install.zip for download server.
# 
######
echo "################################"
echo "### Start packaging"
cd ../
rm -f ../kloxo-install/kloxo-install.zip
echo "### Create zip package..."
#
zip -r9 ./kloxo-install/kloxo-install.zip ./kloxo-install -x \
"*/CVS/*" \
"*/.svn/*" \
"*.svn/*" \
"*.CVS/*" \
"*.*~" \
"*/make-distribution.sh"
#
echo "### Finished"
echo "################################"
cd ./kloxo-install
ls -lh kloxo-install.zip
#
