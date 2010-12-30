<?php 

function print_tab_for_feather($alist)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$img_path = $login->getSkinDir();
	$imgtop = $img_path . "/top_line.gif";

	foreach($alist as $k => $a) {
		//$alist[$k] = $ghtml->getFullUrl($a);
		//This will disable ajax, since the ajax is sent via __v_dialog in the key.
		$nalist[] = $ghtml->getFullUrl($a);
	}
	$alist = $nalist;


	if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax')) {
		$ghtml->print_dialog($alist, $gbl->__c_object);
	}

	?> <link href="/img/skin/kloxo/feather/default/feather.css" rel="stylesheet" type="text/css" /> <?php 


	?> 

	<br> 
<table width=100% cellpadding="0" cellspacing="0" border="0" style="vertical-align:top;  "><tr><td  colspan="2" >
<table  cellpadding="0"  cellspacing="0" border="0"><tr>
<?php 


if (!$sgbl->isBlackBackground()) {
	print("<td width=20 class=tabcomplete nowrap> <div class=tabcompletediv> &nbsp; &nbsp;  </div> </td>");
}

	// This gives a list of key value pair, which shows which of the tab is selected. For instance, if the fifth tab is the selected on, then $list[5] will be true, while all the others will be false. This is necessary because, printing will need to know if the next tab is the selected one.

	$list = $ghtml->whichTabSelect($alist);
	$list[-1] = false;
	$list[count($list) - 1] = false;
	//dprintr($list);
	foreach($alist as $k => $a) {
		print_tab_button_for_feather($k, $a, $list);
	}

	/*
<td class="ver"><div><img src="/img/skin/kloxo/feather/default/images/menulft21.jpg" border="0" /></div></td><td class="new2"><div class="verb">Home</div></td> <td class="ver"><img src="/img/skin/kloxo/feather/default/images/menulft20.jpg" border="0" /></td>
<td class="new"><div class="verb3">Domains</div></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menurit20.jpg" border="0" /></td>
<td class="new1"><div class="verb2" >Sub Domains</div></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menurit21.jpg" border="0" /></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menulft21.jpg" border="0" /></td><td class="new1"><div class="verb" >Mail Accounts</div></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menurit21.jpg" border="0" /></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menulft21.jpg" border="0" /></td><td class="new1"><div class="verb" >Appearance</div></td> 
<td class="ver"><img src="/img/skin/kloxo/feather/default/images/menurit21.jpg" border="0" /></td>
<td class="ver"><div style="margin-bottom:0px"><img src="/img/skin/kloxo/feather/default/images/menulft21.jpg" border="0" /></div></td><td class="new1"><div class="verb" >Advanced</div></td><td class="ver"><img src="/img/skin/kloxo/feather/default/images/menurit21.jpg" border="0" /></td>
*/


	if (!$sgbl->isBlackBackground()) {
		print("<td width=100%  class=tabcomplete> <div class=tabcompletediv> &nbsp; </div> </td>");
	}
?> 


</tr>
</table> 
<br> 

</td> </tr> 

</table>
</div>
<?php 

}


function print_tab_button_for_feather($key, $url, $list)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$cobject = $gbl->__c_object;
	static $after_sel = false;

 	$psuedourl = NULL;
	$target = NULL;
	$img_path = $login->getSkinDir();
	$imgtop = $img_path . "/top_line.gif";

	$buttonpath = get_image_path() . "/button/";
	$bpath = $login->getSkinDir();
	$bdpath=$login->getSkinColor();
	$button = $bpath . "/top_line_medium.gif";

	$ghtml->resolve_int_ext($url, $psuedourl, $target);

	$descr = $ghtml->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);

	$targetstring = $target;

	//$ghtml->save_non_existant_image($image);

	$form_name = $ghtml->createEncForm_name($file . "_" . $name);
	//$bgcolorstring = null;
	//$sel = null;
	//$borderbottom = "style='border-bottom:1px solid black;'";

	$borderbottom = "style =\"border-bottom:2px solid #$bdpath;\"";
	$borderbot = "style =\"background:url($bpath/tab_select_bg2.gif) 0 0 repeat-x;\"";
	$check = $ghtml->compare_urls("display.php?{$ghtml->get_get_from_current_post(null)}", $url);
	if ($check) {
		//$bgcolorstring = "style=\"background:url('$button');\"";
		$bgcolorstring = "bgcolor=#99aaff";
		$sel = "_select";
		$borderbottom = $borderbot;
	} else { 
		$sel = "_select";
		$bgcolorstring = "bgcolor=#99aaff";
		//$image = null;
	}

	$imageheight = 24;
	$height = 34;
	$imgp = $login->getSkinDir();
	$imglt = $imgp . "/tab{$sel}_lt.gif";
	$imgbg = $imgp . "/tab{$sel}_bg.gif";
	$imgrt = $imgp . "/tab{$sel}_rt.gif";

	$linkflag = true;
	if (csa($key, "__var_")) {
		$privar = strfrom($key, "__var_");
		if (!$cobject->checkButton($privar)) {
			$linkflag = false;
		}
	}

	$idstring = null;
	if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax') && csb($key, "__v_dialog")) {
		$idstring = "id=$key-comment";
	}

	$help = $descr['help'];
	$descstring = "<span title='$help'> &nbsp; &nbsp; $descr[2] &nbsp; &nbsp;</span>";

	if ($sgbl->isBlackBackground()) {
		if ($check) {
			$stylestring = "style='font-weight:bold'";
		} else {
			$stylestring = "style='font-weight:normal'";
		}
		$fcolor = "#999999";
		print("<a href=\"$url\" $targetstring><font $stylestring color=$fcolor>$descstring</font> </a>");
		return;
	}
		

	$lastkey = count($list);
	if ($check) {
		if ($key === 0) {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menufirstlft20.jpg' border='0' /></td>");
		} else {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menulft20.jpg' border='0' /></td>");
		}
		print("<td class='tabnew'><div class='verb3'><a href=\"$url\" $targetstring>$descstring</a> </div></td>");

		//dprint("hello $lastkey $key hello");
		//dprintr($list);
		if ($key === $lastkey - 3 ) {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menulastrit20.jpg' border='0' /></td>");
		} else {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menurit20.jpg' border='0' /></td>");
		}
	} else {
		if (!$list[$key - 1]) {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menulft21.jpg' border='0' /></td>");
		}
		
		print("<td class='tabnew1' ><div nowrap class='verb'><a href=\"$url\" $targetstring>$descstring</a></div></td>");
		if ($key === $lastkey - 3 ) {
			print("<td class='tabver'><img src='/img/skin/kloxo/feather/default/images/menulft21.jpg' border='0' /></td>");
		}
	}


	return $check;
}
