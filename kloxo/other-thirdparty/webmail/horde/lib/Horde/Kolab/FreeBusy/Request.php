<?php
/**
 * A dummy request provider for Horde 3.
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
 * A dummy request provider for Horde 3.
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
class Horde_Kolab_FreeBusy_Request
{
    /**
     * Parameters.
     *
     * @var array
     */
    private $_parameters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_parameters = array(
            'folder' => Util::getFormData('folder', ''),
            'uid' => Util::getFormData('uid', ''),
        );
    }

    /**
     * Return the parameters.
     *
     * @return array The parameters.
     */
    public function getParameters()
    {
        return $this->_parameters;
    }


    /**
     * Set a parameter.
     *
     * @param string $key   The parameter key.
     * @param string $calue The parameter value.
     *
     * @return array The parameters.
     */
    public function setParameter($key, $value)
    {
        $this->_parameters[$key] = $value;
    }

    /**
     * Return an element from the server parameters.
     *
     * @param string $key The server parameter to return.
     *
     * @return string The server parameter.
     */
    public function getServer($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
    }
}