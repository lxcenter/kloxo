<?php
/**
 * Logs exporting free/busy data.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * Logs exporting free/busy data.
 *
 * Copyright 2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If
 * you did not receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Export_Freebusy_Decorator_Log
implements Horde_Kolab_FreeBusy_Export_Freebusy
{
    /**
     * The decorated exporter.
     *
     * @var Horde_Kolab_FreeBusy_Export_Freebusy_Interface
     */
    private $_export;

    /**
     * The logger.
     *
     * @var mixed
     */
    private $_logger;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Export_Freebusy_Interface $export The decorated
     *                                                               export.
     * @param mixed                                          $logger The log handler. The
     *                                                               class must at least
     *                                                               provide the debug()
     *                                                               method.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Export_Freebusy_Base $export,
        $logger
    ) {
        $this->_export = $export;
        $this->_logger = $logger;
    }

    /**
     * Get the start timestamp for the export.
     *
     * @return Horde_Date The start timestamp for the export.
     */
    public function getStart()
    {
        return $this->_export->getStart();
    }

    /**
     * Get the end timestamp for the export.
     *
     * @return Horde_Date The end timestamp for the export.
     */
    public function getEnd()
    {
        return $this->_export->getEnd();
    }

    /**
     * Get the name of the resource.
     *
     * @return string The name of the resource.
     */
    public function getResourceName()
    {
        return $this->_export->getResourceName();
    }

    /**
     * Return the organizer mail for the export.
     *
     * @return string The organizer mail.
     */
    public function getOrganizerMail()
    {
        return $this->_export->getOrganizerMail();
    }

    /**
     * Return the organizer name for the export.
     *
     * @return string The organizer name.
     */
    public function getOrganizerName()
    {
        return $this->_export->getOrganizerName();
    }

    /**
     * Return the timestamp for the export.
     *
     * @return string The timestamp.
     */
    public function getDateStamp()
    {
        return $this->_export->getDateStamp();
    }

    /**
     * Generates the free/busy export.
     *
     * @return Horde_iCalendar  The iCal object.
     */
    public function export()
    {
        $this->_logger->debug(
            sprintf('Exporting free/busy data for resource %s from %s to %s',
                    $this->_export->getResourceName(),
                    $this->_export->getStart()->timestamp(),
                    $this->_export->getEnd()->timestamp()
            )
        );

        return $this->_export->export();
    }

}