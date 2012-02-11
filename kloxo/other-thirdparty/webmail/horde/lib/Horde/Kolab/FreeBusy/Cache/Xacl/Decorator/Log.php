<?php
/**
 * Logs extended free/busy access control.
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
 * Logs extended free/busy access control.
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
class Horde_Kolab_FreeBusy_Cache_Xacl_Decorator_Log
implements Horde_Kolab_FreeBusy_Cache_Xacl
{
    /**
     * The decorated extended ACL handler.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Xacl
     */
    private $_xacl;

    /**
     * The logger.
     *
     * @var Horde_Kolab_FreeBusy_Logger
     */
    private $_logger;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Xacl $xacl   The decorated instance.
     * @param Horde_Kolab_FreeBusy_Logger     $logger The logger.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Cache_Xacl $xacl,
        Horde_Kolab_FreeBusy_Logger $logger
    ) {
        $this->_xacl = $xacl;
        $this->_logger  = $logger;
    }

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
        $result = $this->_xacl->allow($user, $partial);
        $this->_logger->debug(
            sprintf(
                "Extended attributes on file %s %s for user \"%s\".",
                $partial->getId(),
                $result ? 'allowed' : 'disallowed',
                $user->getPrimaryId()
            )
        );
        return $result;
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
        $result = $this->_xacl->delete($partial);
        $this->_logger->debug(
            sprintf(
                "Deleted extended attribute cache for file %s.",
                $partial->getId()
            )
        );
        return $result;
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
        //@todo: Log the stored values on the side of the cache.
        $result = $this->_xacl->store($partial, $resource, $acl);
        $this->_logger->debug(
            sprintf(
                "Stored extended attributes cache for file %s.",
                $partial->getId()
            )
        );
        return $result;
    }
}