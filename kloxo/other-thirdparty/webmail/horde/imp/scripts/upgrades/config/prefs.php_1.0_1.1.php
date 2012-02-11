if (isset($prefGroups['identities']['members']) &&
    ($k = array_search('mail_hdr', $prefGroups['identities']['members'])) !== false) {
    unset($prefGroups['identities']['members'][$k]);
}
if (isset($prefGroups['compose']['members']) &&
    ($k = array_search('num_words', $prefGroups['compose']['members'])) !== false) {
    unset($prefGroups['compose']['members'][$k]);
}
if (isset($prefGroups['newmail']['members']) &&
    ($k = array_search('nav_audio', $prefGroups['newmail']['members'])) !== false) {
    unset($prefGroups['newmail']['members'][$k]);
}
if (isset($prefGroups['display']['members']) &&
    ($k = array_search('show_legend', $prefGroups['display']['members'])) !== false) {
    unset($prefGroups['display']['members'][$k]);
}
if (isset($prefGroups['addressbooks']['members']) &&
    ($k = array_search('auto_expand', $prefGroups['addressbooks']['members'])) !== false) {
    unset($prefGroups['addressbooks']['members'][$k]);
}
if (isset($prefGroups['search']['members']) &&
    ($k = array_search('defaultsearchselect', $prefGroups['search']['members'])) !== false) {
    unset($prefGroups['search']['members'][$k]);
}
unset($_prefs['preview_maxlen']['enums'][-1],
      $_prefs['show_legend'],
      $_prefs['defaultsearchselect'],
      $_prefs['default_search'],
      $_prefs['auto_expand']);

$is_pop3 = isset($_SESSION['imp']) &&
           $_SESSION['imp']['base_protocol'] == 'pop3';
