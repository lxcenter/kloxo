<?php 


if (!$ghtml->iset("frm_action")) {
	die("Action Not set <br> ");
}

$cgi_action = "__ac_desc_" . $ghtml->frm_action;

try {
	$cgi_action($gbl->__c_object);
	$login->was();
	$gbl->unsetSessionV('__tmp_redirect_var');
} catch (Exception $e) {
	//save_login();
	$gbl->setSessionV('__tmp_redirect_var', $ghtml->__http_vars);
	$gbl->c_session->write();
	if (is_array($e->variable)) {
		$evlist = implode(",", $e->variable);
	} else {
		$evlist = $e->variable;
	}
	$ghtml->print_redirect_back($e->getMessage(), $evlist, $e->value);
	exit;
}


// If redirecting, too, ssession wont be saved....
if (isset($gbl->__this_function)) {
	dprint("Calling $gbl->__this_function <br> <br> ");
	// workaround for the following php bug:
	//   http://bugs.php.net/bug.php?id=47948
	//   http://bugs.php.net/bug.php?id=51329
	if (is_array($gbl->__this_function) && count($gbl->__this_function) > 0) {
		$class = $gbl->__this_function[0];
		class_exists($class);
	}
	// ---
	call_user_func_array($gbl->__this_function, $gbl->__this_functionargs);
}

if ($gbl->__this_redirect) {
	save_login();
	$ghtml->print_redirect($gbl->__this_redirect);
}


// Thsi is a fucking misnomer.. It jsut saves the lx_http_refer, ssession variables... And also saves the login, if it exists.
exit_program();

if (function_exists("after_exit_program")) {
	after_exit_program();
}



