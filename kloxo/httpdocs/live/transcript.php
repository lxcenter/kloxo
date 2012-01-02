<?php 
include("common.php");

if ($g_login) {
	include_once "lib/define.php";
	include_once "htmllib/phplib/common.inc";
	include_once "lib/common.inc";
	initprogram();
	if (!$login->isAdmin()) {
		Print("Not Admin");
		exit(0);
	}
	if (if_demo()) {
		print("Demo... Not Showing..");
		exit;
	}
}

$list = lfile("__path_program_etc/livetranscript.txt");

foreach($list as $l) {
	if (preg_match("/:\s+lx/", $l)) {
		continue;
	}

	print($l . " <br> ");
}


