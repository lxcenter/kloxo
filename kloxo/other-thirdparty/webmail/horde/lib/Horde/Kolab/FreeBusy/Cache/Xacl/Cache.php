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
class Horde_Kolab_FreeBusy_Cache_Xacl_Cache
extends Horde_Kolab_FreeBusy_Cache_Xacl_Base
{
    /**
     * Is extended access to the given partial allowed?
     *
     * @param Horde_Kolab_FreeBusy_User          $user    The user accessing the system.
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial Partial to forget.
     *
     * @return boolean True if extended access is allowed, false otherwise.
     */
    public function allow(
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Cache_Partial $partial
    ) {
        try {
            $groups = $user->getGroupAddresses();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            /**
             * It is kind of unlikely that we hit an error here (if retrieving
             * the groups is problematic then retrieving the user_object in the
             * first place is likely to have been problematic too. If the
             * unthinkable happens though I consider the assumption that the
             * user has no groups a safe alternative as this will only hide
             * information and not disclose anything that might be problematic.
             */
            $groups = array();
        }

        $groups[] = $user->getPrimaryId();
        foreach ($groups as $id) {
            if ($this->_structure
                ->getXaclDbCache()
                ->has($partial->getId(), $id))
                {
                    return true;
                }
        }
        return false;
    }

    /**
     * Purge the extended ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial Partial to forget.
     *
     * @return NULL
     */
    public function delete(Horde_Kolab_FreeBusy_Cache_Partial $partial)
    {
        $filecache = $this->_structure
            ->getXaclFileCache($partial);
        $this->_structure
            ->getXaclDbCache()
            ->store(
                $partial->getId(),
                '',
                $this->_getOldXacl($filecache)
            );
        $filecache->delete();
    }

    /**
     * Store the extended ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial  Partial to store.
     * @param Horde_Kolab_FreeBusy_Resource      $resource Resource handler providing
     *                                                     the extended ACL information.
     * @oaram array                              $acl      The ACL for the partial.
     *
     * @return NULL
     */
    public function store(
        Horde_Kolab_FreeBusy_Cache_Partial $partial,
        Horde_Kolab_FreeBusy_Resource $resource,
        array $acl
    ) {
        $filecache = $this->_structure
            ->getXaclFileCache($partial);

        $xacl = $resource->getAttributeAcl();

        /* Users with read access to the folder may also access the extended information */
        foreach ($acl as $user => $ac) {
            if (strpos($ac, 'r') !== false) {
                if (!empty($user)) {
                    $xacl .= ' ' . $user;
                }
            }
        }

        $this->_structure
            ->getXaclDbCache()
            ->store(
                $partial->getId(),
                $xacl,
                $this->_getOldXacl($filecache)
            );
        $filecache->store($xacl);
    }

    /**
     * Retrieve the old extended ACL settings for this partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_File_Xacl $filecache The cached data.
     *
     * @return string The old extended ACL settings.
     */
    private function _getOldXacl($filecache)
    {
        try {
            return $filecache->load();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            return '';
        }
    }

}