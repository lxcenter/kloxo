1.<?php
2.
3.chdir('..');
4.include_once 'htmllib/lib/displayinclude.php';
5.
6.init_language();
7.$cgi_clientname = $ghtml->frm_clientname;
8.$cgi_class = $ghtml->frm_class;
9.$cgi_password = $ghtml->frm_password;
10.$cgi_forgotpwd = $ghtml->frm_forgotpwd;
11.$cgi_email = $ghtml->frm_email;
12.$cgi_classname = 'client';
13.if($cgi_class) $cgi_classname = $cgi_classname;
14.
15.ob_start();
16.include_once 'htmllib/lib/indexcontent.php';
17.
18.function index_print_header()
19.{
20.    global $gbl, $sgbl, $login, $ghtml;
21.
22.    $progname = $sgbl->__var_program_name;
23.    ?>
24.    <table width="100%" height="64" border="0" valign="top" align="center" cellpadding="0" cellspacing="0">
25.        <tr>
26.            <td height="64" width="100%" background="/img/header/header_01.gif">
27.                <table cellpadding="0" cellspacing="0" border="0">
28.                <tr>
29.                    <td height="20" colspan="2">
30.                    </td>
31.                </tr>
32.                <tr>
33.                    <td width="15">
34.                    </td>
35.                    <td>
36.                    </td>
37.                </tr>
38.                </table>
39.            </td>
40.            <td height="64" width="20%">
41.                <img src="/img/header/header_02.gif" width="194" height="64">
42.            </td>
43.            <td width="20%" height="64">
44.                <img src="/img/header/<?php echo $progname ?>-header.gif" width="238" height="64">
45.            </td>
46.            <td width="20%" height="64">
47.                <img src="/img/header/header_04.gif" width="10" height="64">
48.            </td>
49.        </tr>
50.        <tr>
51.            <td width="100%" colspan="5" bgcolor="#003366" width="10" height="2">
52.            </td>
53.        </tr>
54.    </table>
55.<?php
56.}