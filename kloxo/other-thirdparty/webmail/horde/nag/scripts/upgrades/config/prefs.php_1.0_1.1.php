array_unshift($prefGroups['display']['members'], 'show_panel');

$prefGroups['tasks'] = array(
    'column' => _("General Options"),
    'label' => _("Task Defaults"),
    'desc' => _("Defaults for new tasks"),
    'members' => array('default_due', 'default_due_days', 'defaultduetimeselect'),
);

$prefGroups['notification']['desc'] = _("Choose if you want to be notified of task changes and task alarms.");
if (!empty($GLOBALS['conf']['alarms']['driver'])) {
    $prefGroups['notification']['members'][] = 'task_alarms';
}

// show the task list options panel?
// a value of 0 = no, 1 = yes
$_prefs['show_panel'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show task list options panel?")
);

$_prefs['sortby']['enum'][NAG_SORT_OWNER] = _("Task List");
$_prefs['altsortby']['enum'][NAG_SORT_OWNER] = _("Task List");

// default to tasks having a due date?
$_prefs['default_due'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("When creating a new task, should it default to having a due da
e?"),
);

// default number of days out for due dates
$_prefs['default_due_days'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'number',
    'desc' => _("When creating a new task, how many days in the future should t
e default due date be (0 means today)?"),
);

// default due time
$_prefs['default_due_time'] = array(
    'value' => 'now',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit',
);

// default due time selection widget
$_prefs['defaultduetimeselect'] = array('type' => 'special');

// new task notifications
$_prefs['task_notification']['enum'] =
    array('' => _("No"),
          'owner' => _("On my task lists only"),
          'show' => _("On all shown task lists"),
          'read' => _("On all task lists I have read access to"));

// alarm methods
$_prefs['task_alarms'] = array(
    'value' => 'a:1:{s:6:"notify";a:0:{}}',
    'locked' => false,
    'shared' => false,
    'type' => 'alarm',
    'desc' => _("Choose how you want to receive reminders for tasks with alarms:"),
);
