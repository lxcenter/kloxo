#!/bin/sh
VERSION=6.1.5
cd ../../
find . -iname "*.php*" | egrep -v "other-thirdparty|.svn" | xargs xgettext --output ./httpdocs/l18n/new.pot --language=PHP

cd httpdocs/l18n

replace "Project-Id-Version: PACKAGE VERSION" "Project-Id-Version: Kloxo $VERSION" -- new.pot
replace "Report-Msgid-Bugs-To: " "Report-Msgid-Bugs-To: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Last-Translator: FULL NAME <EMAIL@ADDRESS>" "Last-Translator: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Language-Team: LANGUAGE <LL@li.org>" "Language-Team: LxCenter <contact@lxcenter.org>" -- new.pot
replace "Content-Type: text/plain; charset=CHARSET" "Content-Type: text/plain; charset=UTF-8" -- new.pot
replace "# SOME DESCRIPTIVE TITLE." "# Kloxo master POT file" -- new.pot
replace "# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER" "# Copyright (C) 2011 LxCenter" -- new.pot
replace "# This file is distributed under the same license as the PACKAGE package." "# License AGPL-V3" -- new.pot
replace "# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR." "# http://www.lxcenter.org/" -- new.pot

cp new.pot kloxo.pot

msgmerge -U ./en_US/LC_MESSAGES/kloxo.po kloxo.pot
msgmerge -U ./es_ES/LC_MESSAGES/kloxo.po kloxo.pot
msgmerge -U ./nl_NL/LC_MESSAGES/kloxo.po kloxo.pot

msgfmt ./en_US/LC_MESSAGES/kloxo.po -o ./en_US/LC_MESSAGES/kloxo.mo
msgfmt ./es_ES/LC_MESSAGES/kloxo.po -o ./es_ES/LC_MESSAGES/kloxo.mo
msgfmt ./nl_NL/LC_MESSAGES/kloxo.po -o ./nl_NL/LC_MESSAGES/kloxo.mo

