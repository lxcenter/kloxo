<?php
# [FIXME] $locale = get_language(); and use $_SESSION for store language
$locale = '';
if(isset($_REQUEST['locale'])){
	switch($_REQUEST['locale']){
		case 'en':
			$locale = 'en';
		break;
		case 'en_US':
			$locale = 'en_US';
		break;
		case 'es':
			$locale = 'es';
		break;
		case 'es_ES':
			$locale = 'es_ES';
		break;
		default:
			$locale = '';
		break;
	}
	if(!empty($locale)){
		$_SESSION['locale'] = $locale;
	}
	elseif(empty($_SESSION['locale'])){
		$_SESSION['locale'] = 'en_US';
	}
}
elseif(empty($_SESSION['locale'])){
	$_SESSION['locale'] = 'en_US';
}
$locale = $_SESSION['locale']; # Default en_US
if(empty($locale)){
	$locale = 'en_US';
}
putenv("LC_ALL=$locale");

$current_locale = setlocale(LC_ALL, $locale);
if(empty($current_locale))
{
	dprint('Setting default locale to en_US');
	$current_locale = 'en_US';
}
dprint('Current locale: '.$current_locale.'<br />');
$gettext_domain = bindtextdomain('kloxo', '/usr/local/lxlabs/kloxo/httpdocs/l18n');
if(empty($gettext_domain)){
	$error_msg = 'Could not load the text domain for translate strings.';
	dprint($error_msg);
	log_log('error', $error_msg);
}
else{
	dprint('Gettext domain: '.$gettext_domain.'<br />');
}
dprint('Text domain: '.textdomain('kloxo'));