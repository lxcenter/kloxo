<?php
/**
 * A berkeley db based cache for free/busy data that holds relevant
 * cache files based on folder ACLs.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * A berkeley db based cache for free/busy data that holds relevant
 * cache files based on folder ACLs.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/Db/Acl.php,v 1.1.2.1 2010/10/10 16:26:43 wrobel Exp $
 *
 * Copyright 2004-2008 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache_Db_Acl
extends Horde_Kolab_FreeBusy_Cache_Db_Base
{

    /**
     * The type of this cache.
     *
     * @var string
     */
    protected $_type = 'acl';

    /**
     * Store permissions on a calender folder.
     *
     * @param string $filename The cache file representing the calendar folder.
     * @param array  $acl      The new ACL.
     * @param array  $oldacl   The old ACL.
     * @param mixed  $perm     False if all permissions should be revoked, a
     *                         single character specifying allowed access
     *                         otherwise.
     *
     * @return NULL
     */
    public function store($filename, $acl, $oldacl, $perm)
    {
        /* We remove the filename from all users listed in the old ACL first */
        foreach ($oldacl as $user => $ac) {
            $this->_remove($filename, $user);
        }

        /* Now add the filename for all users with the correct permissions */
        if ($perm !== false ) {
            foreach ($acl as $user => $ac) {
                if (strpos($ac, $perm) !== false) {
                    if (!empty($user)) {
                        $this->_add($filename, $user);
                    }
                }
            }
        }
    }
}
