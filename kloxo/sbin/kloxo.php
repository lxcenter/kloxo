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
// This file is executed when lowmem flag is disabled.
// When lowmem flaq is enabled kloxo.exe is executed.
//
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/lxserverlib.php";

function timed_execution()
{
	global $global_dontlogshell;

	$global_dontlogshell = true;

    // execute every minute
	timed_exec(2,  "checkRestart");
    // execute every 10 minutes
	timed_exec(2 * 5, "execSisinfoc");

	$global_dontlogshell = false;
}

function execSisinfoc()
{
	log_log("cron_exec","Starting SISInfoC\n");
	lxshell_background("__path_php_path", "../bin/sisinfoc.php");
}

kill_and_save_pid('lxserver');

debug_for_backend();

lxserver_main();
