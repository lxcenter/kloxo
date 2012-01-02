<?php 
$accountlist = array('client' => "Kloxo Account", 'pserver' => "Server Admin", 'domain' => "Domain Owner", 'uuser' => "System User", 'ftpuser' => "Domain User(Ftp)", 'mailaccount' => "Mail User");
?> 
<head> 
		<title> <?php echo get_title() ?> </title> 
    	<link href=/htmllib/css/common.css rel=stylesheet type=text/css>
		<script language=javascript src="/htmllib/js/login.js"></script>
		<script language=javascript src="/htmllib/js/preop.js"></script>
		</head>
		<body leftmargin=0 rightmargin=0 topmargin=0 bottommargin=0>
		<center>
<table width=100%  height=" 64" border="0" valign="top" align="center" cellpadding="0" cellspacing="0">
<tr>
<td height="64" width="100%" background="/img/header/header_01.gif">
<table cellpadding=0 cellspacing=0 border=0>
<tr><td height=20 colspan=2></td></tr>
<tr><td width=15></td><td></td></tr>
</table>
</td>
<td height="64" width="20%"><img src="/img/header/header_02.gif" width="194" height="64"></td>
<td width="20%" height="64"><img src="/img/header/header_03.gif" width="238" height="64"></td>
<td width="20%" height="64"><img src="/img/header/header_04.gif" width="10" height="64"></td></tr>
<tr><td width="100%" colspan=5 bgcolor="#003366" width="10" height="2"></td></tr>
</table>

		<br><br><br><br><br><br>
		<?php

   if(!$cgi_forgotpwd ){
	$ghtml->print_message();


	if (if_demo()) {
		include_once "lib/demologins.php";
	} else {
?>
        <table align=center cellpadding=0 cellspacing=0 border=0 width=314>
		<tr><td><img src="/img/login_01.gif"></td></tr>
		<tr><td background="/img/login_02.gif">
		<form name=loginform action="/htmllib/phplib/" onsubmit="return fieldcheck(this);" method=get>


		<table cellpadding=2 cellspacing=2 border=0 width=100%>
		<tr><td width=20 height=10 ></td><td ></td></tr>    
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Username</b></font></td><td ><input type=text name=frm_clientname size=30 class=logintextbox> </td></tr>  
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Password</b></font></td><td ><input type=password name=frm_password size=30 class=logintextbox ></td></tr>  

		<?php if ($cgi_class) {
			?> 
			<input type=hidden name=frm_class value=<?php echo $cgi_class ?>  class=logintextbox >
			<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Login As: </b></font></td><td ><b> <?php echo $accountlist[$cgi_class] ?> </b>	</td></tr>  
			<?php 
		} else {
			?> 
			<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Login As</b></font></td><td >
			<select  name=frm_class class=logintextbox>
			<?php 
			foreach($accountlist as $k => $v) {
				print("<option value=$k> $v </option>");
			}
			?> 
			</select>
			</td></tr>  
			<?php 
		}
		?> 

		<tr><td colspan=3 height=10></td></tr>
		<?php 
		if ($ghtml->iset("frm_nf")) {
			print("<input type=hidden name=frm_nf value=" . $ghtml->frm_nf . ">");
		}
		?> 
		<input type=hidden name=id value="<?php echo mt_rand() ?>"> 
		<tr><td width=20></td><td >  </td><td ></td> </tr>  
		</table>
		<table cellpadding=0 cellspacing=0 border=0 bgcolor=#ddf2fb width=100%>
		<tr><td width=30 ></td><td width=150><a class=forgotpwd  href="javascript:document.forgotpassword.submit()">Forgot Password?</a></td><td align=right ><input name=login type=image src="/img/loginbtn.gif"  onMouseOver="swapImage('login','','/img/loginbtn_1.gif',1);"  onMouseOut="swapImgRestore();" ></td><td width=20></td></tr>
		</table>
		</form>
		</td></tr>
		<tr><td><img src="img/login_03.gif"></td></tr>
		</table>
		<form name="forgotpassword" method="post"  action="/">
		<input type="hidden" name=frm_forgotpwd value="1">
		</form>
         <script> document.loginform.frm_clientname.focus(); </script>

		<?php if ($cgi_class) {

			?> 
			<tr><td width=20></td><td > <a href=/> Login as Another User </a> </td><td ></td> </tr>  
			<?php 
		}
	}
		

}
elseif ($cgi_forgotpwd == 1) {
?>
        <table align=center cellpadding=0 cellspacing=0 border=0 width=314>
		<tr><td><img src="/img/forgot_01.gif"></td></tr>
		<tr><td background="/img/forgot_02.gif">
<form name=sendmail action="<?php echo $_SERVER['PHP_SELF']; ?>"  method="post">
		<table cellpadding=2 cellspacing=2 border=0 width=100%>
		<tr><td width=20 height=10 ></td><td ></td></tr>    
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Username</b></font></td><td ><input type=text name=frm_clientname size=30 class=forgottextbox> </td></tr>  
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Email Id</b></font></td><td ><input type=text name=frm_email size=30 class=forgottextbox ></td></tr>  

		<?php if ($cgi_class) {
			?> 
			<input type=hidden name=frm_class value=<?php echo $cgi_class ?>  class=logintextbox >
			<?php 
		} else {
			?> 
			<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Login Type</b></font></td><td >
			<select  name=frm_class class=logintextbox>
			<option value=client> Kloxo Account</option>
			<option value=pserver> Server Admin</option>
			<option value=domain> Domain Owner </option>
			<option value=uuser> System User </option>
			<option value=ftpuser> Domain User </option>
			<option value=mailaccount> Mail User </option>
			</select>
			</td></tr>  
			<?php 
		}
		?> 

		<tr><td colspan=3 height=10></td></tr>
		<tr><td width=20></td><td >  </td><td ></td> </tr>  
		</table>
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr><td width=30 ></td><td width=150><a class=forgotpwd href="javascript:history.go(-1);">Back to login</a></td><td align=right ><input name=forgot type=image src="/img/forgotbtn.gif"  onMouseOver="swapImage('forgot','','/img/forgotbtn_1.gif',1);"  onMouseOut="swapImgRestore();" ></td><td width=20></td></tr>
		</table>
          <input type="hidden" name="frm_forgotpwd" value="2">    
	</form>
		</td></tr>
		<tr><td><img src="img/forgot_03.gif"></td></tr>
		</table>



<script> document.sendmail.frm_clientname.focus(); </script>

<?php
} elseif ($cgi_forgotpwd==2) {



	$cgi_clientname = $ghtml->frm_clientname;
	htmllib::checkForScript($cgi_clientname);
	$cgi_email = $ghtml->frm_email;

	/*
	if ($cgi_clientname == 'admin') {
		$ghtml->print_redirect("/?frm_emessage=cannot_reset_admin");
	}
*/

	if ($cgi_clientname != "" && $cgi_email != "") { 
		$tablename = $ghtml->frm_class;
		$rawdb = new Sqlite(null, $tablename);
		$email = $rawdb->rawQuery("select contactemail from $tablename where nname = '$cgi_clientname';");


		if($email && $cgi_email == $email[0]['contactemail']) {
			$rndstring =  randomString(8);
			$pass = crypt($rndstring);

			$rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
			$mailto = $email[0]['contactemail'];
			$name = "Kloxo";
			$email = "Admin";

			$cc = "";
			$subject = "Kloxo Password Reset Request";
			$message = "\n\n\nYour password has been reset to the one below for your Kloxo login.\n";
			$message .= "The Client IP address which requested the Reset: {$_SERVER['REMOTE_ADDR']}\n";
			$message .= 'Username: '. $cgi_clientname."\n";
			$message .= 'New Password: '. $rndstring.'';

			//$message = nl2br($message);

			mail($mailto, $subject, $message, "Cc: $cc\nFrom: \"$name\" <$email>\nReply-To: \"$name\" <$email>" );

			$ghtml->print_redirect("/login.php?frm_smessage=password_sent");

		} else {
			$ghtml->print_redirect("/login.php?frm_emessage=nouser_email");
		}
	}
}
?>
</center>
</html>
