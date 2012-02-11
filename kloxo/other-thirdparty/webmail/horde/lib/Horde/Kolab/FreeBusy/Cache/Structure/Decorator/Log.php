<?php
/**
 * Handles the structure of the cache.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

require_once 'Horde/Kolab/FreeBusy/Cache/Partial/Freebusy/Decorator/Log.php';

/**
 * Handles the structure of the cache.
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
class Horde_Kolab_FreeBusy_Cache_Structure_Decorator_Log
implements Horde_Kolab_FreeBusy_Cache_Structure
{
    /**
     * The decorated structure hanlder.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Structure
     */
    private $_structure;

    /**
     * The logger.
     *
     * @var Horde_Kolab_FreeBusy_Logger
     */
    private $_logger;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Structure_Base $structure The decorated structure handler.
     * @param Horde_Kolab_FreeBusy_Logger               $logger    The logger.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Cache_Structure $structure,
        Horde_Kolab_FreeBusy_Logger $logger
    ) {
        $this->_structure = $structure;
        $this->_logger = $logger;
    }

    public function getSelf()
    {
        return $this;
    }

    /**
     * Return a handler for a partial based on a folder and an owner.
     *
     * @param Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder The folder being accessed.
     * @param Horde_Kolab_FreeBusy_Owner                  $owner  Owner of the folder.
     *
     * @return Horde_FreeBusy_Cache_Partial A handler for cached partials.
     */
    public function getPartialByOwnerAndFolder(
        Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder,
        Horde_Kolab_FreeBusy_Owner $owner
    ) {
        return new Horde_Kolab_FreeBusy_Cache_Partial_Freebusy_Decorator_Log(
            $this->_structure->getPartialByOwnerAndFolder($folder, $owner),
            $this->_logger
        );
    }

    /**
     * Return a handler for a partial based on an ID.
     *
     * @param string $id The ID.
     *
     * @return Horde_FreeBusy_Cache_Partial A handler for cached partials.
     */
    public function getPartialById($id)
    {
        return new Horde_Kolab_FreeBusy_Cache_Partial_Freebusy_Decorator_Log(
            $this->_structure->getPartialById($id),
            $this->_logger
        );
    }

    /**
     * Return the ACL handler.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Acl The ACL handler.
     */
    public function getAcl(
        Horde_Kolab_FreeBusy_Cache_Structure $self = null
    ) {
        if ($self === null) {
            $self = $this;
        }
        return $this->_structure->getAcl($self);
    }

    /**
     * Return the extended ACL handler.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Xacl The extended ACL handler.
     */
    public function getExtendedAcl(
        Horde_Kolab_FreeBusy_Cache_Structure $self = null
    ) {
        if ($self === null) {
            $self = $this;
        }
        require_once 'Horde/Kolab/FreeBusy/Cache/Xacl.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Xacl/Decorator/Log.php';
        return new Horde_Kolab_FreeBusy_Cache_Xacl_Decorator_Log(
            $this->_structure->getExtendedAcl($self),
            $this->_logger
        );
    }

    /**
     * Return the partials handler.
     *
     * @param Horde_Kolab_FreeBusy_Owner $owner The owner of the data being accessed.
     * @param Horde_Kolab_FreeBusy_User  $user  The user accessing the cache.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Freebusy_Partials The representation of the cached data.
     */
    public function getCombined(
        Horde_Kolab_FreeBusy_Owner $owner,
        Horde_Kolab_FreeBusy_User  $user,
        Horde_Kolab_FreeBusy_Cache_Structure $self = null
    ) {
        if ($self === null) {
            $self = $this;
        }
        return $this->_structure->getCombined($owner, $user, $self);
    }

    /**
     * Return the DB based cache for ACL.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Db_Acl The cache.
     */
    public function getAclDbCache()
    {
        return $this->_structure->getAclDbCache();
    }

    /**
     * Return the DB based cache for extended ACL.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Db_Xacl The cache.
     */
    public function getXaclDbCache()
    {
        return $this->_structure->getXaclDbCache();
    }

    /**
     * Return the file based cache for ACL.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial The partial represented
     *                                                    by the cache.
     *
     * @return Horde_Kolab_FreeBusy_Cache_File_Acl The cache.
     */
    public function getAclFileCache(
        Horde_Kolab_FreeBusy_Cache_Partial $partial
    ) {
        //@todo: log decorator that informs about the stored values.
        return $this->_structure->getAclFileCache($partial);
    }

    /**
     * Return the file based cache for extended ACL.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial The partial represented
     *                                                    by the cache.
     *
     * @return Horde_Kolab_FreeBusy_Cache_File_Xacl The cache.
     */
    public function getXaclFileCache(
        Horde_Kolab_FreeBusy_Cache_Partial $partial
    ) {
        return $this->_structure->getXaclFileCache($partial);
    }

    /**
     * Return the path to the cache directory.
     *
     * @return string The path to the cache directory.
     */
    public function getCacheDir()
    {
        return $this->_structure->getCacheDir();
    }
}