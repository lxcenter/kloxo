<?php
/**
 * Free/busy access control that ignores ACL information and simply collects all
 * partials belonging to one user.
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
 * Free/busy access control that ignores ACL information and simply collects all
 * partials belonging to one user.
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
class Horde_Kolab_FreeBusy_Cache_Acl_Null
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
        $file_uid = str_replace("\0", '', str_replace(".", "^", $owner->getPrimaryId()));
        $files = array();
        $this->_findAllReaddir($this->_structure->getCacheDir(), $file_uid, $files);
        return $files;
    }

    //@todo: Iterator?
    private function _findAllReaddir($cache_dir, $uid, &$lst)
    {
        if ($dir = @opendir($cache_dir . '/' . $uid)) {
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $full_path = $cache_dir . '/' . $uid . '/' . $file;

                if (is_file($full_path) && preg_match('/(.*)\.x?pvc$/', $file, $matches)) {
                    $lst[] = $uid . '/' . $matches[1];
                } else if(is_dir($full_path)) {
                    $this->_findAllReaddir($cache_dir, $uid . '/' . $file, $full_path, $lst);
                }
            }
            closedir($dir);
        }
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
    }

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
    ) {
        return array();
    }
}