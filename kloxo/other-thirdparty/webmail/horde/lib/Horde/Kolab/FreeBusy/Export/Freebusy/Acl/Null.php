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
class Horde_Kolab_FreeBusy_Export_Freebusy_Acl_Null
implements Horde_Kolab_FreeBusy_Export_Freebusy_Acl
{
    /**
     * Free/Busy access control object.
     *
     * @var Horde_Kolab_FreeBusy_Access
     */
    private $_access;

    /**
     * Partial free/busy cache directory.
     *
     * @var string
     */
    private $_cache_dir;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Access $access    Free/Busy access control.
     * @param string                      $cache_dir Directory with cached
     *                                               partial free/busy files.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Access $access,
        $cache_dir
    ) {
        $this->_access    = $access;
        $this->_cache_dir = $cache_dir;
    }

    /**
     * Which partials need to be combined into the final vCalendar information?
     *
     * @return array|PEAR_Error The list of files to be combined.
     */
    public function getFiles()
    {
        $file_uid = str_replace("\0", '', str_replace(".", "^", $this->_access->owner));
        $files = array();
        $this->_findAllReaddir($file_uid, $files);

        return $files;
    }

    private function _findAllReaddir($uid, &$lst)
    {
        if ($dir = @opendir($this->_cache_dir . '/' . $uid)) {
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..')
                    continue;

                $full_path = $this->_cache_dir . '/' . $uid . '/' . $file;

                if (is_file($full_path) && preg_match('/(.*)\.x?pvc$/', $file, $matches))
                    $lst[] = $uid . '/' . $matches[1];
                else if(is_dir($full_path))
                    $this->_findAllReaddir($uid . '/' . $file, $full_path, $lst);
            }
            closedir($dir);
        }
    }

    /**
     * Purge the ACL information.
     *
     * @param string $file Name of the cached ACL information.
     *
     * @return boolean|PEAR_Error True if purging worked.
     */
    public function purge($file)
    {
    }

    public function store($file, $fb)
    {
    }
}