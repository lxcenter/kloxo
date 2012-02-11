array_unshift($prefGroups['display']['members'], 'show_notepad', 'show_panel');

// show a notepad column in the list view?
$_prefs['show_notepad'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Should the Notepad be shown in its own column in the List view?")
);

// show the notepad options panel?
// a value of 0 = no, 1 = yes
$_prefs['show_panel'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show notepad options panel?")
);

$_prefs['sortby']['enum'][MNEMO_SORT_NOTEPAD] = _("Notepad");
