<?php
/**
 * $Horde: horde/services/prefs/index.php,v 1.1.2.10 2010/09/27 17:37:49 slusarz Exp $
 *
 * Copyright 2006-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Chuck Hagenbuch <chuck@horde.org>
 */

/* SECURITY: This script is subject to CSRF attacks. It has been removed
 * in Horde 4.  However, for BC, it needs to remain for certain applications &
 * preferences.  The following is the list of allowed prefs to be set using
 * this script. */
$wl_prefs = array(
    'dimp' => array(
        'show_preview'
    )
);


@define('HORDE_BASE', dirname(dirname(dirname(__FILE__))));
require_once HORDE_BASE . '/lib/core.php';

$registry = &Registry::singleton();

/* Which application/preference? */
$app = Util::getFormData('app');
$pref = Util::getFormData('pref');
if (!isset($wl_prefs[$app][$pref])) {
    exit;
}

/* Load $app's base environment, but don't request that the app perform
 * authentication beyond Horde's. */
$authentication = 'none';
$appbase = $registry->get('fileroot', $app);
require_once $appbase . '/lib/base.php';

/* Which action. */
if (Util::getPost('pref') == $pref) {
    /* POST for saving a pref. */
    $prefs->setValue($pref, Util::getPost('value'));
}

/* GET returns the current value, POST returns the new value. */
header('Content-type: text/plain');
echo $prefs->getValue($pref);
