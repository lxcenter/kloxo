<?php 

chdir("../../");

include_once "htmllib/lib/include.php"; 


if ($ghtml->frm_clientname !== 'admin') {
	print("__error_clientname_has_to_be_admin\n");
	exit;
}

if (!check_raw_password('client', 'admin', $ghtml->frm_password)) {
	print("__error_wrong_password\n");
	exit;
}

try {
	rl_exec_get(null, 'localhost', 'update_self', null);
} catch (Exception $e) {
	print("__error_{$e->getMessage()}\n");
	exit;
}

print("__success_upgrade\n");

