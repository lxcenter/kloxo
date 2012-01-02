<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$server = $login->getfromList('pserver', 'localhost');
$server->getandwriteipaddress();
