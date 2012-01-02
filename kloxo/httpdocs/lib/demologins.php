<?php 

include_once "htmllib/lib/include.php";

$ghtml = new HtmlLib();

//$res["Super Admin"] = array('superadmin', 'superclient');
$res["Admin"] = array('admin', 'client');
//$res['Wholesale Reseller'] = array('wholesale', 'client');
$res['Reseller'] = array('reseller', 'client');
$res['Customer/Domain Owner'] = array('customer', 'client');
//$res['Simple Skin'] = array('sillyskin', 'client');
$res['Mail Account'] = array('postmaster@example.com', 'mailaccount');

$color = "style='border:1px solid black'";
print("<table cellspacing=0 cellpadding=0> ");

foreach($res as $k => $v) {

	$formname = $v[0] . "_" . $v[1];
	$class = $v[1];
	$name = $v[0];

	$color = null;
	if ($class == 'superadmin') {
		$color = "style='border-bottom:1px solid black'";
	}
	$formname = str_replace(array('@', '.'), "", $formname);
	print("<tr > <td $color>");
	print("<form name=$formname method=$sgbl->method action='/htmllib/phplib/'>") ;

	print("<input type=hidden name=frm_clientname value={$v[0]}>");
	print("<input type=hidden name=frm_class value={$v[1]}>");
	print("<input type=hidden name=frm_password value=lxlabs>");
	print("</form>");

	
	if ($class == 'client') {
		$var = "cttype_v_$name";
	} else {
		$var = 'show';
	}

	$image = $ghtml->get_image("/img/image/collage/button/", $class, $var, ".gif");

	print(" <img width=20 height=20 src=$image> </td> <td $color ><a href=javascript:document.$formname.submit()> Click here to Login as $k ($v[0])</a>");
	print("</td></tr>");
}

	print(" <tr> <td ><img width=20 height=20 src=/img/general/button/on.gif> </td> <td ><a href=http://forum.lxcenter.org/ target='_blank'> Visit our forums.</a> </td></tr>");
	print(" <tr> <td ><img width=20 height=20 src=/img/general/button/on.gif> </td> <td ><a href=http://www.lxcenter.org/ target='_blank'> LxCenter</a> </td></tr>");
print("</table>");

