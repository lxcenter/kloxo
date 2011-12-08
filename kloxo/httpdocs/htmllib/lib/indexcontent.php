<?php
/*
 *  Kloxo, Hosting Control Panel
 *
 *  Copyright (C) 2000-2009	LxLabs
 *  Copyright (C) 2009-2011	LxCenter
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// ToDo: Remove Pre-Login text box from Admin>General Settings
// ToDo: Remove indexheader.html it is not used anymore, delete it in repository and at client systems.

// Issue #397 - For Kloxo 6.2.x
require_once('l18n/l18n.php');

$accountlist = array('client' => "Kloxo Account", 'domain' => 'Domain Owner', 'mailaccount' => "Mail Account");

// Store product name in a string
$progname = $sgbl->__var_program_name;

$ghtml->print_jscript_source("/htmllib/js/lxa.js");

// Abort if this is a Slave server. No direct login is allowed
// ToDo: Make a nice page for that.
if ($sgbl->is_this_slave()) {
	print(_('Slave Server') . "\n");
	exit;
}

if (!$cgi_forgotpwd) {
	$ghtml->print_message();

	// If this is a Demo server then load a different page.
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
				<div align="center" class="loginboxtitle"><?php print(_('Login')); ?></div>
				<br/>

				<form name="loginform" action="/htmllib/phplib/"
					  onsubmit="encode_url(loginform) ; return fieldcheck(this);" method="post">
					<div class="form-block">
						<div class="inputlabel"><?php print(_('Username')); ?></div>
						<input name="frm_clientname" type="text" class="inputbox" size="30"/>

						<div class="inputlabel"><?php print(_('Password')); ?></div>
						<input name="frm_password" type="password" class="passbox" size="30"/>
						<br/>
						<input type="hidden" name="id" value="<?php echo mt_rand() ?>"/>

						<div align="left"><input type="submit" class="button" name="login"
												 value="<?php print(_('Login')); ?>"/></div>
					</div>
				</form>
			</div>
			<div class="login-text">
				<div class="ctr"><img src="/img/login/icon.gif" width="64" height="64" alt="security"/></div>
				<p><?php print(_('Welcome to')); ?>&nbsp;<?php echo ucfirst($progname); ?></p>

				<p><?php print(_('Use a valid username and password to gain access to the Control Panel.')); ?></p>
				<br/>
				<a class="forgotpwd" href="javascript:document.forgotpassword.submit()">
					<div class="loginlinks"><?php print(_('Forgot Password?')); ?></div>
				</a>

				<form name="forgotpassword" method="post" action="/login/">
					<input type="hidden" name="frm_forgotpwd" value="1"/>
				</form>
				<script> document.loginform.frm_clientname.focus(); </script>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<div id="break"></div>

	<?php

	}


} elseif ($cgi_forgotpwd == 1) {
	?>

<style type="text/css">
	@import url('/htmllib/lib/admin_login.css');
</style>

<div id="ctr" align="center">
	<div class="login">
		<div class="login-form">
			<div align="center" class="loginboxtitle"><?php print(_('Forgot Password')); ?></div>
			<br/>

			<form name="sendmail" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
				<div class="form-block">
					<div class="inputlabel"><?php print(_('Username')); ?></div>
					<input name="frm_clientname" type="text" class="inputbox" size="30"/>

					<div class="inputlabel"><?php print(_('Email')); ?></div>
					<input name="frm_email" type="text" class="passbox" size="30"/>
					<br/>

					<div align="left"><input type="submit" class="button" name="forgot"
											 value="<?php print(_('Send')); ?>"/></div>
				</div>
			</form>
		</div>
		<div class="login-text">
			<div class="ctr"><img src="/img/login/icon1.gif" width="64" height="64" alt="security"/></div>
			<p><?php print(_('Welcome to')); ?>&nbsp;<?php echo ucfirst($progname); ?></p>

			<p><?php print(_('Use a valid username and email address to get a password.')); ?></p>
			<br/>
			<a class=forgotpwd href="javascript:history.go(-1);">
				<div class="loginlinks"><?php print(_('Back to Login')); ?></div>
			</a>

			<form name="forgotpassword" method="post" action="/login/">
				<input type="hidden" name="frm_forgotpwd" value="2"/>
			</form>
		</div>

		<script> document.sendmail.frm_clientname.focus(); </script>

		<div class="clr"></div>
	</div>
</div>
<div id="break"></div>

<?php

} elseif ($cgi_forgotpwd == 2) {


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


		if ($email && $cgi_email == $email[0]['contactemail']) {
			$rndstring = randomString(8);
			$pass = crypt($rndstring);

			$rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
			$mailto = $email[0]['contactemail'];
			$name = "$cprogname";
			$email = "Admin";

			$cc = "";
			$subject = "$cprogname " . _('Password Reset Request');
			$message = "\n\n\n" . _('Your password has been reset to the one below for your') . " " . $cprogname . " " . _('Control Panel login.') . "\n";
			$message .= _('The Client IP address which requested the reset:') . " " . $_SERVER['REMOTE_ADDR'] . "\n";
			$message .= _('Username:') . " " . $cgi_clientname . "\n";
			$message .= _('New Password:') . " " . $rndstring . "\n";

			lx_mail(null, $mailto, $subject, $message);

			$ghtml->print_redirect("/login/?frm_smessage=password_sent");

		} else {
			$ghtml->print_redirect("/login/?frm_emessage=nouser_email");
		}
	}
}
?>
