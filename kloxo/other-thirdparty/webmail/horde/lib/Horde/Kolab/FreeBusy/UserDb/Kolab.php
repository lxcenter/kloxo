<?php
/**
 * This class represents the Kolab user database behind the free/busy system.
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
 * This class represents the Kolab user database behind the free/busy system.
 *
 * Copyright 2010 Kolab Systems AG
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
class Horde_Kolab_FreeBusy_UserDb_Kolab
implements Horde_Kolab_FreeBusy_UserDb
{
    /**
     * Connect to the database using the provided credentials.
     *
     * @param string $user The ID of the user.
     * @param string $pass The password of the user.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_Server_Exception_Bindfailed In case the login failed.
     */
    public function connect($id, $pass)
    {
        $server = Horde_Kolab_Server::singleton(
            array(
                'uid' => $id,
                'pass' => $pass,
            )
        );
        $user_object = $server->fetch();
        if (is_a($user_object, 'PEAR_Error')) {
            throw new Horde_Kolab_FreeBusy_Exception(
                $user_object->getMessage(),
                $user_object->getCode()
            );
        }
    }

    /**
     * Get the actual database handler.
     *
     * @return mixed
     *
     * //@todo: fix return/refactor
     */
    public function fetchDb()
    {
        global $conf;

        require_once 'Horde/Kolab/Server.php';

        /* Connect to the Kolab user database */
        $db = &Horde_Kolab_Server::singleton(array('uid' => $conf['kolab']['ldap']['phpdn']));
        // TODO: Remove once Kolab_Server has been fixed to always return the base dn
        $db->fetch();
        return $db;
    }

    public function getUser(Horde_Kolab_FreeBusy_Params_User $user)
    {
        //@todo This is a bad structure. A decent approach might be to ask the param to return the user object. Logging needs to be taken into account.
        $id = $user->getId();
        if (!empty($id)) {
            return new Horde_Kolab_FreeBusy_User_Kolab($user, $this);
        } else {
            //@todo implement
            //  public function getPrimaryId() {
            //            return 'anonymous';
            //        }
            return new Horde_Kolab_FreeBusy_User_Anonymous();
        }
    }
}
