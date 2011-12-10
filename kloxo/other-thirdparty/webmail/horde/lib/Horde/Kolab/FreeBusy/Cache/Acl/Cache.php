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
class Horde_Kolab_FreeBusy_Cache_Acl_Cache
extends Horde_Kolab_FreeBusy_Cache_Acl_Base
{
    /**
     * Which partials need to be merged into the combined information for one
     * owner?
     *
     * @param Horde_Kolab_FreeBusy_Owner $owner The owner of the partials.
     *
     * @return array The list of partials to be combined.
     */
    public function getPartialIds(Horde_Kolab_FreeBusy_Owner $owner)
    {
        return $this->_structure
            ->getAclDbCache()
            ->get($owner->getPrimaryId());
    }

    /**
     * Purge the ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial Partial to forget.
     *
     * @return NULL
     */
    public function delete(Horde_Kolab_FreeBusy_Cache_Partial $partial)
    {
        $filecache = $this->_structure
            ->getAclFileCache($partial);
        $this->_structure
            ->getAclDbCache()
            ->store(
                $partial->getId(),
                array(),
                $this->_getOldAcl($filecache),
                false
            );
        $filecache->delete();
    }

    /**
     * Store the ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_User          $user     The user accessing the system.
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial  Partial to store.
     * @param Horde_Kolab_FreeBusy_Resource      $resource Resource handler providing
     *                                                     ACL information.
     *
     * @return array The current ACLs.
     */
    public function store(
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Cache_Partial $partial,
        Horde_Kolab_FreeBusy_Resource $resource
    ) {
        $acl = $resource->getAcl();

        $filecache = $this->_structure
            ->getAclFileCache($partial);

        /** 
         * Only store the acl information by overwriting if the current user has
         * admin rights on the folder and can actually retrieve the full ACL
         * information. Otherwise the ACL should only be appended.
         */
        if (!isset($acl[$user->getPrimaryId()])
            || (strpos($acl[$user->getPrimaryId()], 'a') === false)) {
            $oldacl = array();
        } else {
            $oldacl = $this->_getOldAcl($filecache);
        }

        /* Handle relevance */
        switch ($resource->getRelevance()) {
        case 'readers':
            $perm = 'r';
            break;
        case 'nobody':
            $perm = false;
            break;
        case 'admins':
        default:
            $perm = 'a';
        }

        $this->_structure
            ->getAclDbCache()
            ->store(
                $partial->getId(),
                $acl,
                $oldacl,
                $perm
            );

        $filecache->store($acl);
        return $acl;
    }

    /**
     * Retrieve the old ACL settings for this partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_File_Acl $filecache The cached data.
     *
     * @return array The old ACL settings.
     */
    private function _getOldAcl($filecache)
    {
        try {
            return $filecache->load();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            return array();
        }
    }
}