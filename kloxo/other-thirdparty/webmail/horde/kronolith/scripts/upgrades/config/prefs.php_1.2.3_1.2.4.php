if (isset($prefGroups['view']['members']) &&
    ($k = array_search('show_panel', $prefGroups['view']['members'])) !== false) {
    unset($prefGroups['view']['members'][$k]);
}
$_prefs['show_panel']['type'] = 'implicit';