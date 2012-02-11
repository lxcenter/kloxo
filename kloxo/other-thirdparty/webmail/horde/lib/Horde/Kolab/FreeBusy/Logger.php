<?php
/**
 * A log wrapper for Horde framework 3.
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
 * A log wrapper for Horde framework 3.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If
 * you did not receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Logger
{

    public function error($message)
    {
        Horde::logMessage($message, $this->_getFile(), $this->_getLine(), PEAR_LOG_ERR);
    }

    public function debug($message)
    {
        Horde::logMessage($message, $this->_getFile(), $this->_getLine(), PEAR_LOG_DEBUG);
    }

    private function _getFile()
    {
        $backtrace = debug_backtrace();
        if (isset($backtrace[2]['file'])) {
            return $backtrace[2]['file'];
        }
        return 'UNDEFINED';
    }

    private function _getLine()
    {
        $backtrace = debug_backtrace();
        if (isset($backtrace[2]['line'])) {
            return $backtrace[2]['line'];
        }
        return 'UNDEFINED';
    }
}