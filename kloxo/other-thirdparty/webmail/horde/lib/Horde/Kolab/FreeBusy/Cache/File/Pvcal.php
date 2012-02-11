<?php
/**
 * A cache file for partial free/busy information.
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
 * A cache file for partial free/busy information.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/File/Pvcal.php,v 1.1.2.1 2010/10/10 16:26:44 wrobel Exp $
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
class Horde_Kolab_FreeBusy_Cache_File_Pvcal
extends Horde_Kolab_FreeBusy_Cache_File
{
    /**
     * Constructor.
     *
     * @param string $cache_dir The path to the cache direcory.
     */
    public function __construct(
        $cache_dir
    ) {
        parent::__construct($cache_dir);
        $this->setSuffix('pvc');
    }
}
