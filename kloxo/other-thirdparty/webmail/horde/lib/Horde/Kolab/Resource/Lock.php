<?php
/**
 * Handles resource locking.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */

/**
 * Handles resource locking.
 *
 * Copyright 2009-2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @package Kolab_Filter
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author  Gunnar Wrobel <wrobel@pardus.de>
 */
class Horde_Kolab_Resource_Lock
{
    public function isLocked($resource)
    {
        global $conf;

        if (!empty($conf['kolab']['filter']['simple_locks'])) {
            if (!empty($conf['kolab']['filter']['simple_locks_timeout'])) {
                $timeout = $conf['kolab']['filter']['simple_locks_timeout'];
            } else {
                $timeout = 60;
            }
            if (!empty($conf['kolab']['filter']['simple_locks_dir'])) {
                $lockdir = $conf['kolab']['filter']['simple_locks_dir'];
            } else {
                $lockdir = Horde::getTempDir() . '/Kolab_Filter_locks';
                if (!is_dir($lockdir)) {
                    mkdir($lockdir, 0700);
                }
            }
            if (is_dir($lockdir)) {
                $lockfile = $lockdir . '/' . $resource . '.lock';
                $counter = 0;
                while ($counter < $timeout && file_exists($lockfile)) {
                    sleep(1);
                    $counter++;
                }
                if ($counter == $timeout) {
                    Horde::logMessage(sprintf('Lock timeout of %s seconds exceeded. Rejecting invitation.', $timeout),
                                      __FILE__, __LINE__, PEAR_LOG_ERR);
                    return true;
                }
                $result = file_put_contents($lockfile, 'LOCKED');
                if ($result === false) {
                    Horde::logMessage(sprintf('Failed creating lock file %s.', $lockfile),
                                      __FILE__, __LINE__, PEAR_LOG_ERR);
                } else {
                    $this->lockfile = $lockfile;
                }
            } else {
                Horde::logMessage(sprintf('The lock directory %s is missing. Disabled locking.', $lockdir),
                                  __FILE__, __LINE__, PEAR_LOG_ERR);
            }
        }
        return false;
    }

    /**
     * Helper function to clean up after handling an invitation
     *
     * @return NULL
     */
    function cleanup()
    {
        if (!empty($this->lockfile)) {
            @unlink($this->lockfile);
            if (file_exists($this->lockfile)) {
                Horde::logMessage(sprintf('Failed removing the lockfile %s.', $lockfile),
                                  __FILE__, __LINE__, PEAR_LOG_ERR);
            }
            $this->lockfile = null;
        }
    }
}