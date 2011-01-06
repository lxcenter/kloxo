<?php
/**
 * Converts the data from the free/busy resource into a free/busy iCal object,
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
 * Converts the data from the free/busy resource into a free/busy iCal object,
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 * Copyright 2008-2009 The Horde Project (http://www.horde.org/)
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
class Horde_Kolab_FreeBusy_Export_Freebusy_Base
implements Horde_Kolab_FreeBusy_Export_Freebusy
{
    /**
     * The resource to export.
     *
     * @var Horde_Kolab_FreeBusy_Resource
     */
    private $_resource;

    /**
     * The backend definition.
     *
     * @var Horde_Kolab_FreeBusy_Export_Freebusy_Backend
     */
    private $_backend;

    /**
     * The request.
     *
     * @var Horde_Controller_Request_Base
     */
    private $_request;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Export_Freebusy_Backend $backend  The export backend.
     * @param Horde_Kolab_FreeBusy_Resource                $resource The resource to export.
     * @param Horde_Controller_Request_Base                $request  The incoming request.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Export_Freebusy_Backend $backend,
        Horde_Kolab_FreeBusy_Resource $resource,
        //@todo: revert
        //        Horde_Controller_Request_Base $request
        Horde_Kolab_FreeBusy_Request $request
    ) {
        $this->_resource = $resource;
        $this->_backend = $backend;
        $this->_request = $request;
    }

    private function _today()
    {
        return new Horde_Date(
            array(
                'year' => date('Y'), 'month' => date('n'), 'mday' => date('j')
            )
        );
    }
    
    /**
     * Get the start timestamp for the export.
     *
     * @return Horde_Date The start timestamp for the export.
     */
    public function getStart()
    {
        try {
            $past = $this->_resource->getOwner()->getFreeBusyPast();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $past = 0;
        }
        $start = $this->_today();
        $start->mday = $start->mday - $past;
        return $start;
    }

    /**
     * Get the end timestamp for the export.
     *
     * @return Horde_Date The end timestamp for the export.
     */
    public function getEnd()
    {
        try {
            $future = $this->_resource->getOwner()->getFreeBusyFuture();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
        }
        if (empty($future)) {
            global $conf;
            if (isset($conf['fb']['future_days'])) {
                $future = $conf['fb']['future_days'];
            } else {
                $future = 60;
            }
        }
        $end = $this->_today();
        $end->mday = $end->mday + $future;
        return $end;
    }

    /**
     * Get the name of the resource.
     *
     * @return string The name of the resource.
     */
    public function getResourceName()
    {
        return $this->_resource->getName();
    }

    /**
     * Return the organizer mail for the export.
     *
     * @return string The organizer mail.
     */
    public function getOrganizerMail()
    {
        return 'MAILTO:' . $this->_resource->getOwner()->getMail();
    }

    /**
     * Return the organizer name for the export.
     *
     * @return string The organizer name.
     */
    public function getOrganizerName()
    {
        $params = array();
        $name = $this->_resource->getOwner()->getName();
        if (!empty($name)) {
            $params['cn'] = $name;
        }
        return $params;
    }

    /**
     * Return the timestamp for the export.
     *
     * @return string The timestamp.
     */
    public function getDateStamp()
    {
        return $this->_request->getServer('REQUEST_TIME');
    }

    /**
     * Generates the free/busy export.
     *
     * @return Horde_iCalendar  The iCal object.
     */
    public function export()
    {
        /* Create the new iCalendar. */
        $vCal = new Horde_iCalendar();
        $vCal->setAttribute('PRODID', $this->_backend->getProductId());
        $vCal->setAttribute('METHOD', 'PUBLISH');

        /* Create the new vFreebusy component. */
        $vFb = &Horde_iCalendar::newComponent('vfreebusy', $vCal);

        $vFb->setAttribute(
            'ORGANIZER', $this->getOrganizerMail(), $this->getOrganizerName()
        );
        $vFb->setAttribute('DTSTAMP', $this->getDateStamp());
        $vFb->setAttribute('DTSTART', $this->getStart()->timestamp());
        $vFb->setAttribute('DTEND', $this->getEnd()->timestamp());
        $url = $this->_backend->getUrl();
        if (!empty($url)) {
            $vFb->setAttribute('URL', $this->getUrl());
        }

        /* Add all the busy periods. */
        foreach (
            $this->_resource->listEvents($this->getStart(), $this->getEnd())
            as $event
        ) {
            foreach (
                $event->getBusyTimes($this->getStart(), $this->getEnd())
                as $busy
            ) {
                $vFb->addBusyPeriod(
                    'BUSY',
                    $busy,
                    null,
                    $event->duration(),
                    $event->getEncodedInformation()
                );
            }
        }

        /* Remove the overlaps. */
        $vFb->simplify();

        /* Combine and return. */
        $vCal->addComponent($vFb);
        return $vCal;
    }
}
