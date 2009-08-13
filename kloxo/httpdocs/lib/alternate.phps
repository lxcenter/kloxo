<?php 

$ar = array("client", "domain", "mailaccount", "uuser", "ftpuser");

 foreach($ar as $a) {
	print("<tr > <td");
	$formname = "f_$a";
	print("<form name=$formname method=get action='/'>\n") ;
	print("<input type=hidden name=frm_class value={$a}>\n");
	print("</form>");
 }
foreach($ar as $a) {
	print(" <tr> <td ><a href=javascript:document.$formname.submit()> Click here to Login as $a</a> <br> </td> </tr>");
}

