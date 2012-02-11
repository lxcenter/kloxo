<?php
/**
 * Configuration based extended free/busy access control for free/busy exports.
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
 * Configuration based extended free/busy access control for free/busy exports.
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
class Horde_Kolab_FreeBusy_Export_Freebusy_Xacl_Configuration
implements Horde_Kolab_FreeBusy_Export_Freebusy_Xacl
{
    /**
     * Free/Busy access control object.
     *
     * @var Horde_Kolab_FreeBusy_Access
     */
    private $_access;

    /**
     * Is access to extended information allowed?
     *
     * @var boolean
     */
    private $_allow;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Access $access Free/Busy access control.
     * @param boolean                     $allow  Allow access to extended
     *                                            information or not?
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Access $access,
        $allow
    ) {
        $this->_access = $access;
        $this->_allow  = $allow;
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
        return $this->_allow;
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
    }

    public function store($file, $fb, $acl)
    {
    }
}