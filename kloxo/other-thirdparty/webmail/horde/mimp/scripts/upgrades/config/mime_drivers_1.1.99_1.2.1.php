$mime_drivers_map['mimp']['registered'][] = 'status';
$mime_drivers['mimp']['plain']['handles'] = array('text/plain', 'text/rfc822-headers');

/**
 * Delivery Status messages settings
 */
$mime_drivers['mimp']['status'] = array(
    'inline' => true,
    'handles' => array(
        'message/delivery-status'
    )
);
