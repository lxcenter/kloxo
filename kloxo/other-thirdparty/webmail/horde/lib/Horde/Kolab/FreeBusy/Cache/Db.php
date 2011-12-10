<?php
/**
 * A berkeley db based cache for free/busy data.
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
 * A berkeley db based cache for free/busy data.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/Db.php,v 1.1.2.1 2010/10/10 16:26:42 wrobel Exp $
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
interface Horde_Kolab_FreeBusy_Cache_Db
{
    /**
     * Is the partial relevant for the user?
     *
     * @param string $id  The partial ID.
     * @param string $uid The user ID.
     *
     * @return boolean True if the cache file is relevant.
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case adding the value failed     */
    public function has($id, $uid);

    /**
     * Get the full list of relevant partials for a uid.
     *
     * @param string $uid The user ID.
     *
     * @return array The list of partials.
     */
    public function get($uid);

    //todo: the store() function should have the same signature at some point.
}
