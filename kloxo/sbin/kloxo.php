<?php
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/lxserverlib.php";

kill_and_save_pid('lxserver');
debug_for_backend();


lxserver_main();



function timed_execution()
{
	global $global_dontlogshell;
	$global_dontlogshell = true;
	if (windowsOS()) { return; }
	timed_exec(2,  "checkRestart");
	timed_exec(2 * 5, "execSisinfoc"); 
	$global_dontlogshell = false;

}

function execSisinfoc()
{
	dprint("execing sisinfoc\n");
	lxshell_background("__path_php_path", "../bin/sisinfoc.php");
}