if ($is_pop3) {
    unset($prefGroups['server'],
          $prefGroups['fetchmail'],
          $prefGroups['search'],
          $_prefs['delete_spam_after_report']['enum'][2]);
    if (($k = array_search('initialpageselect', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('rename_sentmail_monthly', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('delete_sentmail_monthly', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('delete_sentmail_monthly_keep', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_sentmail', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_sentmail_interval', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_sentmail_keep', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_trash', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_trash_interval', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_trash_keep', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_spam', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_spam_interval', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('purge_spam_keep', $prefGroups['logintasks']['members'])) !== false) {
        unset($prefGroups['logintasks']['members'][$k]);
    }
    if (($k = array_search('use_trash', $prefGroups['delmove']['members'])) !== false) {
        unset($prefGroups['delmove']['members'][$k]);
    }
    if (($k = array_search('trashselect', $prefGroups['delmove']['members'])) !== false) {
        unset($prefGroups['delmove']['members'][$k]);
    }
    if (($k = array_search('use_vtrash', $prefGroups['delmove']['members'])) !== false) {
        unset($prefGroups['delmove']['members'][$k]);
    }
    if (($k = array_search('empty_trash_menu', $prefGroups['delmove']['members'])) !== false) {
        unset($prefGroups['delmove']['members'][$k]);
    }
    if (($k = array_search('nav_expanded', $prefGroups['display']['members'])) !== false) {
        unset($prefGroups['display']['members'][$k]);
    }
    if (($k = array_search('nav_expanded_sidebar', $prefGroups['display']['members'])) !== false) {
        unset($prefGroups['display']['members'][$k]);
    }
}

$prefGroups['compose']['members'][] = 'jseditor';
$prefGroups['compose']['members'][] = 'fckeditor_buttons';
$prefGroups['compose']['members'][] = 'xinha_hide_buttons';
$prefGroups['compose']['members'][] = 'compose_cursor';
$prefGroups['compose']['members'][] = 'compose_cc';
$prefGroups['compose']['members'][] = 'compose_bcc';
$prefGroups['compose']['members'][] = 'reply_format';
$prefGroups['compose']['members'][] = 'forward_default';
$prefGroups['compose']['members'][] = 'forward_bodytext';

$prefGroups['viewing']['members'][] = 'mail_hdr';
$prefGroups['viewing']['members'][] = 'alternative_display';

$prefGroups['newmail']['members'][] = 'nav_poll_all';
$prefGroups['newmail']['members'][] = 'soundselect';

$prefGroups['display']['members'][] = 'tree_view';

$_prefs['sent_mail_folder']['value'] = _("Sent");
$_prefs['drafts_folder']['value'] = _("Drafts");
$_prefs['trash_folder']['value'] = _("Trash");
$_prefs['spam_folder']['value'] = _("Spam");

// purge sent-mail folder?
$_prefs['purge_sentmail'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Purge old messages in the sent-mail folder(s)?"),
    'help' => 'prefs-purge_sentmail');

// how often to purge the Sent-Mail folder?
// 'value': yearly = 1, monthly = 2, weekly = 3, daily = 4, every login = 5
$_prefs['purge_sentmail_interval'] = array(
    'value' => '2',
    'locked' => false,
    'shared' => false,
    'type' => 'select',
    'desc' => _("Purge sent-mail how often:"),
    'help' => 'prefs-purge_sentmail_interval');

// when purging sent-mail folder, purge messages older than how many days?
$_prefs['purge_sentmail_keep'] = array(
    'value' => 30,
    'locked' => false,
    'shared' => false,
    'type' => 'number',
    'desc' => _("Purge messages in sent-mail folder(s) older than this amount o
 days."),
    'help' => 'prefs-purge_sentmail_keep');

// purge Spam folder?
$_prefs['purge_spam'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Purge old messages in the Spam folder?"),
    'help' => 'prefs-purge_spam');

// how often to purge the Spam folder?
// 'value': yearly = 1, monthly = 2, weekly = 3, daily = 4, every login = 5
$_prefs['purge_spam_interval'] = array(
    'value' => '2',
    'locked' => false,
    'shared' => false,
    'type' => 'select',
    'desc' => _("Purge Spam how often:"),
    'help' => 'prefs-purge_spam_interval');

// when purging Spam folder, purge messages older than how many days?
$_prefs['purge_spam_keep'] = array(
    'value' => 30,
    'locked' => false,
    'shared' => false,
    'type' => 'number',
    'desc' => _("Purge messages in Spam folder older than this amount of days."),
    'help' => 'prefs-purge_spam_keep');

// The default JS HTML editor.
$_prefs['jseditor'] = array(
    'value' => 'xinha',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    // To use 'fckeditor', you must have Horde 3.2 or greater installed.
    'enum' => array('fckeditor' => _("FCKeditor"),
                    'xinha' => _("Xinha")),
    'desc' => _("The javascript editor to use on the compose page.")
);

// The list of buttons to show in FCKeditor
$_prefs['fckeditor_buttons'] = array(
    'value' => "[['FontFormat','FontName','FontSize'],['Bold','Italic','Underline'],['TextColor','BGColor'],'/',['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],['OrderedList','UnorderedList','Outdent','Indent'],['Link'],['Undo','Redo']]",
    'locked' => true,
    'shared' => false,
    'type' => 'textarea',
    'desc' => _("The buttons to show when using FCKeditor.")
);

// Hidden Xinha buttons.
$_prefs['xinha_hide_buttons'] = array(
    'value' => 'a:25:{i:0;s:11:"popupeditor";i:1;s:13:"strikethrough";i:2;s:13:"textindicator";i:3;s:9:"subscript";i:4;s:11:"superscript";i:5;s:20:"inserthorizontalrule";i:6;s:11:"insertimage";i:7;s:11:"inserttable";i:8;s:9:"selectall";i:9;s:5:"print";i:10;s:3:"cut";i:11;s:4:"copy";i:12;s:5:"paste";i:13;s:9:"overwrite";i:14;s:6:"saveas";i:15;s:8:"killword";i:16;s:10:"clearfonts";i:17;s:12:"removeformat";i:18;s:13:"toggleborders";i:19;s:10:"splitblock";i:20;s:11:"lefttoright";i:21;s:11:"righttoleft";i:22;s:8:"htmlmode";i:23;s:8:"showhelp";i:24;s:5:"about";}',
    'locked' => false,
    'shared' => false,
    'type' => 'multienum',
    'enum' => array(
        'popupeditor' => _("Maximize/Minimize Editor"),
        'formatblock' => _("Text Format"),
        'fontname' => _("Text Font"),
        'fontsize' => _("Text Size"),
        'bold' => _("Bold"),
        'italic' => _("Italic"),
        'underline' => _("Underline"),
        'strikethrough' => _("Strikethrough"),
        'forecolor' => _("Font Color"),
        'hilitecolor' => _("Background Color"),
        'textindicator' => _("Current style"),
        'subscript' => _("Subscript"),
        'superscript' => _("Superscript"),
        'justifyleft' => _("Justify Left"),
        'justifycenter' => _("Justify Center"),
        'justifyright' => _("Justify Right"),
        'justifyfull' => _("Justify Full"),
        'insertorderedlist' => _("Ordered List"),
        'insertunorderedlist' => _("Bulleted List"),
        'outdent' => _("Decrease Indent"),
        'indent' => _("Increase Indent"),
        'inserthorizontalrule' => _("Horizontal Rule"),
        'createlink' => _("Insert Web Link"),
        'insertimage' => _("Insert/Modify Image"),
        'inserttable' => _("Insert Table"),
        'undo' => _("Undoes your last action"),
        'redo' => _("Redoes your last action"),
        'selectall' => _("Select all"),
        'print' => _("Print document"),
        'cut' => _("Cut selection"),
        'copy' => _("Copy selection"),
        'paste' => _("Paste from clipboard"),
        'overwrite' => _("Insert/Overwrite"),
        'saveas' => _("Save as"),
        'killword' => _("Clear MSOffice tags"),
        'clearfonts' => _("Clear Inline Font Specifications"),
        'removeformat' => _("Remove formatting"),
        'toggleborders' => _("Toggle Borders"),
        'splitblock' => _("Split Block"),
        'lefttoright' => _("Direction left to right"),
        'righttoleft' => _("Direction right to left"),
        'htmlmode' => _("Toggle HTML Source"),
        'showhelp' => _("Help using editor"),
        'about' => _("About this editor")),
    'desc' => _("The buttons NOT to show when using Xinha.")
);

// Where should the cursor be located in the compose text area by default?
$_prefs['compose_cursor'] = array(
    'value' => 'top',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'enum' => array('top' => _("Top"),
                    'bottom' => _("Bottom"),
                    'sig' => _("Before Signature")),
    'desc' => _("Where should the cursor be located in the compose text area by
default?")
);

// Show Cc: field?
$_prefs['compose_cc'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show the Cc: header field when composing mail?")
);

// Show Bcc: field?
$_prefs['compose_bcc'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Show the Bcc: header field when composing mail?")
);

// When replying/forwarding to a message, should we use the same format as the
// original message?
$_prefs['reply_format'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("When replying/forwarding to a message, should we use the same format as the original message?"));

// What should the default forward method be?
$_prefs['forward_default'] = array(
    'value' => 'forward_all',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'enum' => array('forward_all' => _("Entire Message"),
                    'forward_body' => _("Body Text Only"),
                    'forward_attachments' => _("Body Text with Attachments")),
    'desc' => _("Default forwarding method:"),
    'help' => 'message-forward');

// Should the original message be included?
$_prefs['forward_bodytext'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Include body text in forward message by default?"));

$_prefs['html_image_addrbook']['desc'] = _("Automatically show images in HTML messages when the sender is in my address book?");

$_prefs['default_msg_charset']['value'] =
    isset($GLOBALS['nls']['emails'][$GLOBALS['language']])
        ? $GLOBALS['nls']['emails'][$GLOBALS['language']]
        : (isset($GLOBALS['nls']['charsets'][$GLOBALS['language']])
            ? $GLOBALS['nls']['charsets'][$GLOBALS['language']]
            : '');

// how do we display alternative mime parts?
$_prefs['alternative_display'] = array(
    'value' => 'none',
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'enum' => array('above'   => _("Above the message text"),
                    'below' => _("Below the message text"),
                    'none'   => _("Not at all")),
    'desc' => _("Where do you want to display links to alternative formats of a message?"));

$_prefs['disposition_send_mdn']['desc'] = _("Prompt to send read receipt when requested by the sender?");

$_prefs['nav_audio']['type'] = 'implicit';

// sound selection widget
$_prefs['soundselect'] = array('type' => 'special');

$_prefs['mailbox_start']['desc'] = _("When opening a new mailbox for the first time, which page do you want to start on?");

// sort prefs for individual folders
$_prefs['sortpref'] = array(
    'value' => 'a:0:{}',
    'locked' => false,
    'shared' => false,
    'type' => 'implicit');

// folder tree view style
$_prefs['tree_view'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'enum',
    'enum' => array(0 => _("Combine all namespaces"),
                    1 => _("Show non-private mailboxes in separate folders")),
    'desc' => _("How should namespaces be displayed in the folder tree view?")
);

// poll all folders for new mail?
$_prefs['nav_poll_all'] = array(
    'value' => false,
    'locked' => isset($_SESSION['imp']) && $_SESSION['imp']['base_protocol'] == 'pop3',
    'shared' => false,
    'type' => 'checkbox',
    'desc' => _("Poll all folders for new mail?"));

// run filters when sidebar updates?
$_prefs['filter_on_sidebar'] = array(
    'value' => 0,
    'locked' => false,
    'shared' => false,
    'type' => 'implicit');

$_prefs['pgp_verify'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'implicit');

$_prefs['smime_verify'] = array(
    'value' => 1,
    'locked' => false,
    'shared' => false,
    'type' => 'implicit');
