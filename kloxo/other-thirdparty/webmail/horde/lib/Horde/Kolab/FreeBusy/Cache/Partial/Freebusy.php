<?php
/**
 * Handles a cached partial free/busy list.
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
 * Handles a cached partial free/busy list.
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
class Horde_Kolab_FreeBusy_Cache_Partial_Freebusy
implements Horde_Kolab_FreeBusy_Cache_Partial
{
    /**
     * The ID of the partial.
     *
     * @var string
     */
    private $_id;

    /**
     * The cache handler for the partial vCalendar file.
     *
     * @var Horde_Kolab_FreeBusy_Cache_File_Partial_Freebusy
     */
    private $_partial_cache;

    /**
     * Constructor.
     *
     * @param string                                           $id            The ID of the partial.
     * @param Horde_Kolab_FreeBusy_Cache_File_Partial_Freebusy $partial_cache The actual cache file storing the data.
     */
    public function __construct(
        $id,
        Horde_Kolab_FreeBusy_Cache_File_Pvcal $partial_cache
    ) {
        $this->_id = $id;
        $this->_partial_cache = $partial_cache;
        $this->_partial_cache->setPartial($this);
    }

    /**
     * Get a partial ID representing a resource.
     *
     * @return string ID of the partial.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Load the extended partial free/busy data.
     *
     * @return Horde_iCalendar The extended partial free/busy data.
     */
    public function load()
    {
        return $this->_partial_cache->load();
    }

    /**
     * Load the simple partial free/busy data.
     *
     * @return Horde_iCalendar The reduced partial free/busy data.
     */
    public function loadSimple()
    {
        $pvcal = $this->_partial_cache->load();
        if ($pvcal instanceOf Horde_iCalendar) {
            $components = $pvcal->getComponents();
            foreach ($components as $component) {
                if ($component->getType() == 'vFreebusy') {
                    $component->_extraParams = array();
                }
            }
        }
        return $pvcal;
    }

    /**
     * Delete this partial free/busy data.
     *
     * @return NULL
     */
    public function delete()
    {
        $this->_partial_cache->delete();
    }

    /**
     * Store partial free/busy data.
     *
     * @param mixed $data The data that should be stored.
     *
     * @return NULL
     */
    public function store($data)
    {
        $this->_partial_cache->store($data);
    }

    /**
     * Return the last modification date of the cache file.
     *
     * @return int The last modification date.
     */
    public function getMtime()
    {
        $this->_partial_cache->getMtime();
    }
}