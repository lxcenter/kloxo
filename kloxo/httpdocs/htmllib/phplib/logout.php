<?php 

chdir("../../");
include_once "htmllib/lib/displayinclude.php";

logout_main();

function logout_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	initProgram();

	clear_all_cookie();

	$cl = $login->getList("ssession");
	Utmp::updateUtmp($gbl->c_session->nname, $login, "Logout");

	$gbl->c_session->delete();
	$gbl->c_session->was();
	if ($gbl->c_session->ssl_param) {
		$ghtml->print_redirect($gbl->c_session->ssl_param['backurl']);
	} else if ($gbl->c_session->consuming_parent) {
		$ret = $gbl->getSessionV('return_url');
		$ghtml->print_redirect($ret);
	} else {
		$ghtml->print_redirect_self("/login/");
	}
}

