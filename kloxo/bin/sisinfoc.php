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
include_once "htmllib/lib/include.php"; 
include_once "htmllib/lib/lxguardincludelib.php";


function collect_traffic()
{
	$flfile = "__path_program_etc/last_sisinfoc";
	$ret = lfile_get_unserialize($flfile);
	$ret['time'] = time();
	lfile_put_serialize($flfile, $ret);
}

$global_dontlogshell = true;

exit_if_secondary_master();

exit_if_another_instance_running();

debug_for_backend();

watchdog__sync::watchRun();

if ($sgbl->is_this_master()) {
    $gbl->is_master = true;
    initProgram('admin');
    run_mail_to_ticket();
}

monitor_load();

collect_traffic();

lxguard_main();

add_to_log("/var/log/kloxo/smtp.log");

add_to_log("/var/log/kloxo/courier");

