if (isset($prefGroups['display']['members']) &&
    ($k = array_search('show_panel', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
$_prefs['show_panel']['type'] = 'implicit';