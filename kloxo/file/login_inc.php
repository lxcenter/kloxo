
<link href=/htmllib/css/common.css rel=stylesheet type=text/css>
<script language=javascript src="/htmllib/js/login.js"></script>
<script language=javascript src="/htmllib/js/preop.js"></script>

<?php 

chdir("..");
include_once "htmllib/lib/displayinclude.php";

init_language();
$cgi_clientname = $ghtml->frm_clientname; 
$cgi_class = $ghtml->frm_class; 
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd; 
$cgi_email = $ghtml->frm_email;

$cgi_classname = 'client';
if ($cgi_class) {
	$cgi_classname = $cgi_classname;
}
ob_start();
include_once "htmllib/lib/indexcontent.php";


