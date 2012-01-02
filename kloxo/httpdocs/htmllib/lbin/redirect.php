<?php 

chdir("../..");
include_once "htmllib/lib/displayinclude.php";

initProgram();

$name = $ghtml->frm_redirectname;


if (!$ghtml->frm_redirectaction) {
	$ghtml->print_redirect_back('you_didnt_specify_an_action', 'nname');
}

$action = base64_decode($ghtml->frm_redirectaction);

$url = str_replace("__tmp_lx_name__", $name, $action);

if (strpos(trim($url), "http") === 0) {
	$ghtml->print_redirect_back('external_url_not_allowed', 'nname');
}

header("Location: $url");
