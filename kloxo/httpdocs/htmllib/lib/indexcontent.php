<?php
require_once('l18n/l18n.php');

$accountlist = array('client' => "Kloxo Account",'domain' => 'Domain Owner', 'mailaccount' => "Mail Account");
$progname = $sgbl->__var_program_name;

if (lxfile_exists("__path_program_htmlbase/lib/indexheader_vendor.html")) {
	lreadfile("__path_program_htmlbase/lib/indexheader_vendor.html");
} else {
	lreadfile("__path_program_htmlbase/lib/indexheader.html");
}

$ghtml->print_jscript_source("/htmllib/js/lxa.js");
if ($sgbl->is_this_slave()) { print(_('Slave Server') . "\n"); exit; }

   if(!$cgi_forgotpwd ){
	$ghtml->print_message();


	if (if_demo()) {
		include_once "lib/demologins.php";
	} else {
?>

	<style type="text/css">
	@import url("/htmllib/lib/admin_login.css");
	</style>
<div id="ctr" align="center">
	<div class="login">
		<div class="login-form">
			<div align="center">
				<font name="Verdana" size="5" color="red">
					<b><?php echo _('Login');?></b>
				</font>
			</div>
			<br>
			<form name=loginform action="/htmllib/phplib/" onsubmit="encode_url(loginform) ; return fieldcheck(this);" method=post>
				<div class="form-block">
					<div class="inputlabel">
						<?php echo _('Username');?>
					</div>
					<input name="frm_clientname" type="text" class="inputbox" size="30" />
					<div class="inputlabel">
					<?php echo _('Password');?>
					</div>
					<input name="frm_password" type="password" class="passbox" size="30" />
					<br />

					<input type="hidden" name="id" value="<?php echo mt_rand() ?>"> 

					<div align="left">
						<input type="submit" class="button" name="login" value="<?php echo _('Login');?>" />
					</div>
				</div>
			</form>
		</div>
	<div class="login-text">
		<div class="ctr">
			<img src="/img/login/icon.gif" width="64" height="64" alt="security" />
		</div>
		<?php echo _('<p>Welcome to ').$progname._('</p><p>Use a valid username and password to gain access to the console.</p>'); ?> 
		<a class="forgotpwd" href="javascript:document.forgotpassword.submit()">
			<font color="black">
				<u><?php echo _('Forgot Password?');?></u>
		</a>
		<form name="forgotpassword" method="post" action="/login/">
			<input type="hidden" name="frm_forgotpwd" value="1" />
		</form>
		<script>document.loginform.frm_clientname.focus();</script>
	</div>
	<div class="clr"></div>
</div>
</div>
<div id="break"></div>
		<?php
	}	
}
elseif ($cgi_forgotpwd == 1) {
?>
    	<style type="text/css">
	@import url(/htmllib/lib/admin_login.css);
	</style>
		<div id="ctr" align="center">
		<div class="login">
		<div class="login-form">
		<div align="center">
<font name=Verdana size=5 color=red >
<b> <?php echo _('Forgot Password');?> </b></font></div>
<br>
  	
		<form name=sendmail action="<?php echo $_SERVER['PHP_SELF']; ?>"  method="post">
	<div class="form-block">
	
		<div class="inputlabel"><?php echo _('Username');?></div>
<input name=frm_clientname type=text class=inputbox size=30 />
<div class="inputlabel"><?php echo _('Email ID'); ?></div>
<input name=frm_email type=text class=passbox size=30 /><br>	  	
		<div align="left"><input type=submit class=button name=forgot value="<?php echo _('Send');?>" /></div>
</div>	
		</div>
	<div class="login-text">
<div class="ctr"><img src="/img/login/icon1.gif" width="64" height="64" alt="security" /></div>
<?php echo _('<p>Welcome to ').$progname._('</p><p>Use a valid username and email-id to get password.</p><br>'); ?>

	<a class=forgotpwd  href="javascript:history.go(-1);"><font color=black><u><?php echo _('Back to login'); ?></u></a>
		
	<input type="hidden" name="frm_forgotpwd" value="2">  
		</form>
<script> document.sendmail.frm_clientname.focus(); </script>

</div><div class="clr"></div>
</div>
</div>
<div id="break"></div>


<?php
} elseif ($cgi_forgotpwd==2) {


	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	$cgi_clientname = $ghtml->frm_clientname;
	$cgi_email = $ghtml->frm_email;


	htmllib::checkForScript($cgi_clientname);
	$classname = $ghtml->frm_class;

	if (!$classname) {
		$classname = getClassFromName($cgi_clientname);
	}


	if ($cgi_clientname != "" && $cgi_email != "") { 
		$tablename = $classname;
		$rawdb = new Sqlite(null, $tablename);
		$email = $rawdb->rawQuery("select contactemail from $tablename where nname = '$cgi_clientname';");


		if($email && $cgi_email == $email[0]['contactemail']) {
			$rndstring =  randomString(8);
			$pass = crypt($rndstring);

			$rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
			$mailto = $email[0]['contactemail'];
			$name = "$cprogname";
			$email = "Admin";

			$cc = "";
			$subject = $cprogname . _('Password Reset Request') ;
			$message = _('\n\n\nYour password has been reset to the one below for your') . " " . $cprogname . " " . _('login')."\n";
			$message .= _('The Client IP address which requested the Reset:') . " " . $_SERVER['REMOTE_ADDR'] . "\n";
			$message .= _('Username:' ) . " " . $cgi_clientname . "\n";
			$message .= _('New Password:') . " " . $rndstring . "\n";

			lx_mail(null, $mailto, $subject, $message);

			$ghtml->print_redirect("/login/?frm_smessage=password_sent");

		} else {
			$ghtml->print_redirect("/login/?frm_emessage=nouser_email");
		}
	}
}
?>
</center>
</html>
