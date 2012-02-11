$prefGroups['format']['members'][] = 'name_sort';

$_prefs['name_format']['desc'] = _("Select the format used to <em>display</em> names:");

// the format to sort names.  Either 'last_first' or 'first_last'
$_prefs['name_sort'] = array(
    'value' => 'none',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'desc' => _("Select the format used to <em>sort</em> names:"),
    'enum' => array('last_first' => _("\"Lastname, Firstname\" (ie. Doe, John)"),
                    'first_last' => _("\"Firstname Lastname\"  (ie. John Doe)"),
                    'none' => _("no formatting")),
);
