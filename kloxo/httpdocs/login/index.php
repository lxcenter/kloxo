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



function index_print_header()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$progname = $sgbl->__var_program_name;
	?> 
<table width=100%  height=" 64" border="0" valign="top" align="center" cellpadding="0" cellspacing="0">
<tr>
<td height="64" width="100%" background="/img/header/header_01.gif">
<table cellpadding=0 cellspacing=0 border=0>
<tr><td height=20 colspan=2></td></tr>
<tr><td width=15></td><td></td></tr>
</table>
</td>
<td height="64" width="20%"><img src="/img/header/header_02.gif" width="194" height="64"></td>
<td width="20%" height="64"><img src="/img/header/<?php echo $progname ?>-header.gif" width="238" height="64"></td>
<td width="20%" height="64"><img src="/img/header/header_04.gif" width="10" height="64"></td></tr>
<tr><td width="100%" colspan=5 bgcolor="#003366" width="10" height="2"></td></tr>
</table>
<?php 

}
