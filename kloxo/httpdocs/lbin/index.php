<?php 

chdir("..");
include_once "htmllib/lib/displayinclude.php";

main_main();

function domainshow()
{   
	global $gbl, $sgbl, $login, $ghtml; 

	if ($login->isAdmin()) {
		$doctype = "admin";
		$domainclass = "all_domaina";
	} else  {
		$doctype = "client";
		$domainclass = "domaina";
	}


	$url = $login->getUrlFromLoginTo();
	$url = $ghtml->getFullUrl($url);



	if ($login->isAdmin()) {
		//$url = '/display.php?frm_action=list&frm_o_cname=client';
	}

	if ($login->getSpecialObject('sp_specialplay')->isOn('lpanel_scrollbar')) {
		$lpscroll = 'auto';
	} else {
		$lpscroll = 'no';
	}

	if ($gbl->isOn('show_help')) {
		$scrollstring = 'scrolling=no';
		$width = $sgbl->__var_lpanelwidth;
	} else {
		$scrollstring = "scrolling=$lpscroll";
		$width = $sgbl->__var_lpanelwidth;
	}

    $title = get_title();
	?> 
	<head>
	<title> <?php echo $title ?> </title>
		
	<?php $ghtml->print_refresh_key(); ?> 

	<FRAMESET frameborder=0 rows="93,*"  border=0>

	<FRAME name=topframe src="/mibin/header.php" scrolling=no>
		<FRAMESET frameborder=0 cols="<?php echo $width?>,*" border=0>
		<FRAME name=leftframe src='/htmllib/mibin/lpanel.php' <?php echo $scrollstring ?>  border=0>
	<FRAME name=mainframe src="<?php echo $url ?>">
	</FRAMESET>
	</FRAMESET>
	</head>
	<?php
	//<FRAME name=bottomframe src="/bin/bottom.php">
}

function generalshow()
{  
	global $gbl, $login, $ghtml; 

    $title = get_title();

	$gbl->setSessionV("redirect_to", "/display.php?frm_action=show");

	?>
	<head>
	<title> <?php echo $title ?> </title>
	<FRAMESET frameborder=0 rows="98,*" border=0>
	<FRAME name=top src="/header.php" scrolling=no border=0> 
	<FRAME name=mainframe src="/display.php?frm_action=update&frm_subaction=general&frm_ev_list=frm_emailid&frm_emessage=set_emailid">
	</FRAMESET>
	</head>
	<?php 
}

function main_main()
{
	global $gbl, $login, $ghtml; 

   	initProgram();

	domainshow();

}


