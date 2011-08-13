<?php
	$ha = str_replace('cp.', '', $_SERVER["HTTP_HOST"]);
?>
<html>

<head>
<title>Kloxo Default page</title>
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
		<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border: 1px dashed #cccccc" bordercolor="#111111" width="600">
			<tr>
				<td width="100%" bgcolor="#F5F5F5">
				<font size="2" face="arial" color="#444444">If you are seeing this 
				page, it means that web has not been configured for this domain 
				on this server. <br>
				<br>
				This could be due to the following causes: <br>
				<br>
				<li>Kloxo has not restarted the web server yet after you added the 
				domain. Please wait for the web server to restart. <br>
				<br>
				</li>
				<li>The domain is pointing to the wrong Kloxo server. Ping the domain 
				and make sure that the IP matches one of the IPaddress seen in
				<b>admin home -&gt; ipaddresses </b><br>
				<br>
				</li>
				<li>If you are seeing this page when you try to access an IP like 
				http://192.168.1.1, then that means that the IP has not yet been 
				mapped to a domain. Go to <b>client home -&gt; ipaddresses -&gt; ipaddress 
				home -&gt; domain config </b>and map an IP to a domain. <br>
				<br>
				</li>
				<li>Once you map an IP to a domain, then you have to make sure that 
				the domain pings back to the same IP. Otherwise, if you try to access 
				the domain, you will get this page. So IP -&gt; domain.com should mean 
				that domain.com pings to the same IP. <br>
				<br>
				</font></li>
				</td>
			</tr>
		</table>
		</td>
		</td>
		<td width="280" valign="center"><img src="images/disableskeletonbg.gif"></td>
	</tr>
</table>

</body>

</html>
