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
class Horde_Kolab_FreeBusy_Cache_Structure_Base
implements Horde_Kolab_FreeBusy_Cache_Structure
{
    /**
     * The directory used for caching.
     *
     * @var string
     */
    private $_cache_dir;

    /**
     * Constructor.
     *
     * @param string $cache_dir Path to the cache directory.
     */
    public function __construct($cache_dir)
    {
        $this->_cache_dir = $cache_dir;
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
        return $this->getPartialById($this->_getId($folder, $owner));
    }

    /**
     * Get a partial ID representing a resource.
     *
     * @return string ID of the partial.
     */
    private function _getId(
        Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder,
        Horde_Kolab_FreeBusy_Owner $owner
    ) {
        return $this->_encodeId(
            $this->_parseOwnerId($owner->getPrimaryId()) . '/' . $folder->getFolder()
        );
    }

    /**
     * Get the ID representing an owner in the cache.
     *
     * @return string ID of the owner.
     */
    private function _getOwnerId(
        Horde_Kolab_FreeBusy_Owner $owner
    ) {
        return $this->_encodeId($this->_parseOwnerId($owner->getPrimaryId()));
    }

    /**
     * Parse the ID of an owner and return the cache representation.
     *
     * @return string Cache representation for the ID of the owner.
     */
    private function _parseOwnerId($id)
    {
        if (preg_match('/(.*)@(.*)/', $id, $regs)) {
            return $regs[2] . '/' . $regs[1];
        }
        return $id;
    }

    /**
     * Encoding for cache IDs.
     *
     * @param string $id The ID to be encoded.
     *
     * @return string The encoded ID.
     */
    private function _encodeId($id)
    {
        return str_replace(array("\0", '.'), array('', '^'), $id);
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
        require_once 'Horde/Kolab/FreeBusy/Cache/Partial/Freebusy.php';
        return new Horde_Kolab_FreeBusy_Cache_Partial_Freebusy(
            $id,
            new Horde_Kolab_FreeBusy_Cache_File_Pvcal(
                $this->_cache_dir
            )
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
        global $conf;

        if ($self === null) {
            $self = $this;
        }
        require_once 'Horde/Kolab/FreeBusy/Cache/Acl.php';
        if (!empty($conf['fb']['use_acls'])) {
            require_once 'Horde/Kolab/FreeBusy/Cache/Acl/Cache.php';
            return new Horde_Kolab_FreeBusy_Cache_Acl_Cache(
                $self
            );
        } else {
            require_once 'Horde/Kolab/FreeBusy/Cache/Acl/Null.php';
            return new Horde_Kolab_FreeBusy_Cache_Acl_Null(
                $self
            );
        }
    }

    /**
     * Return the extended ACL handler.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Xacl The extended ACL handler.
     */
    public function getExtendedAcl(
        Horde_Kolab_FreeBusy_Cache_Structure $self = null
    ) {
        global $conf;

        if ($self === null) {
            $self = $this;
        }
        require_once 'Horde/Kolab/FreeBusy/Cache/Xacl.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Xacl/Base.php';
        if (!empty($conf['fb']['use_acls'])) {
            require_once 'Horde/Kolab/FreeBusy/Cache/Xacl/Cache.php';
            return new Horde_Kolab_FreeBusy_Cache_Xacl_Cache(
                $self
            );
        } else {
            require_once 'Horde/Kolab/FreeBusy/Cache/Xacl/Configuration.php';
            return new Horde_Kolab_FreeBusy_Cache_Xacl_Configuration(
                $self, true
            );
        }
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
        require_once 'Horde/Kolab/FreeBusy/Cache/Combined/Freebusy.php';
        return new Horde_Kolab_FreeBusy_Cache_Combined_Freebusy(
            $owner, $user, $self
        );
    }

    /**
     * Return the DB based cache for ACL.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Db_Acl The cache.
     */
    public function getAclDbCache()
    {
        return new Horde_Kolab_FreeBusy_Cache_Db_Acl(
            $this->_cache_dir
        );
    }

    /**
     * Return the DB based cache for extended ACL.
     *
     * @return Horde_Kolab_FreeBusy_Cache_Db_Xacl The cache.
     */
    public function getXaclDbCache()
    {
        return new Horde_Kolab_FreeBusy_Cache_Db_Xacl(
            $this->_cache_dir
        );
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
        require_once 'Horde/Kolab/FreeBusy/Cache/File/Acl.php';
        $filecache = new Horde_Kolab_FreeBusy_Cache_File_Acl(
            $this->_cache_dir
        );
        $filecache->setPartial($partial);
        return $filecache;
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
        require_once 'Horde/Kolab/FreeBusy/Cache/File/Xacl.php';
        $filecache = new Horde_Kolab_FreeBusy_Cache_File_Xacl(
            $this->_cache_dir
        );
        $filecache->setPartial($partial);
        return $filecache;
    }

    /**
     * Delete all data specific to one user from the cache.
     *
     * @param Horde_Kolab_FreeBusy_Owner $owner The owner to be deleted.
     *
     * @return Horde_Kolab_FreeBusy_Cache_File_Xacl The cache.
     */
    public function deleteOwner(
        Horde_Kolab_FreeBusy_Owner $owner
    ) {
        $this->getAclDbCache()->delete($owner->getPrimaryId());
        $this->getXaclDbCache()->delete($owner->getPrimaryId());

        $owner_dir = $this->_cache_dir . DIRECTORY_SEPARATOR . $this->_getOwnerId($owner);

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($owner_dir), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($owner_dir), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            }
        }
        rmdir($owner_dir);
    }


    /**
     * Return the path to the cache directory.
     *
     * @return string The path to the cache directory.
     */
    public function getCacheDir()
    {
        return $this->_cache_dir;
    }
}