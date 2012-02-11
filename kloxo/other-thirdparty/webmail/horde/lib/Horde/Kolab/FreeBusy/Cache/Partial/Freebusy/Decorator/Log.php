<?php
/**
 * Logs access to a cached partial free/busy list.
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
 * Logs access to a cached partial free/busy list.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache_Partial_Freebusy_Decorator_Log
implements Horde_Kolab_FreeBusy_Cache_Partial
{
    /**
     * The observed partial.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Partial_Freebusy
     */
    private $_partial;

    /**
     * The logger.
     *
     * @var Horde_Kolab_FreeBusy_Logger
     */
    private $_logger;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial_Freebusy $partial The partial to decorate.
     * @param Horde_Kolab_FreeBusy_Logger                 $logger  The logger.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Cache_Partial_Freebusy $partial,
        Horde_Kolab_FreeBusy_Logger $logger
    ) {
        $this->_partial = $partial;
        $this->_logger  = $logger;
    }

    /**
     * Get a partial ID representing a resource.
     *
     * @return string ID of the partial.
     */
    function getId()
    {
        return $this->_partial->getId();
    }

    /**
     * Load the partial data.
     *
     * @return Horde_iCalendar The extended partial free/busy data.
     */
    public function load()
    {
        try {
            $result = $this->_partial->load();
            $this->_logger->debug(
                sprintf(
                    "Loaded partial cache file from %s.",
                    $this->_partial->getId()
                )
            );
            return $result;
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $this->_logger->error(
                sprintf(
                    "Failed loading partial cache file %s: %s)",
                    $this->_partial->getId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }
    }

    /**
     * Load the simple partial free/busy data.
     *
     * @return Horde_iCalendar The reduced partial free/busy data.
     */
    public function loadSimple()
    {
        try {
            $result = $this->_partial->loadSimple();
            $this->_logger->debug(
                sprintf(
                    "Loaded simple partial cache file from %s.",
                    $this->_partial->getId()
                )
            );
            return $result;
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $this->_logger->error(
                sprintf(
                    "Failed loading simple partial cache file %s: %s)",
                    $this->_partial->getId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }
    }

    /**
     * Delete this partial data.
     *
     * @return NULL
     */
    public function delete()
    {
        try {
            $this->_partial->delete();
            $this->_logger->debug(
                sprintf(
                    "No events. Purging partial cache %s.",
                    $this->_partial->getId()
                )
            );
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $this->_logger->error(
                sprintf(
                    "Failed purging partial cache file %s: %s)",
                    $this->_partial->getId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }
    }
    
    /**
     * Store partial data.
     *
     * @param Horde_iCalendar $vCal The free/busy data that should be stored.
     *
     * @return NULL
     */
    public function store($data)
    {
        try {
            $this->_partial->store($data);
            $this->_logger->debug(
                sprintf(
                    "Stored partial cache file %s.",
                    $this->_partial->getId()
                )
            );
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $this->_logger->error(
                sprintf(
                    "Failed storing partial cache file %s: %s)",
                    $this->_partial->getId(),
                    $e->getMessage()
                )
            );
            throw $e;
        }
    }

    /**
     * Return the last modification date of the cache file.
     *
     * @return int The last modification date.
     */
    public function getMtime()
    {
        return $this->_partial->getMtime();
    }
}