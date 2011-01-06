if (isset($prefGroups['display']['members']) &&
    ($k = array_search('alpha_filter', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
unset($_prefs['alpha_filter'], $_prefs['contextmenu'],
      $_prefs['tableoperations'], $_prefs['listtype'], $_prefs['anselimage']);

$prefGroups['authentication'] = array(
    'column' => _("Your Information"),
    'label' => _("Authentication Credentials"),
    'desc' => _("Set authentication credentials like user names and passwords for external servers."),
    'members' => array('credentialsui'),
);

$prefGroups['language']['members'][] = 'first_week_day';

$prefGroups['richtext']['members'] = array('editor_plugins');

$prefGroups['syncml'] = array(
    'column' => _("Other Information"),
    'label' => _("SyncML"),
    'desc' => _("Configuration for syncing with PDAs, Smartphones and Outlook."),
    'url' => 'services/portal/syncml.php',
    'members' => array()
);

$_prefs['default_identity']['enum'] = (isset($GLOBALS['identity']) && is_object($GLOBALS['identity'])) ? $GLOBALS['identity']->getAll('id') : array();

// identify email confirmation
$_prefs['confirm_email'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => true,
    'type' => 'implicit',
);


// Authentication Options

// credentials
$_prefs['credentials'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => true,
    'type' => 'implicit'
);

// credentials interface
$_prefs['credentialsui'] = array(
    'shared' => true,
    'type' => 'special',
);


// what day should be displayed as the first day of the week?
$_prefs['first_week_day'] = array(
    'value' => '0',
    'locked' => false,
    'shared' => true,
    'type' => 'enum',
    'desc' => _("Which day would you like to be displayed as the first day of the week?"),
    'enum' => array('0' => _("Sunday"),
                    '1' => _("Monday"))
);


$_prefs['editor_plugins'] = array(
    'value' => 'a:2:{i:0;s:8:"ListType";i:1;s:12:"CharacterMap";}',
    'locked' => false,
    'shared' => true,
    'type' => 'multienum',
    'enum' => array(
        'ContextMenu' => _("Right click context menu"),
        'TableOperations' => _("Table operations menu bar"),
        'ListType' => _("Allow setting of ordered list type"),
        'CharacterMap' => _("Special characters"),
        'AnselImage' => _("Insertion of images from Photo Galleries in text")),
    'desc' => _("Select editor plugins")
);
