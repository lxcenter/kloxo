if (isset($prefGroups['display']['members']) &&
    ($k = array_search('show_tasklist', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
unset($_prefs['show_tasklist']);

array_unshift($prefGroups['display']['members'], 'tasklist_columns');

// columns in the list view
$_prefs['tasklist_columns'] = array(
    'value' => 'a:3:{i:0;s:8:"priority";i:1;s:3:"due";i:2;s:8:"category";}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'enum' => array('tasklist' => _("Task List"),
                    'priority' => _("Priority"),
                    'assignee' => _("Assignee"),
                    'due' => _("Due Date"),
                    'category' => _("Category")),
    'desc' => _("Select the columns that should be shown in the list view:")
);
 
$_prefs['sortby']['enum'][NAG_SORT_ASSIGNEE] = _("Assignee");
$_prefs['altsortby']['enum'][NAG_SORT_ASSIGNEE] = _("Assignee");

$_prefs['default_tasklist']['shared'] = false;
