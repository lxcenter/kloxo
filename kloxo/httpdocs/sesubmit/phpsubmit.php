<?php

// include all our functions
require "functions.php";
require "engines.php";

if(@ereg($getext,$getext) == false)
	$getext = "(:)";

if(@ereg($ignoredir,$ignoredir) == false)
	$ignoredir = "(:)";

$domain = $_REQUEST['domain'];
$offset = $_REQUEST['offset'];
$max_results = $_REQUEST['max_results'];
$getext = $_REQUEST['getext'];
$ignoredir = $_REQUEST['ignoredir'];

// check to see whether we know the domain or not
if(!isset($domain)){
	Header("Location: index.html");
	exit();
}

if(!isset($offset))
	$offset = 0;


$domain = str_replace("http://","",$domain);

$index = get_index($domain);

get_links($index,$domain,$links);

if(sizeof($links) <= $offset || !sizeof($links)){
	setcookie("phpsubmit_remembering",false,time()+604800);
	Header("Location: noresults.html");
	exit();
}

?>

<!--
    PHPSubmit - A search engine submission script
    Copyright (C) 2000 Matt Wilson

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
-->

<html>

<head>

<title>PHPSubmit is ready to start the submission process</title>

<script style=javascript>
//<!--
function checkemail(form)
{
	if(form.email.value.search("^(.+)((@){1})(.+)$") == -1){
		alert("Please enter a valid e-mail address");
		return false;
	}
	return true;
}
//-->
</script>

</head>

<body bgcolor=#ffffff text=#000000>

<div align=center>

<table width=500 cellspacing=2>

<tr><td width=500 align=center><a href="http://www.mattsscripts.co.uk/phpsubmit/"><img border=0 src=logo2.gif></a></td></tr>

<tr><td></td></tr>

<tr><td width=500>
<p><font face=Verdana size=2>A list of all the links that reside on <b><?php echo $domain; ?></b> was created and <?php echo $max_results; ?> were picked from this list, if you have just created this search then these are the first <?php echo $max_results; ?> found, otherwise they are the next <?php echo $max_results;?> on the list. The pages that are going to be submitted are listed below, in order for these to be submitted I need you e-mail address and optionally some keywords depending on which search engines you wish to submit to, please enter these details in the text boxes below and then click "OK"</p>
<p>If there are any of the following pages that you do not wish to be submitted to the search engines then please uncheck the boxes next to them</p>

<table border=0>
<form action=submit.php method=post name=form onSubmit="return checkemail(this)">

<?php
	for($t=0; $t<sizeof($links)-$offset; $t++){
		$pages[] = $links[$t+$offset];

		$p = sizeof($pages)-1;

		echo "<input type=hidden name=k[".$t."] value='".$pages[$p]."'>";
		$page_list .= "<input type=checkbox name=uk[$p] value=1 checked> - ".$pages[$p]."<br>\n";
	}

?>

<p>The following pages are going to be submitted:-</p>
<p>
<?php echo $page_list; ?>
</p>

<p>
Please check the box next to the search engine(s) you want to submit these pages to:
</p>
<p>
<table width=100%>
<?php

for($t=0; $t<sizeof($engines); $t+=2){
	echo "<tr><td width=50%><font face=Verdana size=2>";
	echo "<input type=checkbox name=ue[$t] value=1 checked> - ".$enginename[$t];

	if(strpos($engines[$t], "[>KEYS<]")){
		echo "&nbsp;<img src='keyword.gif'>";
	}

	echo "</font></td><td width=50%><font face=Verdana size=2>";

	if($engines[$t+1]){
		echo "<input type=checkbox name=ue[".($t+1)."] value=1 checked> - ".$enginename[$t+1];

		if(strpos($engines[$t+1], "[>KEYS<]")){
			echo "&nbsp;<img src='keyword.gif'>";
		}
	} else {
		echo "&nbsp;";
	}

	echo "</font></td></tr>\n";
}
?>
</table>
</p>

<p>
<div align=center>
<table border=0>
<tr><td align=center><font face=Verdana size=2>Enter your email address</td><td><font face=Verdana size=2><input type=text name=email size=35></td></tr>
<tr><td align=center colspan=2><font face=Verdana size=2>Some search engines require "keywords" in order to list your web pages accurately, the search engines which support this are listed above with a <img src="keyword.gif"> next to it. For this reason, please enter some keywords about your site below, if you need more help regarding this subject, please refer to the <a href="help.html#keywords">help page</a>.</font></td></tr>
<tr><td align=center><font face=Verdana size=2>Keywords</td><td><font face=Verdana size=2><input type=text name=keys size=35></td></tr>
<input type=hidden name=domain value="<?php echo $domain; ?>">
<tr><td colspan=2 align=center><input type=image src=ok.gif border=0></td></tr>
</table>
</div>
</p>

</td></tr></table>

</form>

</div>
</body>

</html>
