if (isset($prefGroups['freebusy']['members']) &&
    ($k = array_search('search_abook_select', $prefGroups['freebusy']['members'])) !== false) {
    unset($prefGroups['freebusy']['members'][$k]);
}
if (isset($prefGroups['freebusy']['members']) &&
    ($k = array_search('display_contact', $prefGroups['freebusy']['members'])) !== false) {
    unset($prefGroups['freebusy']['members'][$k]);
}
unset($prefGroups['tasks'], $prefGroups['remote'],
      $_prefs['show_tasks'], $_prefs['show_task_colors'],
      $_prefs['search_abook'], $_prefs['search_abook_select']);

$prefGroups['view']['members'] = array_merge(
    $prefGroups['view']['members'],
    array('day_hour_force', 'show_time', 'show_location', 'show_panel',
          'show_external_colors'));

if (!empty($GLOBALS['conf']['holidays']['enable'])) {
    $prefGroups['holidays'] = array(
        'column' => _("Calendars"),
        'label' => _("Holidays"),
        'desc' => _("Choose which holidays to display"),
        'members' => array('holiday_drivers'),
    );
}

$prefGroups['event_options'] = array(
    'column' => _("Events"),
    'label' => _("Event Defaults"),
    'desc' => _("Set default values for new events."),
    'members' => array('default_alarm_management'),
);

$prefGroups['notification']['column'] = _("Events");
$prefGroups['notification']['desc'] = _("Choose how you want to be notified about event changes, event alarms and upcoming events.");
$prefGroups['notification']['members'][] = 'event_notification_exclude_self';
$prefGroups['notification']['members'][] = 'daily_agenda';
if (!empty($GLOBALS['conf']['alarms']['driver'])) {
    $prefGroups['notification']['members'][] = 'event_alarms';
}

$prefGroups['freebusy']['desc'] = _("Set your Free/Busy calendars and your own and other users' Free/Busy options.");

if ($GLOBALS['registry']->hasMethod('contacts/sources')) {
    $prefGroups['addressbooks'] = array(
        'column' => _("Other Options"),
        'label' => _("Address Books"),
        'desc' => _("Select address book sources for adding and searching for addresses."),
        'members' => array('display_contact', 'sourceselect'),
    );
}

// enforce hour slots?
$_prefs['day_hour_force'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Restrict day and week views to these time slots, even if there <strong>are</strong> earlier or later events?"),
);

// show event start/end times in the calendar and/or print views?
$_prefs['show_time'] = array(
    'value' => 'a:1:{i:0;s:5:"print";}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'enum' => array('screen' => _("Month, Week, and Day Views"),
                    'print' => _("Print Views"),
     ),
    'desc' => _("Choose the views to show event start and end times in:"),
);

// show event location in the calendar and/or print views?
$_prefs['show_location'] = array(
    'value' => 'a:1:{i:0;s:5:"print";}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'enum' => array('screen' => _("Month, Week, and Day Views"),
                    'print' => _("Print Views"),
     ),
    'desc' => _("Choose the views to show event locations in:"),
);

// show the calendar options panel?
// a value of 0 = no, 1 = yes
$_prefs['show_panel'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show calendar options panel?"),
);

$_prefs['show_fb_legend']['desc'] = _("Show Free/Busy legend?");

// show external event colors?
$_prefs['show_external_colors'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show external events using category colors?"),
);

// Which drivers are we supposed to use to examine holidays?
$_prefs['holiday_drivers'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'desc' => _("Which kind of holidays do you want to get displayed?"),
);

// default alarm
$_prefs['default_alarm'] = array(
    'value' => '',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit',
);
$_prefs['default_alarm_management'] = array('type' => 'special');

// daily agenda
$_prefs['daily_agenda'] = array(
    'value' => '',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'enum' => array('' => _("No"),
                    'owner' => _("On my calendars only"),
                    'show' => _("On all shown calendars"),
                    'read' => _("On all calendars I have read access to")),
    'desc' => _("Choose if you want to receive daily agenda email reminders:")
);

$_prefs['event_notification_exclude_self'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Don't send me a notification if I've added, changed or deleted the event?")
);

$_prefs['event_reminder']['desc'] = _("Choose if you want to receive reminders for events with alarms:");

// alarm methods
$_prefs['event_alarms'] = array(
    'value' => 'a:1:{s:6:"notify";a:0:{}}',
    'locked' => false,
    'shared' => false,
    'type' => 'alarm',
    'desc' => _("Choose how you want to receive reminders for events with alarms:")
);

$_prefs['freebusy_days']['desc'] = _("How many days of Free/Busy information should be generated?");

// address book selection widget
$_prefs['sourceselect'] = array('type' => 'special');

// address book(s) to use when expanding addresses
// You can provide default values this way (note the \t and the double quotes):
// 'value' => "source_one\tsource_two"
// refer to turba/config/sources.php for possible source values
$_prefs['search_sources'] = array(
    'value' => "",
    'locked' => false,
    'shared' => false,
    'type' => 'implicit',
);

// field(s) to use when expanding addresses
// This depends on the search_sources preference if you want to provide
// default values:
// 'value' => "source_one\tfield_one\tfield_two\nsource_two\tfield_three"
// will search the fields 'field_one' and 'field_two' in source_one and
// 'field_three' in source_two.
// refer to turba/config/sources.php for possible source and field values
$_prefs['search_fields'] = array(
    'value' => "",
    'locked' => false,
    'shared' => false,
    'type' => 'implicit',
);

$_prefs['fb_cals']['desc'] = _("Choose the calendars to include when generating Free/Busy URLs:");
