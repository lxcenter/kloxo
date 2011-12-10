<?php
/**
 * Extended free/busy access control for free/busy exports.
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
 * Extended free/busy access control for free/busy exports.
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
interface Horde_Kolab_FreeBusy_Cache_Xacl
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
    );

    /**
     * Purge the extended ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial Partial to forget.
     *
     * @return NULL
     */
    public function delete(Horde_Kolab_FreeBusy_Cache_Partial $partial);

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
    );
}