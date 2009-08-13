<?php 

chdir("../..");
include_once "htmllib/lib/include.php";
include_once "htmllib/lib/helplib.php";



help_main(); 




function __ac_desc_faq()
{
	$file = "/help/help/faq.dart" ;
	show_help_file($file);
}


function __ac_desc_glossary()
{

	$file = "/help/help/glossary.dart" ;
	show_help_file($file);
}

function __ac_desc_icon_glossary()
{

	$file = "/help/help/icon_glossary.dart" ;
	show_help_file($file);
}

function __ac_desc_lxlabs_forum()
{

}

function __ac_desc_show()
{
	global $gbl, $login, $ghtml; 

	$cgi_help = $ghtml->frm_q;
	$help = base64_decode($cgi_help);
	$help = "display.php?" . $help;
	$ghtml->get_post_from_get($help, $path, $post);
	$help_file = "/help/default/{$post['class']}_{$post['var']}.dart";

	if (!file_exists(getreal($help_file))) {
		dprint("Do not exist: $help_file <br> ");
		$help_file = "/help/default/{$post['var']}.dart";
	}

	if (!show_help_file($help_file))
		__ac_desc_tutorial();
		
}


function show_help_file($hhelp_file)
{

	global $gbl, $login, $ghtml; 

	$help_file = getreal($hhelp_file);

	if (!lfile_exists($help_file)) {
		dprint("Debug Message: File <h3> <font color=red> $hhelp_file  Doesn't Exist </font> </h3> . Showing default</h1> ");
		lfile_put_contents("missing.txt", $hhelp_file . "\n", FILE_APPEND);
		return 0;
	}

	dprint("  Debug Message: Showing <h3>  $hhelp_file  </h3> ");
	$fp = lfopen($help_file, "r");

	if (!$fp) {
		print("cannot open $help_file <br> ");
		return  0;
	}

	$last = "";
	$inblock = "out";
	while(!feof($fp)) {
		
		$buf = fgets($fp, 1024);
		$buf = trim($buf);
		if (preg_match("/<reseller>/", $buf)) {
			$inblock = "reseller";
			continue;
		}

		if (preg_match("/<notlogin>/", $buf)) {
			$inblock = "notlogin";
			continue;
		}
		if (preg_match("/<admin>/", $buf)) {
			$inblock = "admin";
			continue;
		}
		if (preg_match("/<lximg:\s*([^>]*)>/", $buf, $matches)) {

			$img = $matches[1];
			$buf = preg_replace("/<lximg:\s*([^>]*)>/", "<img src=/img/image/collage/$1>", $buf);
			print($buf . "\n<br>");
			continue;
		}

		$buf = preg_replace("/<\/link>/", "</a>", $buf);
		if (preg_match("/.*<link:\s*([^>]*)>.*/", $buf, $matches)) {
			$url = $matches[1];
			$url = str_replace(".dart", "", $url);
			$buf = preg_replace("/<link:\s*([^>]*)>/", "<a href=/htmllib/mibin/help.php?frm_action=show&frm_q=$url>", $buf);
			print($buf . "<br> ");
			continue;
		}

		if (preg_match("/<\/reseller>/", $buf) || preg_match("/<\/admin>/", $buf)) {
			$inblock = "out";
			continue;
		}

		if (preg_match("/<\/notlogin>/", $buf)) {
			$inblock = "out";
			continue;
		}


		if ($login->isGte('reseller') && $inblock === "admin") {
				continue;
		}

		if ($login->isLogin() && $inblock === 'notlogin') {
			continue;
		}

		if ($login->isGte('customer') && ($inblock === "reseller" || $inblock === "admin")) {
			continue;
		}



		$buf = preg_replace("/href=(\S*).dart/i", "href=/htmllib/mibin/help.php?frm_q=/\$1", $buf);

		if (preg_match("/:\[.*\]:/", $buf)) {
			$buttonpath = get_image_path() . "/button/";
			$value = preg_replace("/.*:\[(.*)\]:.*/", "$1", $buf);
			$rest = preg_replace("/.*:\[.*\]:/", "", $buf);
			$ghtml->get_post_from_get(trim($value), $path, $post);
			$descr = $ghtml->get_action_descr($path, $post, $class, $name);
			$image = $ghtml->get_image($buttonpath, $class, $name, ".gif");
			print("<img src=$image> <b> $descr[1]: </b> $rest <br> <br>  ");
			continue;
		}


		if ($buf === '') {
			if ($last === "blank") {
				continue;
			}
			$last = "blank";
			$buf =  "</p> <p> ";
		} else  {
			if ($last === "blank") {
				$last = "";
			}
			$buf = $buf . "<br> \n";
		}
		print($buf);
	}
  return 1;
}

function __ac_desc_tutorial()
{
	$file = "/help/tutorial/index.dart" ;
	show_help_file($file);

}

function print_alternate_header()
{
}


function print_help_header()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);
	?> 
	<head> 
	<title> <?php echo $cprogname ?>  Help </title>
	</head>

	<?php 
	$ghtml->print_css_source("/htmllib/css/common.css");
	?> 
		<body topmargin="0" leftmargin="0">
<table width=100%  border="0" valign="top" align="center" cellpadding="0" cellspacing="0">
<tr><td width="100%" colspan=5 background="/img/header/header_05.gif" width="10" height="34"></td></tr>
</table>
<?php
}



function help_main()
{

	global $gbl, $login, $ghtml; 

	initProgram();
	print_help_header();

	$gbl->__c_object = null;

	$ghtml->print_middle_start();

	print("<table height=100% valign=top cellspacing=0 cellpadding=0> <tr valign=top> <td width=200 height=100% bgcolor=#acacff align=center>");

	print("<table> <tr height=10> <td > </td></tr>");
	print("<tr> <td ><a href=/htmllib/mibin/help.php?frm_action=tutorial> Tutorial </a> </td></tr>");
	print(" <tr> <td ><a href=/htmllib/mibin/help.php?frm_action=faq> FAQ </a> </td></tr>");
	print("</td></tr></table>");

	print(" </td> <td >&nbsp;  </td> <td > ");
	


	//print_alternate_header();

	__ac_desc_show();

print("</td> </tr> </table> ");
$ghtml->print_end();

}



