<?php
/**
 * A wrapper for the free/busy export that is specific to cope with
 * the situation in Horde framework 3.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * A wrapper for the free/busy export that is specific to cope with
 * the situation in Horde framework 3.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If
 * you did not receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Export_Freebusy_Fwthree
extends Horde_Kolab_FreeBusy_Export_Freebusy_Base
{
    /**
     * The timestamp for the generation of this export.
     *
     * @var string
     */
    private $_date_stamp;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Export_Freebusy_Backend $backend    The export backend.
     * @param Horde_Kolab_FreeBusy_Resource                $resource   The resource to export.
     * @param string                                       $date_stamp The timestamp of the export.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Export_Freebusy_Backend $backend,
        Horde_Kolab_FreeBusy_Resource $resource,
        $date_stamp
    ) {
        $this->_date_stamp = $date_stamp;
        parent::__construct($backend, $resource);
    }

    /**
     * Return the timestamp for the export.
     *
     * @return string The timestamp.
     */
    public function getDateStamp()
    {
        return $this->_date_stamp;
    }
}
