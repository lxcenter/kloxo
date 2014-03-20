<?php 

function createNavigationTabData()
{
	global $gbl, $sgbl, $login, $ghtml, $gdata;

	$homedesc = $login->getKeywordUc('home');
	$deskdesc = $login->getKeywordUc('desktop');
	$aboutdesc = $login->getKeywordUc('about');

	$domaindesc = get_plural(get_description('domain'));
	$clientdesc = get_plural(get_description('client'));
	$slavedesc = get_description('pserver');
	$ticketdesc = get_plural(get_description('ticket'));
	$ssessiondesc = get_description('ssession');
	$systemdesc = $login->getKeywordUc('system');
	$logoutdesc = $login->getKeywordUc('logout');
	$helpdesc = $login->getKeywordUc('help');
	$ffiledesc = get_plural(get_description("ffile"));
	$alldesc = $login->getKeywordUc('all');

	if ($login->isAdmin()) {
		$doctype = "admin";
		$domainclass = "domain";
	} else  {
		$doctype = "client";
		$domainclass = "domain";
	}

	if (check_if_many_server()) {
		$serverurl = $ghtml->getFullUrl('a=list&c=pserver');
		$slavedesc = get_plural($slavedesc);
	} else {
		$serverurl = $ghtml->getFullUrl('k[class]=pserver&k[nname]=localhost&a=show');
	}

	if ($login->is__table('client')) {
		$ffileurl = $ghtml->getFullUrl('k[class]=ffile&k[nname]=/&a=show');
	} else {
		$ffileurl = $ghtml->getFullUrl('n=web&k[class]=ffile&k[nname]=/&a=show');
	}

	$gob = $login->getObject('general')->generalmisc_b;

	if (isset($gob->ticket_url) && $gob->ticket_url) {
		$url = $gob->ticket_url;
		$url = add_http_if_not_exist($url);
		$ticket_url = "javascript:window.open('$url')";
	} else {
		$ticket_url = "/display.php?frm_action=list&frm_o_cname=ticket";
	}
	$helpurl = "http://wiki.lxcenter.org";

	$gdata = array(
		"desktop" => array($deskdesc, "/display.php?frm_action=desktop", "client_list.gif"),
		"home" => array($homedesc, "/display.php?frm_action=show", "client_list.gif"),
		"all" => array($alldesc, "/display.php?frm_action=list&frm_o_cname=all_domain", "client_list.gif"),
		"domain" => array($domaindesc, "/display.php?frm_action=list&frm_o_cname=$domainclass", "domain_list.gif"),
		"system" => array($systemdesc, "/display.php?frm_action=show&frm_o_o[0][class]=pserver&frm_o_o[0][nname]=localhost", "pserver_list.gif"),
		"client" => array($clientdesc, "/display.php?frm_action=list&frm_o_cname=client", "client_list.gif"),
		"ffile" => array($ffiledesc, $ffileurl, "client_list.gif"),
		"pserver" => array($slavedesc, $serverurl, "pserver_list.gif"),
		"ticket" => array($ticketdesc, $ticket_url, "ticket_list.gif"),
		"ssession" => array($ssessiondesc, "/display.php?frm_action=list&frm_o_cname=ssessionlist", "ssession_list.gif"),
		"about" => array($aboutdesc, "/display.php?frm_action=about", "ssession_list.gif"),
		"help" => array($helpdesc, "javascript:window.open('$helpurl/')", "ssession_list.gif"),
		"logout" => array("<font color=red>$logoutdesc<font >", "javascript:top.mainframe.logOut();", "delete.gif")
	);
}

function print_a_right_button($something, $ttype, $id, $pos)
{
	global $gbl, $login, $ghtml, $gdata; 

	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];

	if (csa($url, "javascript")) {
		$onclickstring = "onClick=\"$url\"";
	} else {
		$onclickstring = "onClick=\"top.mainframe.location='$url';\"";
	}
	$skindir = $login->getSkinDir();
?>
<td>
<table class="headertabletabsright" style="background: url(<?php echo $skindir ?>right_btn.gif);" OnMouseOver="style.cursor='pointer';" <?php echo $onclickstring ?>>
    <tr>
        <td valign="bottom" width="17" height="34" align="left" style="padding-bottom: 5px; padding-left: 6px;">
            <img height="8" width="8" src="/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name ?>/button/<?php echo $icon ?>">
        </td>
        <td valign="bottom" width="53" style="padding-left: 3px; padding-bottom: 3px;" align="left"><b><?php echo  $name ?></b>
        </td>
    </tr>
</table>
</td>
<?php
}

