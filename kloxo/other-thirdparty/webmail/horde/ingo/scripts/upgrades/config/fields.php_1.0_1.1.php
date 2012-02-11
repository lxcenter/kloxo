$ingo_fields['Resent-from']['label'] = _("Resent-From");
$ingo_fields['Resent-to']['label'] = _("Resent-To");
$ingo_fields['To,Cc,Bcc,Resent-to'] = array(
    'label' => _("Destination (To,Cc,Bcc,etc)"),
    'type' => INGO_STORAGE_TYPE_HEADER
);
$ingo_fields['From,Sender,Reply-to,Resent-from'] = array(
    'label' => _("Source (From,Reply-to,etc)"),
    'type' => INGO_STORAGE_TYPE_HEADER
);
$ingo_fields['To,Cc,Bcc,Resent-to,From,Sender,Reply-to,Resent-from'] = array(
    'label' => _("Participant (From,To,etc)"),
    'type' => INGO_STORAGE_TYPE_HEADER
);
$ingo_fields['Body']['tests'] = array(
    'contains', 'not contain', 'is', 'not is', 'begins with', 'not begins with',
    'ends with', 'not ends with', 'regex', 'matches', 'not matches');
