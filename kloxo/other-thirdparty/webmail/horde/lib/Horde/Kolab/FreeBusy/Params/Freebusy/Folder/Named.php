<?php
/**
 * This class provides the folder name as given in the constructor.
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
 * This class provides the folder name as given in the constructor.
 *
 * Copyright 2004-2007 Klarälvdalens Datakonsult AB
 * Copyright 2009-2010 The Horde Project (http://www.horde.org/)
 * Copyright 2011 Kolab Systems AG
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Params_Freebusy_Folder_Named
implements Horde_Kolab_FreeBusy_Params_Freebusy_Folder
{
    /**
     * The owner of the folder.
     *
     * @var string
     */
    private $_owner;

    /**
     * The extracted folder name.
     *
     * @var string
     */
    private $_folder;

    /**
     * Constructor.
     *
     * @param string $name  The folder name in free/busy trigger format.
     */
    public function __construct($name)
    {
        $this->_extractOwnerAndFolder($name);
    }

    /**
     * Extract the owner and folder name from the request.
     *
     * @param string $name  The folder name in free/busy trigger format.
     *
     * @return NULL
     */
    private function _extractOwnerAndFolder($name)
    {
        $folder = explode('/', $name);
        if (count($folder) < 2) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    'No such folder %s. A folder must have at least two components separated by "/".',
                    $name
                )
            );
        }

        $folder[0] = strtolower($folder[0]);
        $this->_owner = $folder[0];
        unset($folder[0]);
        $this->_folder = join('/', $folder);
    }

    /**
     * Extract the folder name from the request.
     *
     * @return string The requested folder.
     */
    public function getFolder()
    {
        return $this->_folder;
    }

    /**
     * Extract the resource owner from the request.
     *
     * @return string The resource owner.
     */
    public function getOwner()
    {
        return $this->_owner;
    }
}