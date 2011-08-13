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
}
a {
	text-decoration: none;
}
img {
	border: 0;
}
</style>
<body topmargin="0" leftmargin="0" leftmargin="0">

<table cellpadding="0" cellspacing="0" width="100%" border="0" background="/images/dheadbg.gif" height="96">
	<tr>
		<td>&nbsp;</td>
		<td width="176"><a href="http://lxcenter.org/" title="Go to LxCenter website"><img src="/images/kloxo.gif"></a></td>
	</tr>
</table>
<br>
<br>
<table width="100%" height="300" cellpadding="0" cellspacing="0">
	<tr>
		<td width="50">&nbsp;</td>
		<td valign="top">
		<div align="center">
			<table width="400" style="border-collapse: collapse" border="1" bordercolor="#C0C0C0">
				<tr>
					<td nowrap>&nbsp;</td>
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
		<img src="/images/disableskeletonbg.gif"></td>
	</tr>
</table>

</body>

</html>
