<?php
/**
 * A berkeley db based cache for free/busy data that holds relevant
 * cache files based on extended folder ACLs.
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
 * cache files based on extended folder ACLs.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/Db/Xacl.php,v 1.1.2.1 2010/10/10 16:26:43 wrobel Exp $
 *
 * Copyright 2004-2008 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @author  Gunnar Wrobel <p@rdus.de>
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @package Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache_Db_Xacl
extends Horde_Kolab_FreeBusy_Cache_Db_Base
{
    /**
     * The type of this cache.
     *
     * @var string
     */
    protected $_type = 'xacl';

    /**
     * Store permissions on a calender folder.
     *
     * @param string $filename The cache file representing the calendar folder.
     * @param string $xacl     The new extended ACL.
     * @param string $oldxacl  The old extended ACL.
     *
     * @return NULL
     */
    public function store($filename, $xacl, $oldxacl)
    {
        $xacl = explode(' ', $xacl);
        $oldxacl = explode(' ', $oldxacl);
        $both = array_intersect($xacl, $oldxacl);

        /* Removed access rights */
        foreach (array_diff($oldxacl, $both) as $uid) {
            if (!empty($uid)) {
                $this->_remove($filename, $uid);
            }
        }

        /* Added access rights */
        foreach (array_diff($xacl, $both) as $uid) {
            if (!empty($uid)) {
                $this->_add($filename, $uid);
            }
        }
    }
}
