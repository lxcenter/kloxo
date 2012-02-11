<?php
/**
 * Handles partial cached data.
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
 * Handles partial cached data.
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
interface Horde_Kolab_FreeBusy_Cache_Partial
{
    /**
     * Get a partial ID representing a resource.
     *
     * @return string ID of the partial.
     */
    public function getId();

    /**
     * Load the partial data.
     *
     * @return Horde_iCalendar The extended partial free/busy data.
     */
    public function load();

    /**
     * Delete this partial data.
     *
     * @return NULL
     */
    public function delete();

    /**
     * Store partial data.
     *
     * @param mixed $data The data that should be stored.
     *
     * @return NULL
     */
    public function store($data);

    /**
     * Return the last modification date of the cache file.
     *
     * @return int The last modification date.
     */
    public function getMtime();
}