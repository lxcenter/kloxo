<?php 
include_once "htmllib/lib/include.php"; 

lxfile_mv_rec("/var/tmp/graylist.d/", "/var/tmp/tmp_graylist.d");
lxfile_mkdir("/var/tmp/graylist.d/");
createRestartFile("xinetd");

lxfile_rm_rec("/var/tmp/tmp_graylist.d/");


