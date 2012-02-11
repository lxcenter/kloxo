/* Personal stuff. */
$attributes['middlenames'] = array(
    'label' => _("Middle Names"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['namePrefix'] = array(
    'label' => _("Name Prefixes"),
    'type' => 'text',
    'required' => false,
    'params' => array('', 40, 255),
    'params' => array('regex' => '', 'size' => 32, 'maxlength' => 32)
);
$attributes['nameSuffix'] = array(
    'label' => _("Name Suffixes"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 32, 'maxlength' => 32)
);
$attributes['nickname'] = array(
    'label' => _("Nickname"),
    'type' => 'text',
    'required' => false,
    'params' => array('', 40, 255),
    'params' => array('regex' => '', 'size' => 32, 'maxlength' => 32)
);
$attributes['birthday'] = array(
    'label' => _("Birthday"),
    'type' => 'monthdayyear',
    'required' => false,
    'params' => array('start_year' => 1900, 'end_year' => null, 'picker' => true, 'format_in' => '%Y-%m-%d', 'format_out' => '%x'),
    'time_object_label' => _("Birthdays"),
);
$attributes['anniversary'] = array(
    'label' => _("Anniversary"),
    'type' => 'monthdayyear',
    'params' => array('start_year' => 1900, 'end_year' => null, 'picker' => true, 'format_in' => '%Y-%m-%d', 'format_out' => '%x'),
    'required' => false,
    'time_object_label' => _("Anniversaries"),
);
$attributes['spouse'] = array(
    'label' => _("Spouse"),
    'type' => 'text',
    'required' => false,
    'params' => array('', 40, 255),
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['children'] = array(
    'label' => _("Children"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);

/* Locations, addresses. */
$attributes['homePOBox'] = array(
    'label' => _("Home Post Office Box"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 10, 'maxlength' => 10)
);
$attributes['homeCountry'] = array(
    'label' => _("Home Country"),
    'type' => 'country',
    'required' => false,
    'params' => array('prompt' => true)
);
$attributes['workPOBox'] = array(
    'label' => _("Work Post Office Box"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 10, 'maxlength' => 10)
);
$attributes['workCountry'] = array(
    'label' => _("Work Country"),
    'type' => 'country',
    'required' => false,
    'params' => array('prompt' => true)
);
$attributes['timezone'] = array(
    'label' => _("Time Zone"),
    'type' => 'enum',
    'params' => array('values' => $GLOBALS['tz'], 'prompt' => true),
    'required' => false
);

/* Communication. */
$attributes['email'] = array(
    'label' => _("Email"),
    'type' => 'email',
    'required' => false,
    'params' => array('allow_multi' => false, 'strip_domain' => false, 'link_compose' => true)
);
$attributes['emails'] = array(
    'label' => _("Emails"),
    'type' => 'email',
    'required' => false,
    'params' => array('allow_multi' => true, 'strip_domain' => false, 'link_compose' => true)
);
$attributes['pager'] = array(
    'label' => _("Pager"),
    'type' => 'phone',
    'required' => false
);

/* Job, company, organization. */
$attributes['title'] = array(
    'label' => _("Job Title"),
    'type' => 'text',
    'required' => false,
    'params' => array('', 40, 25),
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['role'] = array(
    'label' => _("Occupation"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['department'] = array(
    'label' => _("Department"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['office'] = array(
    'label' => _("Office"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);

/* Other */
require_once 'Horde/Prefs/CategoryManager.php';
require_once 'Horde/Array.php';
$cManager = new Prefs_CategoryManager();
$attributes['category'] = array(
    'label' => _("Category"),
    'type' => 'enum',
    'params' => array(
        'values' => array_merge(array('' => _("Unfiled")), Horde_Array::valuesToKeys($cManager->get())),
        'prompt' => false),
    'required' => false
);

/* Additional attributes supported by Kolab */
$attributes['initials'] = array(
    'label' => _("Initials"),
    'type' => 'text',
    'required' => false,
    'params' => array('', 40, 255),
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['instantMessenger'] = array(
    'label' => _("Instant Messenger"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['manager'] = array(
    'label' => _("Manager"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['assistant'] = array(
    'label' => _("Assistant"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['gender'] = array(
    'label' => _("Gender"),
    'type' => 'enum',
    'required' => false,
    'params' => array('values' => array(_("male"), _("female")), 'prompt' => true),
);
$attributes['language'] = array(
    'label' => _("Language"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['latitude'] = array(
    'label' => _("Latitude"),
    'type' => 'number',
    'required' => false,
);
$attributes['longitude'] = array(
    'label' => _("Longitude"),
    'type' => 'number',
    'required' => false,
);

/* Additional attributes supported by some SyncML clients */
$attributes['workEmail'] = array(
    'label' => _("Work Email"),
    'type' => 'email',
    'required' => false,
    'params' => array('allow_multi' => false, 'strip_domain' => false, 'link_compose' => true)
);
$attributes['homeEmail'] = array(
    'label' => _("Home Email"),
    'type' => 'email',
    'required' => false,
    'params' => array('allow_multi' => false, 'strip_domain' => false, 'link_compose' => true)
);
$attributes['phone'] = array(
    'label' => _("Common Phone"),
    'type' => 'phone',
    'required' => false
);
$attributes['workFax'] = array(
    'label' => _("Work Fax"),
    'type' => 'phone',
    'required' => false
);
$attributes['homeFax'] = array(
    'label' => _("Home Fax"),
    'type' => 'phone',
    'required' => false
);
$attributes['workCellPhone'] = array(
    'label' => _("Work Mobile Phone"),
    'type' => 'cellphone',
    'required' => false
);
$attributes['homeCellPhone'] = array(
    'label' => _("Home Mobile Phone"),
    'type' => 'cellphone',
    'required' => false
);
$attributes['videoCall'] = array(
    'label' => _("Common Video Call"),
    'type' => 'phone',
    'required' => false
);
$attributes['workVideoCall'] = array(
    'label' => _("Work Video Call"),
    'type' => 'phone',
    'required' => false
);
$attributes['homeVideoCall'] = array(
    'label' => _("Home Video Call"),
    'type' => 'phone',
    'required' => false
);
$attributes['voip'] = array(
    'label' => _("VoIP"),
    'type' => 'phone',
    'required' => false
);
$attributes['sip'] = array(
    'label' => _("SIP"),
    'type' => 'email',
    'required' => false,
    'params' => array('allow_multi' => true, 'strip_domain' => false, 'link_compose' => true)
);
$attributes['ptt'] = array(
    'label' => _("PTT"),
    'type' => 'phone',
    'required' => false
);
$attributes['commonExtended'] = array(
    'label' => _("Common Address Extended"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['commonStreet'] = array(
    'label' => _("Common Street"),
    'type' => 'address',
    'required' => false,
    'params' => array('rows' => 3, 'cols' => 40)
);
$attributes['commonPOBox'] = array(
    'label' => _("Common Post Office Box"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 10, 'maxlength' => 10)
);
$attributes['commonCity'] = array(
    'label' => _("Common City"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['commonProvince'] = array(
    'label' => _("Common State/Province"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['commonPostalCode'] = array(
    'label' => _("Common Postal Code"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 10, 'maxlength' => 10)
);
$attributes['commonCountry'] = array(
    'label' => _("Common Country"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['workWebsite'] = array(
    'label' => _("Work Website URL"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['workExtended'] = array(
    'label' => _("Work Address Extended"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['workLatitude'] = array(
    'label' => _("Work Latitude"),
    'type' => 'number',
    'required' => false,
);
$attributes['workLongitude'] = array(
    'label' => _("Work Longitude"),
    'type' => 'number',
    'required' => false,
);
$attributes['homeWebsite'] = array(
    'label' => _("Home Website URL"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['homeExtended'] = array(
    'label' => _("Home Address Extended"),
    'type' => 'text',
    'required' => false,
    'params' => array('regex' => '', 'size' => 40, 'maxlength' => 255)
);
$attributes['homeLatitude'] = array(
    'label' => _("Home Latitude"),
    'type' => 'number',
    'required' => false,
);
$attributes['homeLongitude'] = array(
    'label' => _("Home Longitude"),
    'type' => 'number',
    'required' => false,
);
