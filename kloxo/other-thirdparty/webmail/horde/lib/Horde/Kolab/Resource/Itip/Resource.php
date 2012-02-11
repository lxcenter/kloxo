<?php
/**
 * Provides information about an invited resource.
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
 * Provides information about an invited resource.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */
interface Horde_Kolab_Resource_Itip_Resource
{
    /**
     * Retrieve the mail address of the resource.
     *
     * @return string The mail address.
     */
    public function getMailAddress();

    /**
     * Retrieve the common name of the resource.
     *
     * @return string The common name.
     */
    public function getCommonName();

    /**
     * Retrieve the "From" address for this resource.
     *
     * @return string The "From" address.
     */
    public function getFrom();
}