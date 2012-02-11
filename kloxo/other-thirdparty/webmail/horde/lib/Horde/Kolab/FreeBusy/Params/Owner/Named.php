<?php
/**
 * This class provides the owner id as provided via the constructor.
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
 * This class provides the owner id as provided via the constructor.
 *
 * Copyright 2009-2010 The Horde Project (http://www.horde.org/)
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
 * @todo     Shouldn't be a separate class. Rather handle string parameters
 *           in a factory or something similar.
 */
class Horde_Kolab_FreeBusy_Params_Owner_Named
implements Horde_Kolab_FreeBusy_Params_Owner
{
    /**
     * The owner id.
     *
     * @var string
     */
    private $_owner;

    /**
     * Constructor.
     *
     * @param string $owner The name of the owner.
     */
    public function __construct($owner)
    {
        $this->_owner = $owner;
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