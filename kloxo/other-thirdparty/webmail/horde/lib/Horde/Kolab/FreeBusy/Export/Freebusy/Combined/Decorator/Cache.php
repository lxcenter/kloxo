<?php
/**
 * A cache for combined free/busy lists.
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
 * A cache for combined free/busy lists.
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
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
class Horde_Kolab_FreeBusy_Export_Freebusy_Combined_Decorator_Cache
{
    /**
     * Free/Busy access control object.
     *
     * @var Horde_Kolab_FreeBusy_Export_Freebusy_Combined
     */
    private $_combined;

    /**
     * The owner of the accessed data.
     *
     * @var Horde_Kolab_FreeBusy_Owner
     */
    private $_owner;

    /**
     * The user accessing the system.
     *
     * @var Horde_Kolab_FreeBusy_User
     */
    private $_user;

    /**
     * Partial free/busy cache directory.
     *
     * @var string
     */
    private $_cache_dir;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Export_Freebusy_Combined $combined The handler joined free/busy data.
     * @param Horde_Kolab_FreeBusy_Access                   $access   Free/Busy access control
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Export_Freebusy_Combined $combined,
        Horde_Kolab_FreeBusy_Owner $owner,
        Horde_Kolab_FreeBusy_User $user,
        $cache_dir
    ) {
        $this->_combined  = $combined;
        $this->_owner     = $owner;
        $this->_user      = $user;
        $this->_cache_dir = $cache_dir;
    }

    public function generate($extended = false)
    {
        global $conf;

        try {
        if (preg_match('/(.*)@(.*)/', $this->_owner->getPrimaryId(), $regs)) {
            $owner = $regs[2] . '/' . $regs[1];
        }
        if (preg_match('/(.*)@(.*)/', $this->_user->getPrimaryId(), $regs)) {
            $user = $regs[2] . '/' . $regs[1];
        }
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            list($vCal, $mtimes) = $this->_combined->generate($extended);
            return $vCal;
        }

        $c_file = str_replace("\0", '', str_replace('.', '^', $user . '/' . $owner));

        if (empty($conf['fb']['vcal_cache']['min_age'])) {
            $min_age = 300;
        } else {
            $min_age = $conf['fb']['vcal_cache']['min_age'];
        }
        if (empty($conf['fb']['vcal_cache']['max_age'])) {
            $max_age = 259200;
        } else {
            $max_age = $conf['fb']['vcal_cache']['max_age'];
        }

        require_once 'Horde/Kolab/FreeBusy/Cache/File.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/File/Vcal.php';
        $c_vcal = new Horde_Kolab_FreeBusy_Cache_File_Vcal(
            $this->_cache_dir, $extended, $min_age, $max_age
        );
        $c_vcal->setFilename($c_file);

        /* If the current vCal cache did not expire, we can deliver it */
        if (!$c_vcal->expired($this->_combined, $extended)) {
            return $c_vcal->loadVcal();
        }
        list($vCal, $mtimes) = $this->_combined->generate($extended);

        $c_vcal->storeVcal($vCal, $mtimes, $this->_combined->getSignature($extended));

        return $vCal;
    }

}