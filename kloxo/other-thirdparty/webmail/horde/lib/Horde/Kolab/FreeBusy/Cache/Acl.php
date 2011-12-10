<?php
/**
 * Free/busy access control for free/busy exports.
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
 * Free/busy access control for free/busy exports.
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
interface Horde_Kolab_FreeBusy_Cache_Acl
{
    /**
     * Which partials need to be merged into the combined information for one
     * owner?
     *
     * @param Horde_Kolab_FreeBusy_Owner $owner The owner of the partials.
     *
     * @return array The list of partials to be combined.
     */
    public function getPartialIds(Horde_Kolab_FreeBusy_Owner $owner);

    /**
     * Purge the ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial Partial to forget.
     *
     * @return NULL
     */
    public function delete(Horde_Kolab_FreeBusy_Cache_Partial $partial);

    /**
     * Store the ACL information for a partial.
     *
     * @param Horde_Kolab_FreeBusy_User          $user     The user accessing the system.
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial  Partial to forget.
     * @param Horde_Kolab_FreeBusy_Resource      $resource Resource handler providing
     *                                                     ACL information.
     *
     * @return NULL
     */
    public function store(
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Cache_Partial $partial,
        Horde_Kolab_FreeBusy_Resource $resource
    );
}