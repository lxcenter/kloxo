<?php
/**
 * Free/busy access control based on cached ACL information.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * Free/busy access control based on cached ACL information.
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Export_Freebusy_Acl_Cache
implements Horde_Kolab_FreeBusy_Export_Freebusy_Acl
{
    /**
     * Free/Busy access control object.
     *
     * @var Horde_Kolab_FreeBusy_Access
     */
    private $_access;

    /**
     * The db based cache for free/busy ACLs
     *
     * @var Horde_Kolab_FreeBusy_Cache_DB_acl
     */
    private $_dbcache;

    /**
     * The file based cache for free/busy ACLs
     *
     * @var Horde_Kolab_FreeBusy_Cache_File_acl
     */
    private $_filecache;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Access         $access    Free/Busy access control.
     * @param Horde_Kolab_FreeBusy_Cache_DB_acl   $dbcache   DB based ACL cache.
     * @param Horde_Kolab_FreeBusy_Cache_File_acl $filecache File based ACL cache.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Access $access,
        Horde_Kolab_FreeBusy_Cache_DB_acl $dbcache,
        Horde_Kolab_FreeBusy_Cache_File_acl $filecache
    ) {
        $this->_access    = $access;
        $this->_dbcache   = $dbcache;
        $this->_filecache = $filecache;
    }

    /**
     * Which partials need to be combined into the final vCalendar information?
     *
     * @return array|PEAR_Error The list of files to be combined.
     */
    public function getFiles()
    {
        return $this->_dbcache->get($this->_access->owner);
    }

    /**
     * Purge the ACL information.
     *
     * @param string $file Name of the cached ACL information.
     *
     * @return boolean|PEAR_Error True if purging worked.
     */
    public function purge($file)
    {
        return $this->_filecache->purge($file);
    }

    public function store($file, $fb)
    {
        $relevance = $fb->getRelevance();
        if (is_a($relevance, 'PEAR_Error')) {
            return $relevance;
        }

        $acl = $fb->getACL();
        if (is_a($acl, 'PEAR_Error')) {
            return $acl;
        }

        /** 
         * Only store the acl information by overwriting if the current user has
         * admin rights on the folder and can actually retrieve the full ACL
         * information. Otherwise the ACL should only be appended.
         */
        $append = false;
        if (!isset($acl[$this->_access->user])
            || (strpos($acl[$this->_access->user], 'a') === false)) {
            $append = true;
        }

        $result = $this->_filecache->storeACL($file, $acl, $relevance, $append);
        if (is_a($result, 'PEAR_Error')) {
            return $result;
        }
        return $acl;
    }
}