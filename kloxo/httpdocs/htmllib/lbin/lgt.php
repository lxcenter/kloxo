<?php 
if (!file_exists("htmllib/lib/displayinclude.php")) {
	chdir("../..");
}
include_once "htmllib/lib/displayinclude.php";

if (!os_isSelfSystemOrLxlabsUser()) {
	exit;
}
initProgram('admin');
license::doupdateLicense();
print("License Successfully updated\n");
