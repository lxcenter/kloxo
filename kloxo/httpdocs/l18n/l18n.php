<?php
# [FIXME] $locale = get_language(); and use $_SESSION for store language
//$locale = '1';
if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
	{
		$locale = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	}
log_log('error',"localecheck. found agent $locale");
/*
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
		case 'nl':
			$locale = 'nl';
		break;
		case 'nl_NL':
			$locale = 'nl';
		break;
		default:
			$locale = 'en';
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
 */
$_SESSION['locale'] = $locale;
$locale = $_SESSION['locale'];
log_log('error',"Locale found. Defaults now to $locale");

if(empty($locale)){
	$locale = 'en_US';
	log_log('error',"no locale found. Defaults now to $locale");

}

/*
 putenv("LC_ALL=$locale");

$current_locale = setlocale(LC_ALL, $locale);
if(empty($current_locale))
{
	dprint('Setting default locale to en_US');
	$current_locale = 'en_US';
	log_log('error',"XX no locale found. Defaults now to $current_locale");
}
dprint('Current locale: '.$current_locale.'<br />');
 */

$locale_dir = '/usr/local/lxlabs/kloxo/httpdocs/l18n';
setlocale(LC_MESSAGES, $locale);
putenv("LANGUAGE=$locale");
putenv("LANG=$locale");
$gettext_domain = bindtextdomain('kloxo', $locale_dir);
textdomain('kloxo');

if(empty($gettext_domain)){
	$error_msg = 'Could not load the text domain for translate strings.';
	log_log('error', $error_msg);
}
else{
	log_log('error','Gettext domain: '.$gettext_domain);
}
log_log('error','Text domain: '.textdomain('kloxo'));
