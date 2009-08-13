<?php 

include_once "htmllib/lib/include.php"; 

while (true) {
	print("$");
	flush();
	$string = fread(STDIN, 8096);
	print($string);
}
