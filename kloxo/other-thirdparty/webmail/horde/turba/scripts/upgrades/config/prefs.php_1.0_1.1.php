if (isset($prefGroups['display']['members']) &&
    ($k = array_search('sortby', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
if (isset($prefGroups['display']['members']) &&
    ($k = array_search('sortdir', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
unset($prefGroups['imsp'], $_prefs['sortby'], $_prefs['sortdir'],
      $_prefs['imsp_opt']);

$prefGroups['addressbooks']['members'][] = 'sync_books';
$prefGroups['display']['desc'] = _("Select view to display by default and paging options.");

// Address books use for synchronization
$_prefs['sync_books'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'desc' => _("Select the address books that should be used for synchronization with external devices:"),
);

$_prefs['columns']['value'] = "netcenter\temail\nverisign\temail\nlocalsql\temail";

// user preferred sorting column
// serialized array of hashes containing 'field' and 'ascending' keys
$_prefs['sortorder'] = array(
    'value' => 'a:1:{i:0;a:2:{s:5:"field";s:8:"lastname";s:9:"ascending";b:1;}}',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit',
);

// Used to keep track of which turba maintenance tasks have been run.
$_prefs['turba_maintenance_tasks'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit'
);
