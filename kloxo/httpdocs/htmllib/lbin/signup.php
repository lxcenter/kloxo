<?php 

chdir("../..");
include_once "htmllib/lib/include.php"; 
include_once "htmllib/phplib/display.php";

initProgram('admin');
do_addform($login, "client", array('var' => 'cttype', 'val' => "customer"));

