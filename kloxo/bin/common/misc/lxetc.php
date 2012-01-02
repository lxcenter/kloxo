<?php 

include_once "htmllib/lib/include.php"; 
include_once "htmllib/lib/updatelib.php";

fixZshEtc();
system("cd ~/.etc/bin ; mv vihist.txt vihist.c ; cc vihist.c -o vihist ; ");

