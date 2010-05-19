<?php 

exit_if_not_system_user();

system("/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/");
system("/usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php {$argv[1]}");
exit;

