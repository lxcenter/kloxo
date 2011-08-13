<?php
	$ha = str_replace('cp.', '', $_SERVER["HTTP_HOST"]);
?>
<html>

<head>
<title>Kloxo Control Panel</title>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
</head>

<style>
body {
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 1em;
	background: #ddeeff;
	margin: 0;
}
a {
	text-decoration: none;
}
img {
	border: 0;
}
</style>
<body>

<table cellpadding="0" cellspacing="0" width="100%" border="0" bgcolor="#66aaddd" height="100">
	<tr>
		<td valign="top"><img src="images/logo.png" vspace="5" hspace="5"></td>
		<td width="130">
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td>
				<img src="images/lxcenter.png" width="120" height="35" hspace="5" vspace="5"></td>
			</tr>
			<tr>
				<td>
				<img src="images/kloxo.png" width="120" height="27" hspace="5" vspace="5"></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" bgcolor="#000000">&nbsp;</td>
	</tr>
</table>
<table width="100%" height="300" cellpadding="0" cellspacing="0">
	<tr>
		<td width="50">&nbsp;</td>
		<td valign="top"><br>
		<br>
		<div align="center"><?php
print(" <h2> Choose Your Webmail Program </h2> <br> ");
print("<a href=horde> <img src=horde/themes/graphics/horde-power1.png></a> <br> <br>   ");
print("<a href=roundcube> <img src=roundcube/skins/default/images/roundcube_logo.png></a> ");
?> </div>
		</td>
		<td width="280" valign="center"><img src="images/disableskeletonbg.gif"></td>
	</tr>
</table>

</body>

</html>
