#!/bin/sh
cd ../../
find . -iname "*.php" | egrep -v "other-thirdparty" | xargs xgettext -o ./httpdocs/l18n/new.pot
cd httpdocs/l18n

replace "Project-Id-Version: PACKAGE VERSION" "Project-Id-Version: Kloxo 6" -- new.pot
replace "Report-Msgid-Bugs-To: " "Report-Msgid-Bugs-To: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Last-Translator: FULL NAME <EMAIL@ADDRESS>" "Last-Translator: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Language-Team: LANGUAGE <LL@li.org>" "Language-Team: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Content-Type: text/plain; charset=CHARSET" "Content-Type: text/plain; charset=utf-8" -- new.pot
replace "# SOME DESCRIPTIVE TITLE." "# Kloxo master POT file" -- new.pot
replace "# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER" "# Copyright (C) 2011 LxCenter" -- new.pot
replace "# This file is distributed under the same license as the PACKAGE package." "# License AGPL-V3" -- new.pot
replace "# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR." "# http://www.lxcenter.org/" -- new.pot
ls -lh *.pot
echo "Now replace kloxo.pot with new.pot to make it active."
