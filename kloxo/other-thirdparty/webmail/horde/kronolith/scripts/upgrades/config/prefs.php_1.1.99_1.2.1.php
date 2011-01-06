$prefGroups['maintenance'] = array(
    'column' => _("Events"),
    'label' => _("Maintenance"),
    'desc' => _("Set options for deleting old events."),
    'members' => array('purge_events', 'purge_events_interval', 'purge_events_keep')
);

$_prefs['purge_events'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Purge old events from your calender?"),
);

// 'value': yearly = 1, monthly = 2, weekly = 3, daily = 4, every login = 5
$_prefs['purge_events_interval'] = array(
    'value' => '2',
    'locked' => false,
    'shared' => false,
    'type' => 'select',
    'desc' => _("Purge old events how often:"),
);

$_prefs['purge_events_keep'] = array(
    'value' => 365,
    'locked' => false,
    'shared' => false,
    'type' => 'number',
    'desc' => _("Purge old events older than this amount of days."),
);

// last time maintenance was run.
// value is a UNIX timestamp of the last time maintenance ran for the user.
$_prefs['last_kronolith_maintenance'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'implicit'
);
