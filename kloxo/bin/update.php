<?php 

include_once "htmllib/lib/include.php";
// include_once "lib/updatelib.php";
include_once "htmllib/lib/updatelib.php";


exit_if_not_system_user();
exit_if_another_instance_running();

update_main();



