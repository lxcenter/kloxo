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


if (isset($_REQUEST['locale'])) {
	switch ($_REQUEST['locale']) {
		case 'en':
			$locale = 'en_US';
			break;
		case 'en_US':
			$locale = 'en_US';
			break;
		case 'es':
			$locale = 'es_ES';
			break;
		case 'es_ES':
			$locale = 'es_ES';
			break;
		case 'nl':
			$locale = 'nl_NL';
			break;
		case 'nl_NL':
			$locale = 'nl_NL';
			break;
		case 'fr':
			$locale = 'fr_FR';
			break;
		case 'fr_FR':
			$locale = 'fr_FR';
			break;
		default:
			$locale = '';
			break;
	}

	if (!empty($locale)) {
		$_SESSION['locale'] = $locale;
	} elseif (empty($_SESSION['locale'])) {
		$_SESSION['locale'] = 'en_US';
	}
}

elseif (empty($_SESSION['locale'])) {
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$localex = explode(";", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$localex = explode(",", $localex['0']);
		$locale = $localex['0'];
		switch ($locale) {
			case 'en':
				$_SESSION['locale'] = 'en_US';
				break;
			case 'en-us':
				$_SESSION['locale'] = 'en_US';
				break;
			case 'es':
				$_SESSION['locale'] = 'es_ES';
				break;
			case 'es-es':
				$_SESSION['locale'] = 'es_ES';
				break;
			case 'nl':
				$_SESSION['locale'] = 'nl_NL';
				break;
			case 'nl-nl':
				$_SESSION['locale'] = 'nl_NL';
				break;
			case 'fr':
				$_SESSION['locale'] = 'fr_FR';
				break;
			case 'fr-fr':
				$_SESSION['locale'] = 'fr_FR';
				break;
			default:
				$_SESSION['locale'] = 'en_US';
				break;
		}

	} else {
		$_SESSION['locale'] = 'en_US';
	}
}

$locale = $_SESSION['locale'];
log_log('error', "Locale found. Language is set to $locale");

if (empty($locale)) {
	$locale = "en_US";
	log_log('error', "No locale found. Defaults now to $locale");

}

$locale_dir = $_SERVER["DOCUMENT_ROOT"] . "l18n";
putenv("LANG=$locale");
setlocale('LC_ALL', $locale);
$gettext_domain = bindtextdomain("kloxo", $locale_dir);
textdomain("kloxo");

if (empty($gettext_domain)) {
	$error_msg = 'Could not load the text domain for translate strings.';
	log_log('error', $error_msg);
}