function print_a_button($side, $ttype, $id, $pos, $menupos = 0)
{
	global $gbl, $login, $ghtml, $gdata; 
	$name = $gdata[$id][0];
	$url = $gdata[$id][1];
	$icon = $gdata[$id][2];

	if ($side === 'right') {
		$imgprop = 'height="8" width="8"';
		$menu = "rightmenu";
		$bgimg = "right_btn.gif";
		$imgtdprop = 'width="17"';
		$tdstyle = 'style="padding-top: 10px;"';
		$arg = "0, $menupos";
	} else {
		$bgimg = "left_btn.gif";
		$imgprop = 'height="15" width="13"';
		$menu = "showMenuInFrame";
		$imgtdprop = 'width="25"';
		$tdstyle = 'style="padding-top: 1px;"';
		$arg = "$menupos, 0";
	}
?>
<td>
<table class="headertabletabsleft" style="background: url(<?php echo $login->getSkinDir() ?><?php echo $bgimg ?>)" OnMouseOver="style.cursor='pointer';" onClick="top.mainframe.location='<?php echo $url ?>';">
    <tr>
        <td <?php echo $imgtdprop ?> align="center" <?php echo $tdstyle ?>>
            <img <?php echo $imgprop ?>  src="/img/image/<?php echo $login->getSpecialObject('sp_specialplay')->icon_name ?>/button/<?php echo $icon ?>">
        </td>
        <td <?php $tdstyle ?> valign="middle" align="center"><b><?php echo $name ?></b>
        </td>
    </tr>
</table>
</td>
<?php
}

function print_header_default()
{
	global $gbl, $login, $ghtml;

	$ttype = $login->cttype;

    $ghtml->print_include_jscript("header");
	$skin = $login->getSkinDir();
	$logo = "/img/kloxo-logo.gif";
	$logo_loading = "/img/kloxo-splash.gif";
?>
<script>

function changeLogo(flag)
{
	imgob = document.getElementById('main_logo');
	if (!imgob) {
		return;
	}
	if (flag) {
		imgob.src = '<?php echo $logo_loading ?>';
	} else {
		imgob.src = '<?php echo $logo ?>';
	}
}
</script>
<?php
print_close_head_tag();
createNavigationTabData();
?>
<!-- Start Body -->
<body>
<!-- Start Header Table -->
<table width="100%" height="58" valign="top" align="center">
<tr>
    <td width="100%" style="background: url(<?php echo $login->getSkinDir() ?>/header_top_bg.gif);"></td>
    <td width="326" style="background: url(<?php echo $login->getSkinDir() ?>/header_top_rt.gif); background-repeat: no-repeat;">
        <table width="326">
            <tr align="right">
                <td width="200">&nbsp;</td>
                <td align="right">
                    <img id="main_logo" width="84" height="23" src="<?php echo $logo_loading?>">
                </td>
                <td width="10%">&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>
</table>
<!-- End Header Table -->
 <!-- Start navigation -->
  <!-- Main Table -->
<table width="100%" background="<?php echo $login->getSkinDir() ?>/header_panel_bg.gif">
<tr><!-- Main  Row -->
<?php
    print("<!-- Cell Left -->\n");

	$count = 1;
	$count += 83;
    print("<!-- Load button Home (Left) -->\n");

	print_a_button("left", $ttype, "home", $count, 1);
	$count += 83;

    print("<!-- Load other buttons (Left) -->\n");
    print_left_side($ttype, $count);

    print("<!-- Cell Middle -->\n");
    print("<td width=\"100%\">&nbsp;</td>\n");

    print("<!-- Cell Right -->\n");
    if (!$login->is__table('mailaccount')) {

		if (!$login->is__table('ticket')) {
            print("<!-- Load button Ticket (Right) -->\n");
            print_a_right_button("right", $ttype, "ticket", 294);
		}

        print("<!-- Load button Session (Right) -->\n");
        print_a_right_button("right", $ttype, "ssession", 150);
        print("<!-- Load button Help (Right) -->\n");
        print_a_right_button("right", $ttype, "help", 150);

	}

    print("<!-- Load button LogOut (Right) -->\n");
    print_a_right_button("right", $ttype, "logout", 190);

	print("</tr><!-- End Main Row -->\n</table><!-- End Main Table -->\n");
}

function print_left_side($ttype, $count)
{
	global $gbl, $login, $ghtml; 

	if($login->isLte('reseller')) {
        print("<!-- Load button Client -->\n");
        print_a_button("left", $ttype, "client", $count);
	}

	if($login->isLte('reseller')) {
        print("<!-- Load button All -->\n");
        print_a_button("left", $ttype, "all", $count);
	} 

	if($login->isAdmin()) {
        print("<!-- Load button Server -->\n");
        print_a_button("left", $ttype, "pserver", $count);
	} 

	if ($login->isLte('customer') && $login->priv->isOn('webhosting_flag')) {
        print("<!-- Load button FileManager -->\n");
        print_a_button("left", $ttype, "ffile",$count);
	}
}
