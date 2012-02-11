$cfgSources['localsql']['map'] = array(
        '__key' => 'object_id',
        '__owner' => 'owner_id',
        '__type' => 'object_type',
        '__members' => 'object_members',
        '__uid' => 'object_uid',
        'firstname' => 'object_firstname',
        'lastname' => 'object_lastname',
        'middlenames' => 'object_middlenames',
        'namePrefix' => 'object_nameprefix',
        'nameSuffix' => 'object_namesuffix',
        'name' => array('fields' => array('namePrefix', 'firstname',
                                          'middlenames', 'lastname',
                                          'nameSuffix'),
                        'format' => '%s %s %s %s %s',
                        'parse' => array(
                            array('fields' => array('firstname', 'middlenames',
                                                    'lastname'),
                                  'format' => '%s %s %s'),
                            array('fields' => array('firstname', 'lastname'),
                                  'format' => '%s %s'))),
        // This is a shorter version of a "name" composite field which only
        // consists of the first name and last name.
        // 'name' => array('fields' => array('firstname', 'lastname'),
        //                 'format' => '%s %s'),
        'alias' => 'object_alias',
        'birthday' => 'object_bday',
        'homeStreet' => 'object_homestreet',
        'homePOBox' => 'object_homepob',
        'homeCity' => 'object_homecity',
        'homeProvince' => 'object_homeprovince',
        'homePostalCode' => 'object_homepostalcode',
        'homeCountry' => 'object_homecountry',
        // This is an example composite field for addresses, so you can display
        // the various map links. If you use this, be sure to add 'homeAddress'
        // to the 'tabs' parameter below.
        //'homeAddress' => array('fields' => array('homeStreet', 'homeCity',
        //                                         'homeProvince',
        //                                         'homePostalCode'),
        //                       'format' => "%s \n %s, %s  %s"),
        'workStreet' => 'object_workstreet',
        'workPOBox' => 'object_workpob',
        'workCity' => 'object_workcity',
        'workProvince' => 'object_workprovince',
        'workPostalCode' => 'object_workpostalcode',
        'workCountry' => 'object_workcountry',
        'timezone' => 'object_tz',
        'email' => 'object_email',
        'homePhone' => 'object_homephone',
        'workPhone' => 'object_workphone',
        'cellPhone' => 'object_cellphone',
        'fax' => 'object_fax',
        'pager' => 'object_pager',
        'title' => 'object_title',
        'role' => 'object_role',
        'company' => 'object_company',
        'category' => 'object_category',
        'notes' => 'object_notes',
        'website' => 'object_url',
        'freebusyUrl' => 'object_freebusyurl',
        'pgpPublicKey' => 'object_pgppublickey',
        'smimePublicKey' => 'object_smimepublickey',
);
$cfgSources['localsql']['tabs'] = array(
        _("Personal") => array('firstname', 'lastname', 'middlenames',
                               'namePrefix', 'nameSuffix', 'name', 'alias',
                               'birthday'),
        _("Location") => array('homeStreet', 'homePOBox', 'homeCity',
                               'homeProvince', 'homePostalCode', 'homeCountry',
                               'workStreet', 'workPOBox', 'workCity',
                               'workProvince', 'workPostalCode', 'workCountry',
                               'timezone'),
        _("Communications") => array('email', 'homePhone', 'workPhone',
                                     'cellPhone', 'fax', 'pager'),
        _("Organization") => array('title', 'role', 'company'),
        _("Other") => array('category', 'notes', 'website', 'freebusyUrl',
                            'pgpPublicKey', 'smimePublicKey'),
);
$cfgSources['localsql']['list_name_field'] = 'lastname';

$cfgSources['favourites'] = array(
    'title' => _("Favourite Recipients"),
    'type' => 'favourites',
    'params' => array(
        'limit' => 10
    ),
    'map' => array(
        '__key' => 'email',
        'name' => 'email',
        'email' => 'email'
    ),
    'search' => array(
        'name',
        'email'
    ),
    'strict' => array(
        'id',
    ),
    'export' => true,
    'browse' => true,
);
