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
#
# This file creates kloxo-theme-default.zip
# This file creates kloxo-theme-feather.zip
# Those are used to make the rpm packages.
#
# Version 1.0 - DT12022014
#
# - create packages
######
#
# Pack:
# httpdocs/img/skin/kloxo/default
# httpdocs/img/skin/kloxo/feather
#
###############################

	echo "################################"
	echo "### Start packaging Kloxo themes."

	echo "### Packaging version: kloxo-theme-default.zip"
	rm -f kloxo-theme-default.zip

	echo "### Create zip package...";
	zip -qr9 kloxo-theme-default.zip \
	./httpdocs/img/skin/kloxo/default \
	-x \
	"*/CVS/*" \
    "*/.git/*" \
	"*/.svn/*"

	echo "### Packaging version: kloxo-theme-feather.zip"
	rm -f kloxo-theme-feather.zip

	echo "### Create zip package...";
	zip -qr9 kloxo-theme-feather.zip \
	./httpdocs/img/skin/kloxo/feather \
	-x \
	"*/CVS/*" \
    "*/.git/*" \
	"*/.svn/*"

	echo "### Finished!"
	echo "################################"
	ls -lh kloxo-theme-*.zip
