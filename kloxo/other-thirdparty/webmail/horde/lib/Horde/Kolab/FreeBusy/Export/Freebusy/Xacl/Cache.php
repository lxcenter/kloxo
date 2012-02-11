<?php
/**
 * Extended free/busy access control based on cached ACL information.
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
 * Extended free/busy access control based on cached ACL information.
 *
 * Copyright 2008-2010 Klarälvdalens Datakonsult AB
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
class Horde_Kolab_FreeBusy_Export_Freebusy_Xacl_Cache
implements Horde_Kolab_FreeBusy_Export_Freebusy_Xacl
{
    /**
     * Free/Busy access control object.
     *
     * @var Horde_Kolab_FreeBusy_Access
     */
    private $_access;

    /**
     * The db based cache for extended free/busy ACLs
     *
     * @var Horde_Kolab_FreeBusy_Cache_DB_xacl
     */
    private $_dbcache;

    /**
     * The file based cache for extended free/busy ACLs
     *
     * @var Horde_Kolab_FreeBusy_Cache_DB_xacl
     */
    private $_filecache;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Access          $access    Free/Busy access control.
     * @param Horde_Kolab_FreeBusy_Cache_DB_xacl   $dbcache   Db based extended ACL cache.
     * @param Horde_Kolab_FreeBusy_Cache_File_xacl $filecache File based extended ACL cache.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Access $access,
        Horde_Kolab_FreeBusy_Cache_DB_xacl $dbcache,
        Horde_Kolab_FreeBusy_Cache_File_xacl $filecache
    ) {
        $this->_access    = $access;
        $this->_dbcache   = $dbcache;
        $this->_filecache = $filecache;
    }

    /**
     * Is extended access to the given file allowed?
     *
     * @param string $file Name of the cached partial free/busy information.
     *
     * @return boolean|PEAR_Error True if extended access is allowed.
     */
    public function allow($file)
    {
        $groups = array();
        if (!empty($this->_access->user_object)) {
            /* Check if the calling user has access to the extended information of
             * the folder we are about to integrate into the free/busy data.
             */
            $result = $this->_access->user_object->getGroupAddresses();
            if (!is_a($result, 'PEAR_Error')) {
                /**
                 * It is kind of unlikely that we hit an error here (if retrieving
                 * the groups is problematic then retrieving the user_object in the
                 * first place is likely to have been problematic too. If the
                 * unthinkable happens though I consider the assumption that the
                 * user has no groups a safe alternative as this will only hide
                 * information and not disclose anything that might be problematic.
                 */
                $groups = $result;
            }
        }

        $groups[] = $this->_access->user;
        foreach ($groups as $id) {
            if ($this->_dbcache->has($file, $id) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the ID of the user for whom extended free/busy access is being checked.
     *
     * @return string The user ID.
     */
    public function getUserId()
    {
        if (!isset($this->_access->user_object)) {
            return 'anonymous';
        }
        return $this->_access->user;
    }

    /**
     * Purge the extended ACL information.
     *
     * @param string $file Name of the cached extended ACL information.
     *
     * @return boolean|PEAR_Error True if purging worked.
     */
    public function purge($file)
    {
        return $this->_filecache->purge($file);
    }

    public function store($file, $fb, $acl)
    {
        $xacl = $fb->getExtendedACL();
        if (is_a($xacl, 'PEAR_Error')) {
            return $xacl;
        }

        return $this->_filecache->storeXACL($file, $xacl, $acl);
    }
}