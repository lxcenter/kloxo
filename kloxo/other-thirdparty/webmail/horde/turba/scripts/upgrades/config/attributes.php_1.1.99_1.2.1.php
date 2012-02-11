$attributes['birthday']['params']['format_out'] = $GLOBALS['prefs']->getValue('date_format');
$attributes['anniversary']['params']['format_out'] = $GLOBALS['prefs']->getValue('date_format');
$attributes['photo'] = array(
    'label' => _("Photo"),
    'type' => 'image',
    'required' => false,
    'params' => array('show_upload' => true, 'show_keeporig' => true, 'max_filesize'  => null),
);
$attributes['phototype'] = array(
    'label' => _("Photo MIME Type"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['logo'] = array(
    'label' => _("Logo"),
    'type' => 'image',
    'required' => false,
    'params' => array('show_upload' => true, 'show_keeporig' => true, 'max_filesize'  => null),
);
$attributes['logotype'] = array(
    'label' => _("Logo MIME Type"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['pgpPublicKey'] = array(
    'label' => _("PGP Public Key"),
    'type' => 'pgp',
    'required' => false,
    'params' => array('gpg' => '/usr/bin/gpg', 'temp_dir' => Horde::getTempDir(), 'rows' => 3, 'cols' => 40)
);
$attributes['smimePublicKey'] = array(
    'label' => _("S/MIME Public Certificate"),
    'type' => 'smime',
    'required' => false,
    'params' => array('temp_dir' => Horde::getTempDir(), 'rows' => 3, 'cols' => 40)
);
$attributes['category'] = array(
    'label' => _("Category"),
    'type' => 'category',
    'params' => array(),
    'required' => false
);
