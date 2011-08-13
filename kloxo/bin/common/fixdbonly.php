<?php 
include_once "htmllib/lib/include.php"; 
include_once "lib/updatelib.php";

if (!lxfile_exists("__path_slave_db")) {
	updateDatabaseProperly();
	fixDataBaseIssues();
}
