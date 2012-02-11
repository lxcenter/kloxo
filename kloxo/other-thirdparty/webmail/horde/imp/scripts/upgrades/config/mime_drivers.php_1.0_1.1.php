$mime_drivers_map['imp']['registered'][] = 'smil';

/* If you want to limit the display of message data inline for large messages,
 * set the maximum size of the displayed message here (in bytes).  If
 * exceeded, the user will only be able to download the part.  Set to 0 to
 * disable this check. */
$mime_drivers['imp']['plain']['limit_inline_size'] = 104857;

/* If you don't want to display the link to open the HTML content in a
 * separate window, set the following to false. */
$mime_drivers['imp']['html']['external'] = true;

/* Run 'tidy' on all HTML output? This requires at least version 2.0 of the
 * PECL 'tidy' extension to be installed on your system. */
$mime_drivers['imp']['html']['tidy'] = false;

/* Check for phishing exploits? */
$mime_drivers['imp']['html']['phishing_check'] = true;

/**
 * Default smil driver settings
 */
$mime_drivers['imp']['smil'] = array(
    'inline' => true,
    'handles' => array(
        'application/smil'
    )
);

/* Display thumbnails for all images, not just large images? */
$mime_drivers['imp']['images']['allthumbs'] = true;
