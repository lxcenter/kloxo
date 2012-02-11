$mime_drivers_map['horde']['registered'][] = 'audio';
$mime_drivers_map['horde']['registered'][] = 'smil';
// $mime_drivers_map['horde']['registered'][] = 'rtf';
// $mime_drivers_map['horde']['registered'][] = 'wordperfect';

unset($mime_drivers['horde']['default']['icons']['audio/*']);


/**
 * Default audio driver settings
 */
$mime_drivers['horde']['audio'] = array(
    'inline' => true,
    'handles' => array(
        'audio/*'
    ),
    'icons' => array(
        'default' => 'audio.png'
    )
);


/**
 * Default smil driver settings
 */
$mime_drivers['horde']['smil'] = array(
    'inline' => true,
    'handles' => array(
        'application/smil'
    ),
    'icons' => array(
        'default' => 'video.png'
    )
);


// Check for phishing exploits?
$mime_drivers['horde']['html']['phishing_check'] = true;


/**
 * RTF driver settings
 * This driver requires UnRTF to be installed.
 * UnRTF homepage: http://www.gnu.org/software/unrtf/unrtf.html
 */
$mime_drivers['horde']['rtf'] = array(
    'location' => '/usr/bin/unrtf',
    'inline' => false,
    'handles' => array(
        'text/rtf', 'application/rtf'
    ),
    'icons' => array(
        'default' => 'text.png'
    )
);


/**
 * WordPerfect driver settings
 * This driver requires wpd2html to be installed.
 * libwpd homepage: http://libwpd.sourceforge.net/
 */
$mime_drivers['horde']['wordperfect'] = array(
    'location' => '/usr/bin/wpd2html',
    'inline' => false,
    'handles' => array(
        'application/vnd.wordperfect', 'application/wordperf',
        'application/wordperfect', 'application/wpd', 'application/x-wpwin'
    ),
    'icons' => array(
        'default' => 'wordperfect.png'
    )
);
