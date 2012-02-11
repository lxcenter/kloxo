foreach (array('reply_quote', 'reply_format', 'forward_default', 'forward_bodytext', 'reply_headers', 'attrib_text', 'folderselect', 'close_draft', 'unseen_drafts') as $p) {
    if (isset($prefGroups['compose']['members']) &&
        ($k = array_search($p, $prefGroups['compose']['members'])) !== false) {
        unset($prefGroups['compose']['members'][$k]);
    }
}
array_unshift($prefGroups['compose']['members'], 'mailto_handler');

$prefGroups['reply_forward'] = array(
    'column' => _("Message Options"),
    'label' => _("Message Replies/Forwards"),
    'desc' => _("Customize how you reply to or forward mail."),
    'members' => array('reply_quote', 'reply_format', 'forward_default',
                       'forward_bodytext', 'reply_headers', 'attrib_text')
);

$prefGroups['drafts'] = array(
    'column' => _("Message Options"),
    'label' => _("Message Drafts"),
    'desc' => _("Customize how to deal with message drafts."),
    'members' => array('folderselect', 'close_draft', 'unseen_drafts')
);

// Link to add a Firefox 3 mailto: handler
$_prefs['mailto_handler'] = array(
    'type' => 'link',
    'xurl' => 'javascript:if(typeof navigator.registerProtocolHandler==\'undefined\')alert(\''
        . addslashes(_("Your browser does not support this feature."))
        . '\');else navigator.registerProtocolHandler(\'mailto\',\''
        . Util::addParameter(Horde::applicationUrl('compose.php', true),
                             array('actionID' => 'mailto_link', 'to' => ''), false)
        . '%s\',\'' . $GLOBALS['registry']->get('name') . '\');',
    'desc' => sprintf(_("Click here to open all mailto: links in %s."), $GLOBALS['registry']->get('name')));

// Only works w/DIMP - does not show in prefs screen by default
$_prefs['auto_save_drafts']['value'] = 5;

// should we show large blocks of quoted text or hide them?
$_prefs['show_quoteblocks']['enum']['list'] = _("Hidden in List Messages");
$_prefs['show_quoteblocks']['enum']['listthread'] = _("Hidden in Thread View and List Messages");

// use Virtual Trash folder
$_prefs['use_vtrash']['type'] = 'implicit';
