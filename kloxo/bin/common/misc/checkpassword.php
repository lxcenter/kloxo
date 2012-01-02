<?php 

$c = crypt($argv[1], $argv[2]);
if ( $c === $argv[2]) {
	print("Password Match \n");
} else {
	print("No Password Match: $c, $argv[2] \n");
}
