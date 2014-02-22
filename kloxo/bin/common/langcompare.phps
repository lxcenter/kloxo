<?php
//    Kloxo, Hosting Control Panel
//
//    Copyright (C) 2000-2009	LxLabs
//    Copyright (C) 2009-2014	LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//
if (!isset($argv[1])) {
    print("Usage: lphp.exe ../bin/common/langcompare.phps lang\n");
    print("The language you provide will be compared with the default English, and any missing values will be printed\n");
    exit;
}

// Load default english desclib
include_once "lang/en/desclib.php";
$eng_description = $__description;
$__description = null;

// Load default english messagelib
include_once "lang/en/messagelib.php";
$eng_information = $__information;
$__information = null;

$eng_emessage = $__emessage;
$__emessage = null;

// Other language
$countDesc = 0;
$countInfo = 0;
$countMes = 0;

// Load the other language desclib
include_once "lang/$argv[1]/desclib.php";

foreach ($eng_description as $k => $v) {
    if (!isset($__description[$k])) {
        print("__description $k doesn't exist\n");
        $countDesc++;
    }
}

// Load the other language messagelib
include_once "lang/$argv[1]/messagelib.php";

foreach ($eng_information as $k => $v) {
    if (!isset($__information[$k])) {
        print("__information $k doesn't exist\n");
        $countInfo++;
    }
}

foreach ($eng_emessage as $k => $v) {
    if (!isset($__emessage[$k])) {
        print("__emessage $k doesn't exist\n");
        $countMes++;
    }
}

print("\n\n");
print("Your lang/" . $argv[1] . "/desclib.php has " . $countDesc . " missing description translations.\n");
print("Your lang/" . $argv[1] . "/messagelib.php has " . $countInfo . " missing information translations.\n");
print("Your lang/" . $argv[1] . "/messagelib.php has " . $countMes . " missing message translations.\n");

