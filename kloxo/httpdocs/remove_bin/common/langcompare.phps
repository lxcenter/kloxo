<?php 

if (!isset($argv[1])) {
	print("Usage: lphp.exe $argv[0] lang \nEg   : lphp.exe $argv[0] fr \n\n");
	print("The language you provide will be compared with the default English, and any missing values will be printed\n");
	exit;
}
// First load the english one.
include_once "lang/en/desclib.php";
$eng_description = $__description;
$__description = null;

include_once "lang/en/messagelib.php";
$eng_information = $__information;
$__information = null;


// Load the other language
include_once "lang/$argv[1]/desclib.php";

foreach($eng_description as $k => $v) {
	if (!isset($__description[$k])) {
		print("__description $k doesn't exist\n");
	} 
}


include_once "lang/$argv[1]/messagelib.php";


foreach($eng_information as $k => $v) {
	if (!isset($__information[$k])) {
		print("__information $k doesn't exist\n");
	} 
}
