<?php
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2014     LxCenter
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
//  This is the scavenge file. It is a master cronjob that runs once every 24h (default at 3:35 AM)
//  The default time is inside kloxo.c
//  Related file: etc/conf/scavenge_time.conf

include_once "htmllib/lib/displayinclude.php";

function scavenge_main()
{
    global $gbl, $sgbl, $login, $ghtml;

    log_scavenge("Fix log dir");
    passthru("$sgbl->__path_php_path ../bin/common/fixlogdir.php");

    log_scavenge("### Starting Scavenge");
    initProgramlib('admin');
    uploadStatsLxCenter();

    if (lxfile_exists("../etc/conf/scavenge_time.conf")) {
        log_scavenge("Found scavenge_time.conf");
    }

    log_scavenge("Collect Traffic");
    passthru("$sgbl->__path_php_path ../bin/gettraffic.php");

    log_scavenge("Collect Quota");
    passthru("$sgbl->__path_php_path ../bin/collectquota.php");

    log_scavenge("Schedule backups");
    passthru("$sgbl->__path_php_path ../bin/common/schedulebackup.php");

    log_scavenge("Clear Sessions");
    passthru("$sgbl->__path_php_path ../bin/common/clearsession.php");

    log_scavenge("Self backup");
    passthru("$sgbl->__path_php_path ../bin/common/mebackup.php");

    log_scavenge("Check Cluster Disk Quota");
    checkClusterDiskQuota();

    $driverapp = $gbl->getSyncClass(null, 'localhost', 'web');
    if ($driverapp === 'lighttpd') {
        log_scavenge("Restarting lighttpd");
        system("service lighttpd restart");
    }

    log_scavenge("InstallApp update");
    passthru("$sgbl->__path_php_path ../bin/installapp-update.phps");

    log_scavenge("Watchdog checks");
    $rs = get_all_pserver();
    foreach ($rs as $r) {
        watchdog::addDefaultWatchdog($r);
    }

    log_scavenge("Collect LxGuard info");
    lxguard::collect_lxguard();

    log_scavenge("Fix MySQL root password");
    fix_all_mysql_root_password();

    log_scavenge("Auto update Kloxo");
    auto_update();

    log_scavenge("### End Scavenge");

    // Wait at least 60 seconds before ending the scavenge
    sleep(60);
}

exit_if_secondary_master();
exit_if_another_instance_running();
scavenge_main();
