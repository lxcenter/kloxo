<?php 

include_once "htmllib/lib/include.php";
$list = os_get_allips();
$list[] = "127.0.0.1/8";
change_config("/etc/mararc", "ipv4_bind_addresses", implode(",", $list));
