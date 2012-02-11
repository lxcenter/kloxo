<?php
/**
 * Access to the resource storage backend.
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
 * Access to the resource storage backend.
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 * Copyright 2010 Kolab Systems AG
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @package Kolab_Filter
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author  Gunnar Wrobel <wrobel@pardus.de>
 */
class Horde_Kolab_Resource_Storage
{
    /**
     * The ID of the resource.
     *
     * @var string
     */
    private $_resource_id;

    /**
     * An error in case opening the resource did not work.
     *
     * @var PEAR_Error
     */
    private $_error;

    /**
     * The link to the Kolab storage folder handler.
     *
     * @var Kolab_Folder
     */
    private $_folder;

    /**
     * The link to the Kolab storage data handler.
     *
     * @var Kolab_Data
     */
    private $_data;

    /**
     * Constructor.
     *
     * @param string $resource_id The ID of the resource the class should
     *                            manage.
     */
    public function __construct($resource_id)
    {
        $this->_resource_id = $resource_id;
    }

    public function getFolder()
    {
        global $conf;

        // Handle virtual domains
        list($user, $domain) = explode('@', $this->_resource_id);
        if (empty($domain)) {
            $domain = $conf['kolab']['filter']['email_domain'];
        }
        $calendar_user = $conf['kolab']['filter']['calendar_id'] . '@' . $domain;

        /* Load the authentication libraries */
        require_once "Horde/Auth.php";
        require_once 'Horde/Secret.php';

        $auth = &Auth::singleton(isset($conf['auth']['driver'])?$conf['auth']['driver']:'kolab');
        $authenticated = $auth->authenticate($calendar_user,
                                             array('password' => $conf['kolab']['filter']['calendar_pass']),
                                             false);

        if (is_a($authenticated, 'PEAR_Error')) {
            $authenticated->code = OUT_LOG | EX_UNAVAILABLE;
            return $authenticated;
        }
        if (!$authenticated) {
            return PEAR::raiseError(sprintf('Failed to authenticate as calendar user: %s',
                                            $auth->getLogoutReasonString()),
                                    OUT_LOG | EX_UNAVAILABLE);
        }
        @session_start();
        $_SESSION['__auth'] = array(
            'authenticated' => true,
            'userId' => $calendar_user,
            'timestamp' => time(),
            'credentials' => Secret::write(Secret::getKey('auth'),
                                           serialize(array('password' => $conf['kolab']['filter']['calendar_pass']))),
            'remote_addr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        );

        /* Kolab IMAP handling */
        require_once 'Horde/Kolab/Storage/List.php';
        $list = &Kolab_List::singleton();
        $default = $list->getForeignDefault($this->_resource_id, 'event');
        if (!$default || is_a($default, 'PEAR_Error')) {
            $default = new Kolab_Folder();
            $default->setList($list);
            $default->setName($conf['kolab']['filter']['calendar_store']);
            //FIXME: The calendar user needs access here
            $attributes = array('default' => true,
                                'type' => 'event',
                                'owner' => $this->_resource_id);
            $result = $default->save($attributes);
            if (is_a($result, 'PEAR_Error')) {
                $result->code = OUT_LOG | EX_UNAVAILABLE;
                return $result;
            }
        }

        if ($default instanceOf PEAR_Error) {
            $this->_error = $default;
            return;
        }
        if (!$default->exists()) {
            $this->_error = PEAR::raiseError(
                'Error, could not open calendar folder!',
                OUT_LOG | EX_TEMPFAIL
            );
            return;
        }

        $this->_folder = $default;

        $data = $default->getData();
        if ($data instanceOf PEAR_Error) {
            $this->_error = $data;
            return;
        }
        $this->_data = $data;
    }

    public function failed()
    {
        if ($this->_error instanceOf PEAR_Error) {
            return $this->_error->getMessage();
        }
        return false;
    }

    public function objectUidExists($uid)
    {
        if (!$this->failed()) {
            return $this->_data->objectUidExists($uid);
        }
        return false;
    }

    public function save($object, $old_uid)
    {
        return $this->_data->save($object, $old_uid);
    }

    public function delete($uid)
    {
        return $this->_data->delete($uid);
    }

    public function trigger()
    {
        return $this->_folder->trigger();
    }

}