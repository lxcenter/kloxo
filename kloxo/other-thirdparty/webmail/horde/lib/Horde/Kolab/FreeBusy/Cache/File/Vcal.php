<?php
/**
 * A cache file for complete free/busy information.
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
 * A cache file for complete free/busy information.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/File/Vcal.php,v 1.1.2.2 2011/05/30 08:45:39 wrobel Exp $
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
class Horde_Kolab_FreeBusy_Cache_File_Vcal
extends Horde_Kolab_FreeBusy_Cache_File
{

    /**
     * The suffix of this cache file.
     *
     * @var string
     */
    protected $_suffix = 'vc';

    /**
     * Cache file version.
     *
     * @var int
     */
    protected $_version = 3;

    /**
     * Cached data.
     *
     * @var array
     */
    private $_data;

    /**
     * Minimum age before a cache file can be considered to be expired.
     *
     * @var int
     */
    private $_min_age;

    /**
     * Maximum age after which a cache file is always considered to be expired.
     *
     * @var int
     */
    private $_max_age;

    /**
     * Construct the Horde_Kolab_FreeBusy_Cache_File_vcal instance.
     *
     * @param string  $cache_dir The path to the cache direcory.
     * @param boolean $extended  Does the cache hold extended data?
     * @param int     $min_age   Minimum age in seconds before a cache file can
     *                           be considered to be expired.
     * @param int     $max_age   Maximum age in seconds after which a cache file
     *                           is always considered to be expired.
     */
    public function __construct(
        $cache_dir, $extended, $min_age = 300, $max_age = 259200
    ) {
        parent::__construct($cache_dir);
        $this->setSuffix(empty($extended) ? 'vc' : 'xvc');
        $this->_min_age = $min_age;
        $this->_max_age = $max_age;
    }

    /**
     * Store free/busy infomation in the cache file.
     *
     * @param Horde_iCalendar $vcal   A reference to the data object.
     * @param array           $mtimes A list of modification times for the 
     *                                partial free/busy cache times.
     *
     * @return boolean|PEAR_Error True if successful.
     */
    function storeVcal($vcal, $mtimes, $signature)
    {
        $data = array(
            'vcal'      => $vcal,
            'mtimes'    => $mtimes,
            'signature' => $signature
        );
        $this->store($data);
    }

    /**
     * Load the free/busy information from the cache.
     *
     * @return Horde_iCalendar
     */
    function loadVcal()
    {
        if ($this->_data) {
            return $this->_data;
        }

        $result = $this->load();
        $this->_data = $result['vcal'];

        return $this->_data;
    }

    /**
     * Check if the cached free/busy expired.
     *
     * @param array  $files     A list of partial free/busy cache files.
     * @param string $signature The current signature.
     * @param string $signature The current signature.
     *
     * @return boolean True if the cached data expired.
     */
    function expired(
        Horde_Kolab_FreeBusy_Export_Freebusy_Combined $combined,
        $extended
    ) {
        try {
            $this->loadVcal();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            return true;
        }

        /* Check the cache version */
        if ($this->_version !== 3) {
            return true;
        }

        if (!$this->_data instanceOf Horde_iCalendar) {
            return true;
        }

        /* Age of the cache file. */
        $components = $this->_data->getComponents();
        foreach ($components as $component) {
            if ($component->getType() == 'vFreebusy') {
                $attr = $component->getAttributeDefault('DTSTAMP', false);
                if (!empty($attr)) {
                    $dtstamp = (int)$attr;
                }
            }
        }

        if (time() - $dtstamp < $this->_min_age) {
            return false;
        }

        if ($combined->hasRemoteServers()) {
            return true;
        }

        /* Signature changed? */
        if ($result['signature'] !== $combined->getSignature($extended)) {
            return true;
        }

        /* Check the file mtimes */
        $files = $combined->getFiles();
        foreach ($files as $file) {
            if (filemtime($result['mtimes'][$file][0]) != $result['mtimes'][$file][1]) {
                return true;
            }
        }

        if (time() - $dtstamp > $this->_max_age) {
            return true;
        }

        return false;
    }
}
