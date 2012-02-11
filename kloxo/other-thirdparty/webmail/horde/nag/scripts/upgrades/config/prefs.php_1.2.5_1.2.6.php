$prefGroups['notification']['members'][] = 'task_notification_exclude_self';

$_prefs['task_notification_exclude_self'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Don't send me a notification if I've added, changed or deleted the task?")
);
