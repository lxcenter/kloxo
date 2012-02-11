<?php
/**
 * A berkeley db based cache for access lists for cache partials.
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
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/Db/Base.php,v 1.1.2.2 2010/10/10 18:37:57 wrobel Exp $
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
class Horde_Kolab_FreeBusy_Cache_Db_Base
implements Horde_Kolab_FreeBusy_Cache_Db
{

    /**
     * The directory that should be used for caching.
     *
     * @var string
     */
    private $_cache_dir;

    /**
     * The resource handle into the database.
     *
     * @var resource
     */
    private $_db = false;

    /**
     * The format of the database.
     *
     * @var string
     */
    private $_dbformat;

    /**
     * The type of this cache.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * The directory that should be used for caching.
     *
     * @var string
     */
    public function __construct($cache_dir)
    {
        global $conf;

        $this->_cache_dir = $cache_dir;

        if (!empty($conf['fb']['dbformat'])) {
            $this->_dbformat = $conf['fb']['dbformat'];
        } else {
            $this->_dbformat = 'db4';
        }

        /* make sure that a database really exists before accessing it */
        if (!file_exists($this->_cache_dir . '/' . $this->_type . 'cache.db')) {
            $this->_open();
            $this->_close();
        }
    }

    /**
     * Open the database.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case opening the DB failed.
     */
    private function _open()
    {
        if ($this->_db !== false) {
            return true;
        }

        $dbfile = $this->_cache_dir . '/' . $this->_type . 'cache.db';
        if (($this->_db = dba_open($dbfile, 'cd', $this->_dbformat)) === false) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "Unable to open freebusy cache db %s", $dbfile
                )
            );
        }
    }

    /**
     * Close the database.
     */
    private function _close()
    {
        if ($this->_db !== false) {
            dba_close($this->_db);
        }
        $this->_db = false;
    }

    /**
     * Set a partial as irrelevant for a user.
     *
     * @param string $id  The partial ID to remove.
     * @param string $uid The user ID.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case removing the value failed.
     */
    protected function _remove($id, $uid)
    {
        $this->_open();
        if (dba_exists($uid, $this->_db)) {
            $lst = dba_fetch($uid, $this->_db);
            $lst = explode(',', $lst);
            $lst = array_diff($lst, array($id));
            if (dba_replace($uid, join(',', $lst), $this->_db) === false) {
                throw new Horde_Kolab_FreeBusy_Exception(
                    sprintf("Unable to set db value for uid %s", $uid)
                );
            }
        }
        $this->_close();
    }

    /**
     * Set a partial as relevant for a user.
     *
     * @param string $id  The partial ID to add.
     * @param string $uid The user ID.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case adding the value failed.
     */
    protected function _add($id, $uid)
    {
        if (empty($id)) {
            return true;
        }

        $this->_open();
        if (dba_exists($uid, $this->_db)) {
            $lst = dba_fetch($uid, $this->_db);
            $lst = explode(',', $lst);
            $lst[] = $id;
            if (dba_replace($uid, join(',', array_keys(array_flip($lst))), $this->_db) === false) {
                throw new Horde_Kolab_FreeBusy_Exception(
                    sprintf("Unable to set db value for uid %s", $uid)
                );
            }
        } else {
            if (dba_insert($uid, $id, $this->_db) === false) {
                throw new Horde_Kolab_FreeBusy_Exception(
                    sprintf("Unable to set db value for uid %s", $uid)
                );
            }
        }
        $this->_close();
    }

    /**
     * Is the partial relevant for the user?
     *
     * @param string $id  The partial ID.
     * @param string $uid The user ID.
     *
     * @return boolean True if the cache file is relevant.
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case adding the value failed     */
    public function has($id, $uid)
    {
        $this->_open();
        $result = false;
        if (dba_exists($uid, $this->_db)) {
            $lst = dba_fetch($uid, $this->_db);
            $lst = explode(',', $lst);
            $result = in_array($id, $lst);
        }
        $this->_close();
        return $result;
    }

    /**
     * Get the full list of relevant partials for a uid.
     *
     * @param string $uid The user ID.
     *
     * @return array The list of partials.
     */
    public function get($uid)
    {
        $this->_open();
        $result = array();
        if (dba_exists($uid, $this->_db)) {
            $lst = dba_fetch($uid, $this->_db);
            $lst = explode(',', $lst);
            $result = array_filter($lst, array($this, '_notEmpty'));
        }
        $this->_close();
        return $result;
    }

    /**
     * Delete a user from the DB.
     *
     * @param string $uid The user ID.
     *
     * @return NULL
     */
    public function delete($uid)
    {
        foreach ($this->get($uid) as $id) {
            $this->_remove($id, $uid);
        }
    }

    /**
     * Check if the value is set.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value is set.
     */
    private function _notEmpty($value)
    {
        return !empty($value);
    }
}
