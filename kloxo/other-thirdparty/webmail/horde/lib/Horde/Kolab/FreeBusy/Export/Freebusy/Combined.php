<?php
/**
 * Combines several partial free/busy lists into the free/busy list for a user.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/** We require the iCalendar library to build the free/busy list */
require_once 'Horde/iCalendar.php';
require_once 'Horde/iCalendar/vfreebusy.php';

/**
 * Combines several partial free/busy lists into the free/busy list for a user.
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Export_Freebusy_Combined
{
    /**
     * Owner of the data being accessed.
     *
     * @var Horde_Kolab_FreeBusy_Owner
     */
    private $_owner;

    /**
     * The partial free/busy lists.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Combined_Freebusy
     */
    private $_combined;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Owner                   $owner    Owner of the accessed data.
     * @param Horde_Kolab_FreeBusy_Cache_Freebusy_Partials $partials Partial free/busy information handler.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Owner $owner,
        Horde_Kolab_FreeBusy_Cache_Combined_Freebusy $combined
    ) {
        $this->_owner    = $owner;
        $this->_combined = $combined;
    }

    public function getSignature($extended)
    {
        return md5(
            join(':', $this->getOwnerCnParameter()) . '|' .
            join(':', $this->_combined->getPartialIds()) . '|' .
            join(':', $this->_combined->getExtendedAccess($extended))
        );
    }

    public function getOwnerCnParameter()
    {
        $cn_parameter = array();
        $cn = $this->_owner->getName();
        if (!empty($cn) && !is_a($cn, 'PEAR_Error')) {
            $cn_parameter['cn'] = $cn;
        };
        return $cn_parameter;
    }

    public function getUrlAttribute()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } else {
            $host = 'localhost';
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = '/';
        }
        return 'http://' . $host . $uri;
    }

    public function hasRemoteServers()
    {
        global $conf;

        return !empty($conf['fb']['remote_servers']) ||
            !empty($conf['fb']['exchange_server']);
    }

    public function generate($extended = false)
    {
        global $conf;

        try {
        // Create the new iCalendar.
        $vCal = new Horde_iCalendar();
        $vCal->setAttribute('PRODID', '-//kolab.org//NONSGML Kolab Server 2//EN');
        $vCal->setAttribute('METHOD', 'PUBLISH');

        // Create new vFreebusy.
        $vFb = &Horde_iCalendar::newComponent('vfreebusy', $vCal);

        $vFb->setAttribute(
            'ORGANIZER',
            'MAILTO:' . $this->_owner->getPrimaryId(),
            $this->getOwnerCnParameter()
        );

        $vFb->setAttribute('DTSTAMP', time());
        $vFb->setAttribute('URL', $this->getUrlAttribute());

        $mtimes = $this->_combined->combineResult($vFb, $extended);
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            if (!$this->hasRemoteServers()) {
                throw $e;
            }
        }

        if (!empty($conf['fb']['remote_servers'])) {
            $remote_vfb = $this->_fetchRemote($conf['fb']['remote_servers'],
                                              $this->_access);
            if (is_a($remote_vfb, 'PEAR_Error')) {
                Horde::logMessage(sprintf("Ignoring remote free/busy files: %s)",
                                          $remote_vfb->getMessage()),
                                  __FILE__, __LINE__, PEAR_LOG_INFO);
            } else {
                $vFb->merge($remote_vfb);
            }
        }

        if (!empty($conf['fb']['exchange_server'])) {
            $vFb->merge(
                $this->_fetchExchange($conf['fb']['exchange_server'])
            );
        }

        if (!(boolean)$vFb->getBusyPeriods()) {
            /* No busy periods in fb list. We have to add a
             * dummy one to be standards compliant
             */
            $vFb->setAttribute('COMMENT', 'This is a dummy vfreebusy that indicates an empty calendar');
            $vFb->addBusyPeriod('BUSY', 0, 0, null);
        }

        $vCal->addComponent($vFb);

        $result = array($vCal, $mtimes);
        return $result;
    }

    /**
     * Retrieve external free/busy data.
     *
     * @param array                 $servers  The remote servers to query
     * @param Horde_Kolab_FreeBusy_Access $access   The object holding the
     *                                        relevant access
     *                                        parameters.
     *
     * @return Horde_iCalender The remote free/busy information.
     *
     * @todo Fixme and extract to class. Combine with the other "fetchRemote"
     */
    function &_fetchRemote($servers, $access)
    {
        $vFb = null;

        foreach ($servers as $server) {

            $url = 'https://' . urlencode($access->user) . ':' . urlencode($access->pass)
            . '@' . $server . $_SERVER['REQUEST_URI'];
            $remote = @file_get_contents($url);
            if (!$remote) {
                $message = sprintf("Unable to read free/busy information from %s",
                                   'https://' . urlencode($access->user) . ':XXX'
                                   . '@' . $server . $_SERVER['REQUEST_URI']);
                Horde::logMessage($message, __FILE__, __LINE__, PEAR_LOG_INFO);
            }

            $rvCal = new Horde_iCalendar();
            $result = $rvCal->parsevCalendar($remote);

            if (is_a($result, 'PEAR_Error')) {
                $message = sprintf("Unable to parse free/busy information from %s: %s",
                                   'https://' . urlencode($access->user) . ':XXX'
                                   . '@' . $server . $_SERVER['REQUEST_URI'],
                                   $result->getMessage());
                Horde::logMessage($message, __FILE__, __LINE__, PEAR_LOG_INFO);
                return $result;
            }

            $rvFb = &$rvCal->findComponent('vfreebusy');
            if (!$pvFb) {
                $message = sprintf("Unable to find free/busy information in data from %s.",
                                   'https://' . urlencode($access->user) . ':XXX'
                                   . '@' . $server . $_SERVER['REQUEST_URI']);
                Horde::logMessage($message, __FILE__, __LINE__, PEAR_LOG_INFO);
            }
            if ($ets = $rvFb->getAttributeDefault('DTEND', false) !== false) {
                // PENDING(steffen): Make value configurable
                if ($ets < time()) {
                    $message = sprintf("free/busy information from %s is too old.",
                                       'https://' . urlencode($access->user) . ':XXX'
                                       . '@' . $server . $_SERVER['REQUEST_URI']);
                    Horde::logMessage($message, __FILE__, __LINE__, PEAR_LOG_INFO);
                }
            }
            if (!empty($vFb)) {
                $vFb->merge($rvFb);
            } else {
                $vFb = $rvFb;
            }
        }
        return $vFb;
    }

    /**
     * Retrieve external free/busy data from an exchange server.
     *
     * @param array $server The remote exchange server to query
     *
     * @return Horde_iCalender The remote free/busy information.
     */
    public function _fetchExchange($server)
    {
        global $conf;

        require_once 'Horde/Http/Client.php';
        require_once 'Horde/Http/Client/Exception.php';
        require_once 'Horde/Http/Client/Response.php';
        require_once 'Horde/Date.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Backend.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Backend/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Base.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Resource.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Decorator/Mcache.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Owa.php';
        require_once 'Horde/Kolab/FreeBusy/Object/Event.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/Owa.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap/Default.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap/Config.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Decorator/Mcache.php';
;
        require_once 'Horde/Kolab/FreeBusy/Logger.php';

        $resource = new Horde_Kolab_FreeBusy_Resource_Event_Decorator_Log(
            new Horde_Kolab_FreeBusy_Resource_Event_Decorator_Mcache(
                new Horde_Kolab_FreeBusy_Resource_Event_Owa(
                    $this->_owner, $server
                )
            ),
            new Horde_Kolab_FreeBusy_Logger()
        );


        $params = array(
            'request_time' => time()
        );
        if (isset($conf['fb']['future_days'])) {
            $params['future_days'] = $conf['fb']['future_days'];
        }
        if (!empty($conf['fb']['status_map'])) {
            $params['status_map'] = new Horde_Kolab_FreeBusy_Helper_FreeBusy_StatusMap_Config(
                $conf['fb']['status_map']
            );
        }

        $export = new Horde_Kolab_FreeBusy_Export_Freebusy_Decorator_Log(
            new Horde_Kolab_FreeBusy_Export_Freebusy_Base(
                new Horde_Kolab_FreeBusy_Export_Freebusy_Backend_Kolab(),
                $resource,
                $params
            ),
            new Horde_Kolab_FreeBusy_Logger()
        );

        $ical = $export->export();
        return $ical->findComponent('vfreebusy');
    }
}