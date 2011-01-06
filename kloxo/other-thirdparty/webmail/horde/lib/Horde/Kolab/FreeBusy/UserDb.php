<?php
/**
 * This interface represents the user database behind the free/busy system.
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
 * This interface represents the user database behind the free/busy system.
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
interface Horde_Kolab_FreeBusy_UserDb
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
    public function connect($id, $pass);

    /**
     * Get the actual database handler.
     *
     * @return mixed
     *
     * //@todo: fix return/refactor
     */
    public function fetchDb();
}
