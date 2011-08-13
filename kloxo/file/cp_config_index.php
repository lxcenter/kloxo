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
					</table></td>
	</tr><tr><td colspan="2" bgcolor="#000000">&nbsp;</td></tr>
</table>
<table width="100%" height="300" cellpadding="0" cellspacing="0">
	<tr>
		<td width="50">&nbsp;</td>
		<td valign="top"><br><br>
		<div align="center">
			<table width="400" style="border-collapse: collapse" border="1" bordercolor="#AAAAAA">
				<tr>
					<td nowrap bgcolor="#CCCCCC">&nbsp;</td>
					<td colspan="2" nowrap align="center" bgcolor="#EEEEEE">Panel</td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee">&nbsp; Kloxo</td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="http://<?php echo $ha; ?>:7778/">http</a></td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="https://<?php echo $ha; ?>:7777/">https</a></td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee">&nbsp; Webmail</td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="http://webmail.<?php echo $ha; ?>/">http</a></td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="https://webmail.<?php echo $ha; ?>/">https</a></td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee" width="200">&nbsp; PHPMyAdmin</td>
					<td nowrap align="center" width="100" bgcolor="#FFFFCC">
					<a href="http://<?php echo $ha; ?>:7778/thirdparty/phpMyAdmin/">
					http</a></td>
					<td nowrap align="center" width="100" bgcolor="#FFFFCC">
					<a href="https://<?php echo $ha; ?>:7777/thirdparty/phpMyAdmin/">
					https</a></td>
				</tr>
			</table>
		</div>
		</td>
		<td width="280" valign="center">
		<img src="images/disableskeletonbg.gif"></td>
	</tr>
</table>

</body>

</html>
