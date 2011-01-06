$_prefs['rules']['value'] = 'a:5:{i:0;a:2:{s:4:"name";s:9:"Whitelist";s:6:"action";i:' . INGO_STORAGE_ACTION_WHITELIST . ';}i:1;a:3:{s:4:"name";s:8:"Vacation";s:6:"action";i:' . INGO_STORAGE_ACTION_VACATION . ';s:7:"disable";b:1;}i:2;a:2:{s:4:"name";s:9:"Blacklist";s:6:"action";i:' . INGO_STORAGE_ACTION_BLACKLIST . ';}i:3;a:3:{s:4:"name";s:11:"Spam Filter";s:6:"action";i:' . INGO_STORAGE_ACTION_SPAM . ';s:7:"disable";b:1;}i:4;a:2:{s:4:"name";s:7:"Forward";s:6:"action";i:' . INGO_STORAGE_ACTION_FORWARD . ';}}';
$_prefs['vacation']['value'] = 'a:8:{s:9:"addresses";a:0:{}s:4:"days";i:7;s:8:"excludes";a:0:{}s:10:"ignorelist";b:1;s:6:"reason";s:0:"";s:7:"subject";s:0:"";s:5:"start";i:0;s:3:"end";i:0;}';

// Spam rule.
// Lock this preference to disable the spam rule.
$_prefs['spam'] = array(
    'value' => 'a:2:{s:6:"folder";N;s:5:"level";i:5;}',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit'
);
