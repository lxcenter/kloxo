<?php
/**
 * Represents the incoming request.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */

class Horde_Kolab_Resource_Request
{
    function getICalendar($filename)
    {
        $requestText = '';
        $handle = fopen($filename, 'r');
        while (!feof($handle)) {
            $requestText .= fread($handle, 8192);
        }

        $mime = &MIME_Structure::parseTextMIMEMessage($requestText);

        $parts = $mime->contentTypeMap();
        foreach ($parts as $mimeid => $conttype) {
            if ($conttype == 'text/calendar') {
                $part = $mime->getPart($mimeid);

                $iCalendar = new Horde_iCalendar();
                $iCalendar->parsevCalendar($part->transferDecode());

                return $iCalendar;
            }
        }
        // No iCal found
        return false;
    }
}