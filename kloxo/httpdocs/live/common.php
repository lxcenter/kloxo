<?php

/********************************************************************************
 * Server variables: MySQL, IRC
*********************************************************************************/


if (file_exists("conf.real.php")) {
	include("conf.real.php");
} else {
	include("conf.php");
}

include_once "../htmllib/lib/displayinclude.php";


if (!$redirect) {
	$chanbase = isset($_REQUEST['channel'])? $_REQUEST['channel']: "";
}

if ($g_login) {
	$chanbase = $sgbl->__var_program_name;
}

$channel = $chanbase; 
$g_db_file = "$sgbl->__path_program_etc/phplive.db";

$serv_addr = "support.lxlabs.com"; 		// the irc server dns or ip
$serv_name = $serv_addr;

$serv_port = 4507;		// the irc server port, normally 6667.

//$channels[] = "main";		// or uncomment the rest to let
//$channels[] = "jongeren";		// the user choose the channel

$hide_motd = false;		// false = show the Message of the Day, true = don't show motd...
				        // the motd might be too long, not usefull or sth else for u... so set it to true then...
$chat_newscr = true;		// opens a new screen by default when pressing the chat button

/********************************************************************************
 * Layout variables: Colors, fonts, CSS
*********************************************************************************/

$fontsize = 10;		// the font size of all the pages
$fontfamily = "'helvetica', 'lucida', 'arial'";	// the font family(s) of all the pages. *! Within the dubble quotes, supply single quotes around each family !*

$page_bg  = "#ffffff"; 		// page background color
$chan_bg  = "#ffffff"; 		// channel frame background color
$chan_fg  = "#000000"; 		// channel frame foreground color
$input_bg = "#bbbbbb"; 		// background color of the input text, the buttons, and the color chooser
$table_border = "#AACCAA"; 	// border color of the table
$page_fg  = "#000000"; 		// page foreground/text color

 $ircColors[0] = "#FFFFFF"; // white
 $ircColors[1] = "#000000"; // black
 $ircColors[2] = "#000080"; // dark blue
 $ircColors[3] = "#008000"; // dark green -> standard for join/part/quit
 $ircColors[4] = "#FF0000"; // red	 -> standard for error/disconnect
 $ircColors[5] = "#800000"; // dark red	 -> standard for notices
 $ircColors[6] = "#FF00FF"; // purple	 -> standard for (ctcp) Actions
 $ircColors[7] = "#FF8000"; // orange	 -> standard for server messages (like topics, names, modes)
 $ircColors[8] = "#FFFF00"; // yellow
 $ircColors[9] = "#00FF00"; // green
$ircColors[10] = "#008080"; // dark cyan
$ircColors[11] = "#00FFFF"; // cyan
$ircColors[12] = "#0000FF"; // blue
$ircColors[13] = "#800080"; // dark purple
$ircColors[14] = "#808080"; // dark grey
$ircColors[15] = "#C0C0C0"; // light grey


/* The css part that gets included on every page, add ur own wishes here ;) */
$css = <<<EOF
<style type="text/css">

body,tr,td,iframe {
    font-family: $fontfamily;
    font-size: {$fontsize}pt;
}
</style>

EOF;

/********************************************************************************
 * That's all, have fun PHP chatting ;)
*********************************************************************************/

