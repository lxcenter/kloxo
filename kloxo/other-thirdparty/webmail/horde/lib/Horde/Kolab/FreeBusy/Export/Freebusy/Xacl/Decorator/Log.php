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
class Horde_Kolab_FreeBusy_Export_Freebusy_Xacl_Decorator_Log
implements Horde_Kolab_FreeBusy_Export_Freebusy_Xacl
{
    /**
     * The decorated extended ACL handler.
     *
     * @var Horde_Kolab_FreeBusy_Export_Freebusy_Xacl
     */
    private $_xacl;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Export_Freebusy_Xacl $xacl The decorated instance.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Export_Freebusy_Xacl $xacl
    ) {
        $this->_xacl = $xacl;
    }

    /**
     * Is extended access to the given file allowed?
     *
     * @param string $file Name of the cached partial free/busy information.
     *
     * @return boolean True if extended access is allowed.
     */
    public function allow($file)
    {
        $result = $this->_xacl->allow($file);
        Horde::logMessage(
            sprintf(
                "Extended attributes on file %s %s for user \"%s\".",
                $file,
                $result ? 'allowed' : 'disallowed',
                $this->_xacl->getUserId()
            ),
            __FILE__, __LINE__, PEAR_LOG_DEBUG
        );
        return $result;
    }

    /**
     * Return the ID of the user for whom extended free/busy access is being checked.
     *
     * @return string The user ID.
     */
    public function getUserId()
    {
        return $this->_xacl->getUserId();
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
        $result = $this->_xacl->purge($file);
        Horde::logMessage(
            sprintf(
                "Purged extended attribute cache for file %s.",
                $file
            ),
            __FILE__, __LINE__, PEAR_LOG_DEBUG
        );
        return $result;
    }

    public function store($file, $fb, $acl)
    {
        //@todo: Log the stored values on the side of the cache.
        $result = $this->_xacl->store($file, $fb, $acl);
        Horde::logMessage(
            sprintf(
                "Stored extended attributes cache for file %s.",
                $file
            ),
            __FILE__, __LINE__, PEAR_LOG_DEBUG
        );
        return $result;
    }
}