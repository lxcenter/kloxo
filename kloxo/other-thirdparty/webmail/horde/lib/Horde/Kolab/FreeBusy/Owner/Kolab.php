<?php
/**
 * This class represents a Kolab resource owner.
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
 * This class represents a Kolab resource owner.
 *
 * Copyright 2010 The Horde Project (http://www.horde.org/)
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
class Horde_Kolab_FreeBusy_Owner_Kolab
extends Horde_Kolab_FreeBusy_UserDb_User_Kolab
implements Horde_Kolab_FreeBusy_Owner
{
    /**
     * The owner information.
     *
     * @var Horde_Kolab_FreeBusy_Params_Owner
     */
    private $_owner;

    /**
     * The user accessing the system.
     *
     * @var Horde_Kolab_FreeBusy_User
     */
    private $_user;

    /**
     * The connection to the user database.
     *
     * @var Horde_Kolab_FreeBusy_UserDb
     */
    private $_userdb;

    /**
     * The owner data retrieved from the user database.
     *
     * @var array
     */
    private $_owner_data;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Params_Owner $owner  The resource owner.
     * @param Horde_Kolab_FreeBusy_UserDb       $userdb The connection to the user database.
     * @param Horde_Kolab_FreeBusy_User         $user   The user accessing the system.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Params_Owner $owner,
        Horde_Kolab_FreeBusy_UserDb $userdb,
        Horde_Kolab_FreeBusy_User $user
    ) {
        $this->_owner  = $owner;
        $this->_user   = $user;
        parent::__construct($userdb);
    }

    /**
     * Return the original owner parameter.
     *
     * @return string The original owner parameter.
     */
    public function getOwner()
    {
        return $this->_owner->getOwner();
    }

    /**
     * Fetch the user data from the user db.
     *
     * @return NULL
     */
    protected function fetchUserDbUser()
    {
        try {
            return $this->fetchOwner($this->_owner->getOwner());
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $domain = $this->_user->getDomain();
            if (!empty($domain)) {
                try {
                    return $this->fetchUserByPrimaryId(
                        $this->_owner->getOwner() . '@' . $this->_user->getDomain()
                    );
                } catch (Horde_Kolab_FreeBusy_Exception $f) {
                }
            }
            throw $e;
        }
    }
}