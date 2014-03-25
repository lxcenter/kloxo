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
//    This file is called by scavenge.php
//
include_once "htmllib/lib/include.php";

function fixlogdir_main()
{
	global $gbl, $sgbl, $login, $ghtml;

	$progname = $sgbl->__var_program_name;
	$logl = lscandir_without_dot("../log");
	lxfile_mkdir("../processed_log");

	// DT25032014 - Project #1103
	// Do not delete the access_log for security analyzing by System admins
	// @ lunlink("../log/access_log");
	// Do not delete the PHP error log (it is rotated by system logrotate)
	// @ lunlink("/usr/local/lxlabs/ext/php/error.log");

	$dir = getNotexistingFile("../processed_log", "proccessed");
	system("mv ../log ../processed_log/$dir");

	mkdir("../log");

	// DT25032014 - Changed from 6 to 30 days so System admins can analyze if there are problems.
	$list = lscandir_without_dot("../processed_log");
	foreach ( $list as $l ) {
		remove_directory_if_older_than_a_day("../processed_log/$l", 30);
	}
	foreach ( $logl as $l ) {
		lxfile_touch("../log/$l");
	}


	lxfile_generic_chown_rec("../log", "lxlabs:lxlabs");

	//
	// Related to Issue #15
	//
	lxfile_generic_chmod_rec("../log", "0640");
	lxfile_generic_chmod_rec("../processed_log", "0640");

	lxfile_generic_chmod("../log", "0700");
	lxfile_generic_chmod("../processed_log", "0700");

	lxfile_generic_chmod("../log/lighttpd_error.log", "0644");
	lxfile_generic_chmod("../log/access_log", "0644");

	lxfile_generic_chown("../log/lighttpd_error.log", "lxlabs:root");
	lxfile_generic_chown("../log/access_log", "lxlabs:root");
	//

	// Restart ourself.
	os_restart_program();
}

fixlogdir_main();
