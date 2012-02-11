$mime_drivers_map['imp']['registered'][] = 'pdf';

/**
 * PDF settings
 */
$mime_drivers['imp']['pdf'] = array(
    'inline' => false,
    'handles' => array(
        'application/pdf', 'image/pdf'
    ),
    'icons' => array(
        'default' => 'pdf.png'
    )
);

$mime_drivers['imp']['itip']['handles'] = array('text/calendar', 'text/x-vcalendar');
