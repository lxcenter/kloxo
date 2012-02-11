<?php
/**
 * The Kolab implementation of free/busy.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy.php,v 1.10.2.10 2011/07/25 03:55:18 wrobel Exp $
 *
 * @package Kolab_FreeBusy
 */

/** PEAR for raising errors */
require_once 'PEAR.php';

/** View classes for the result */
require_once 'Horde/Kolab/FreeBusy/View.php';

/** A class that handles access restrictions */
require_once 'Horde/Kolab/FreeBusy/Access.php';

require_once 'Horde/Kolab/FreeBusy/Exception.php';

require_once 'Horde/iCalendar.php';
require_once 'Horde/iCalendar/vfreebusy.php';

/**
 * How to use this class
 *
 * require_once 'config.php';
 *
 * $fb = new Kolab_Freebusy();
 * 
 * $fb->trigger();
 *
 * OR
 *
 * $fb->fetch();
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy.php,v 1.10.2.10 2011/07/25 03:55:18 wrobel Exp $
 *
 * Copyright 2004-2008 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @since   Horde 3.2
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author  Gunnar Wrobel <wrobel@pardus.de>
 * @author  Thomas Arendsen Hein <thomas@intevation.de>
 * @package Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy {

    /**
     * Parameters provided to this class.
     *
     * @var array
     */
    var $_params;

    /**
     * Link to the cache.
     *
     * @var Horde_Kolab_FreeBusy_Cache
     */
    private $_cache;

    private $_access;
    private $_request;
    private $_db_owner;
    private $_db_user;

    /**
     * Trigger regeneration of free/busy data in a calender.
     */
    function trigger()
    {
        global $conf;

        try {
            /* Get the folder name */
            list($user, $owner, $folder) = $this->_getAccess();
            $db_owner = $this->_getDbOwner();

            Horde::logMessage(
                sprintf(
                    "Partial free/busy data for folder \"%s\" of owner \"%s\" requested by user \"%s\".",
                    $folder->getFolder(),
                    $db_owner->getPrimaryId(),
                    $user->getId()
                ), 
                __FILE__,
                __LINE__,
                PEAR_LOG_DEBUG
            );
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $error = array('type' => FREEBUSY_ERROR_NOTFOUND, 'error' => $e);
            return new Horde_Kolab_FreeBusy_View_error($error);
        }

        /* Get the cache request variables */
        $req_cache    = Util::getFormData('cache', false);
        $req_extended = Util::getFormData('extended', false);

        /* Try to fetch the data if it is stored on a remote server */
        $result = $this->fetchRemote($user, $owner, $folder, true, $req_extended);
        if (is_a($result, 'PEAR_Error')) {
            $error = array('type' => FREEBUSY_ERROR_UNAUTHORIZED,
                           'error' => $result);
            $view = new Horde_Kolab_FreeBusy_View_error($error);
            return $view;
        }

        if (!$req_cache) {
            /* User wants to regenerate the cache */
            $db_user  = $this->_getDbUser();
            $db_owner = $this->_getDbOwner();

            /* Here we really need an authenticated IMAP user */
            if (!$db_user->isAuthenticated()) {
                $error = array(
                    'type' => FREEBUSY_ERROR_UNAUTHORIZED,
                    'error' => $result
                );
                $view = new Horde_Kolab_FreeBusy_View_error($error);
                return $view;
            }

            $id = $db_owner->getPrimaryId();
            if (empty($id)) {
                $message = sprintf(_("No such account %s!"), 
                                   htmlentities($owner->getOwner()));
                $error = array('type' => FREEBUSY_ERROR_NOTFOUND,
                               'error' => PEAR::raiseError($message));
                $view = new Horde_Kolab_FreeBusy_View_error($error);
                return $view;
            }

            /* Update the cache */
            $vCal = $this->_generate($this->_getResource());
            if (is_a($vCal, 'PEAR_Error')) {
                $error = array('type' => FREEBUSY_ERROR_NOTFOUND,
                               'error' => $vCal);
                $view = new Horde_Kolab_FreeBusy_View_error($error);
                return $view;
            }

            if (empty($vCal)) {
                $result = $this->_getCache()->deletePartial($this->_getDbUser(), $folder);
            } else {
                $result = $this->_getCache()->storePartial($this->_getDbUser(), $folder, $this->_getResource(), $vCal);
            }
            if (is_a($result, 'PEAR_Error')) {
                $error = array('type' => FREEBUSY_ERROR_NOTFOUND,
                               'error' => $result);
                $view = new Horde_Kolab_FreeBusy_View_error($error);
                return $view;
            }
        }

        /* Load the cache data */
        $vfb = $this->_getCache()->loadPartial($this->_getDbUser(), $folder, $req_extended);
        if (is_a($vfb, 'PEAR_Error')) {
            $error = array('type' => FREEBUSY_ERROR_NOTFOUND,
                           'error' => $vfb);
            $view = new Horde_Kolab_FreeBusy_View_error($error);
            return $view;
        }

        Horde::logMessage("Delivering partial free/busy data.", __FILE__, __LINE__, PEAR_LOG_DEBUG);

        /* Generate the renderer */
        $data = array(
            'fb' => $vfb,
            'name' => $this->_getDbOwner()->getPrimaryId() . '.ifb'
        );
        $view = &new Horde_Kolab_FreeBusy_View_vfb($data);

        /* Finish up */
        Horde::logMessage("Free/busy generation complete.", __FILE__, __LINE__, PEAR_LOG_DEBUG);

        return $view;
    }

    /**
     * Fetch the free/busy data for a user.
     */
    function &fetch()
    {
        global $conf;

        /* Get the user requsted */
        $req_owner = Util::getFormData('uid');

        Horde::logMessage(sprintf("Starting generation of free/busy data for user %s", 
                                  $req_owner), __FILE__, __LINE__, PEAR_LOG_DEBUG);

        try {
            /* Get the folder name */
            list($user, $owner, $folder) = $this->_getAccess();

            Horde::logMessage(
                sprintf(
                    "Free/busy data of owner \"%s\" requested by user \"%s\".",
                    $owner->getOwner(),
                    $user->getId()
                ), 
                __FILE__,
                __LINE__,
                PEAR_LOG_DEBUG
            );
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $error = array('type' => FREEBUSY_ERROR_NOTFOUND, 'error' => $e);
            return new Horde_Kolab_FreeBusy_View_error($error);
        }


        $req_extended = Util::getFormData('extended', false);

        /* Try to fetch the data if it is stored on a remote server */
        $result = $this->fetchRemote(
            $owner, $user, null, false, $req_extended
        );
        if (is_a($result, 'PEAR_Error')) {
            $error = array('type' => FREEBUSY_ERROR_UNAUTHORIZED, 'error' => $result);
            return new Horde_Kolab_FreeBusy_View_error($error);
        }

        try {
            $result = $this->_getCache()->loadCombined(
                $this->_getDbUser(), $req_extended
            );
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            $error = array('type' => FREEBUSY_ERROR_NOTFOUND, 'error' => $e);
            return new Horde_Kolab_FreeBusy_View_error($error);
        }
        if (is_a($result, 'PEAR_Error')) {
            $error = array('type' => FREEBUSY_ERROR_NOTFOUND, 'error' => $result);
            return new Horde_Kolab_FreeBusy_View_error($error);
        }

        Horde::logMessage("Delivering complete free/busy data.", __FILE__, __LINE__, PEAR_LOG_DEBUG);

        /* Generate the renderer */
        $data = array('fb' => $result, 'name' => $access->owner . '.vfb');
        $view = &new Horde_Kolab_FreeBusy_View_vfb($data);

        /* Finish up */
        Horde::logMessage("Free/busy generation complete.", __FILE__, __LINE__, PEAR_LOG_DEBUG);

        return $view;
    }

    /**
     * Regenerate the free/busy cache.
     */
    function &regenerate($reporter)
    {
        $access = &new Horde_Kolab_FreeBusy_Access();
        $result = $access->authenticated();
        if (is_a($result, 'PEAR_Error')) {
            return $result;
        }

        /* Load the required Kolab libraries */ 
        require_once "Horde/Kolab/Storage/List.php";

        $list = &Kolab_List::singleton();
        $calendars = $list->getByType('event');
        if (is_a($calendars, 'PEAR_Error')) {
            return $calendars;
        }

        $lines = array();

        foreach ($calendars as $calendar) {
            /**
             * We are using imap folders for our calendar list but 
             * the library expects us to follow the trigger format
             * used by pfb.php
             */
            $req_domain = explode('@', $calendar->name);
            if (isset($req_domain[1])) {
                $domain = $req_domain[1];
            } else {
                $domain = null;
            }
            $req_folder = explode('/', $req_domain[0]);
            if ($req_folder[0] == 'user') {
                unset($req_folder[0]);
                $owner = $req_folder[1];
                unset($req_folder[1]);
            } else if ($req_folder[0] == 'INBOX') {
                $owner = $access->user;
                unset($req_folder[0]);
            }
            $owner = $owner . ($domain ? '@' . $domain : '');
            $trigger = $owner . '/' . join('/', $req_folder);
            $trigger = String::convertCharset($trigger, 'UTF7-IMAP', 'UTF-8');

            /* Validate folder access */
            $result = $access->parseFolder($trigger);
            if (is_a($result, 'PEAR_Error')) {
                $reporter->failure($calendar->name, $result->getMessage());
                continue;
            }

            /* Hack for allowing manager access */
            if ($access->user == 'manager') {
                $imapc = &Horde_Kolab_IMAP::singleton($GLOBALS['conf']['kolab']['imap']['server'],
                                                      $GLOBALS['conf']['kolab']['imap']['port']);
                $result = $imapc->connect($access->user, Auth::getCredential('password'));
                if (is_a($result, 'PEAR_Error')) {
                    $reporter->failure($calendar->name, $result->getMessage());
                    continue;
                }
                $acl = $imapc->getACL($calendar->name);
                if (is_a($acl, 'PEAR_Error')) {
                    $reporter->failure($calendar->name, $result->getMessage());
                    continue;
                }
                $oldacl = '';
                if (isset($acl['manager'])) {
                    $oldacl = $acl['manager'];
                }
                $result = $imapc->setACL($calendar->name, 'manager', 'lrs');
                if (is_a($result, 'PEAR_Error')) {
                    $reporter->failure($calendar->name, $result->getMessage());
                    continue;
                }
            }

            /* Update the cache */
            try {
                $vCal = $this->_generate($this->_getResource($trigger, $owner));
            } catch (Horde_Kolab_FreeBusy_Exception $e) {
                $reporter->failure($calendar->name, $e->getMessage());
                continue;
            }

            $folder = new Horde_Kolab_FreeBusy_Params_Freebusy_Folder_Named(
                $trigger
            );
            if (empty($vCal)) {
                $result = $this->_getCache($owner)->deletePartial($this->_getDbUser(), $folder);
            } else {
                $result = $this->_getCache($owner)->storePartial($this->_getDbUser(), $folder, $this->_getResource($trigger, $owner), $vCal);
            }
            if (is_a($result, 'PEAR_Error')) {
                $reporter->failure($calendar->name, $result->getMessage());
                continue;
            }

            /* Revert the acl  */
            if ($access->user == 'manager' && $oldacl) {
                $result = $imapc->setACL($calendar->name, 'manager', $oldacl);
                if (is_a($result, 'PEAR_Error')) {
                    $reporter->failure($calendar->name, $result->getMessage());
                    continue;
                }
            }

            $reporter->success($calendar->name);

        }
        return $lines;
    }

    /**
     * Fetch remote free/busy user if the current user is not local or
     * redirect to the other server if configured this way.
     *
     * @param boolean $trigger Have we been called for triggering?
     * @param boolean $extended Should the extended information been delivered?
     */
    function fetchRemote($owner, $user, $folder, $trigger = false, $extended = false)
    {
        global $conf;

        if (!empty($conf['kolab']['freebusy']['server'])) {
            $server = $conf['kolab']['freebusy']['server'];
        } else {
            $server = 'https://localhost/freebusy';
        }
        if (!empty($conf['fb']['redirect'])) {
            $do_redirect = $conf['fb']['redirect'];
        } else {
            $do_redirect = false;
        }

        $db_owner = $this->_getDbOwner();

        try {
            $owner_server = $db_owner->getFreebusyServer();
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
            // May be unknown and present on another remote server.
            return;
        }

        /* Check if we are on the right server and redirect if appropriate */
        if ($owner_server && $owner_server != $server) {

            if ($trigger) {
                $path = sprintf('/trigger/%s/%s.' . ($extended)?'pxfb':'pfb',
                                urlencode($db_owner->getPrimaryId()), urlencode($folder->getFolder()));
            } else {
                $path = sprintf('/%s.' . ($extended)?'xfb':'ifb', urlencode($db_owner->getPrimaryId()));
            }

            $redirect = $owner_server . $path;
            Horde::logMessage(sprintf("URL %s indicates remote free/busy server since we only offer %s. Redirecting.", 
                                      $owner_server, $server), __FILE__,
                              __LINE__, PEAR_LOG_ERR);
            if ($do_redirect) {
                header("Location: $redirect");
            } else {
                header("X-Redirect-To: $redirect");
                $redirect = 'https://' . urlencode($user->getId()) . ':' . urlencode(Auth::getCredential('password'))
                    . '@' . $owner_server . $path;
                if (!@readfile($redirect)) {
                    $message = sprintf(_("Unable to read free/busy information from %s"), 
                                       'https://' . urlencode($user->getPrimaryId()) . ':XXX'
                                       . '@' . $owner_server . $_SERVER['REQUEST_URI']);
                    return PEAR::raiseError($message);
                }
            }
            exit;
        }
    }

    /**
     * Generate partial free/busy data for a calendar.
     *
     * @param Horde_Kolab_FreeBusy_Resource $resource The calendar resource.
     *
     * @return Horde_iCalendar|PEAR_Error The partial free/busy data if successful.
     */
    private function _generate($resource)
    {
        global $conf;

        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Backend.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Backend/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Base.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap/Default.php';
        require_once 'Horde/Kolab/FreeBusy/Helper/Freebusy/StatusMap/Config.php';
        require_once 'Horde/Kolab/FreeBusy/Logger.php';

        $params = array(
            'request_time' => $this->_getRequest()->getServer('REQUEST_TIME')
        );
        if (isset($conf['fb']['future_days'])) {
            $params['future_days'] = $conf['fb']['future_days'];
        }
        if (!empty($conf['fb']['status_map'])) {
            $params['status_map'] = new Horde_Kolab_FreeBusy_Helper_FreeBusy_StatusMap_Config(
                $conf['fb']['status_map']
            );
        }

        $export = new Horde_Kolab_FreeBusy_Export_Freebusy_Decorator_Log(
            new Horde_Kolab_FreeBusy_Export_Freebusy_Base(
                new Horde_Kolab_FreeBusy_Export_Freebusy_Backend_Kolab(),
                $resource,
                $params
            ),
            new Horde_Kolab_FreeBusy_Logger()
        );
        return $export->export();

        /* global $conf; */

        /* /\* Now we really need the free/busy library *\/ */
        /* require_once 'Horde/Kolab/FreeBusy/Imap.php'; */

        /* $fb = new Horde_Kolab_FreeBusy_Imap(); */

        /* $result = $fb->connect($access->imap_folder); */
        /* if (is_a($result, 'PEAR_Error')) { */
        /*     return $result; */
        /* } */

        /* $fbpast = $fbfuture = null; */
        /* if (!empty($access->server_object)) { */
        /*     $result = $access->server_object->get(KOLAB_ATTR_FBPAST); */
        /*     if (!is_a($result, 'PEAR_Error')) { */
        /*         $fbpast = $result; */
        /*     } */
        /* } */
        /* if (!empty($access->owner_object)) { */
        /*     $result = $access->owner_object->get(KOLAB_ATTR_FBFUTURE); */
        /*     if (!is_a($result, 'PEAR_Error')) { */
        /*         $fbfuture = $result; */
        /*     } */
        /* } */

        /* return $fb->generate(null, null, */
	/* 		     !empty($fbpast) ? $fbpast : 0, */
	/* 		     !empty($fbfuture)? $fbfuture : isset($conf['fb']['future_days']) ? $conf['fb']['future_days'] : 60, */
	/* 		     $access->owner, */
	/* 		     $access->owner_object->get(KOLAB_ATTR_CN)); */
    }

    /**
     * Generate partial free/busy data for a calendar.
     *
     * @return Horde_Kolab_FreeBusy_Resource The calendar resource.
     */
    private function _getResource($name = null, $owner = null)
    {
        /* Now we really need the free/busy library */
        require_once 'Horde/Kolab/FreeBusy/Imap.php';

        require_once 'Horde/Kolab/FreeBusy/Resource.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Decorator/Mcache.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Fwthree.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Decorator/Log.php';
        require_once 'Horde/Kolab/FreeBusy/Resource/Event/Decorator/Mcache.php';
        require_once 'Horde/Kolab/FreeBusy/Logger.php';

        $imap = new Horde_Kolab_FreeBusy_Imap();
        if (empty($name)) {
            $imap->connect($this->_getImapFolder()->getResourceId());
        } else {
            $imap->connect($this->_getNamedImapFolder($name)->getResourceId());
        }

        return new Horde_Kolab_FreeBusy_Resource_Event_Decorator_Log(
            new Horde_Kolab_FreeBusy_Resource_Event_Decorator_Mcache(
                new Horde_Kolab_FreeBusy_Resource_Event_Fwthree(
                    $imap, $this->_getDbOwner($owner)
                )
            ),
            new Horde_Kolab_FreeBusy_Logger()
        );
    }

    private function _getImapFolder()
    {
        list($user, $owner, $folder) = $this->_getAccess();
        require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Resource/Kolab.php';
        return new Horde_Kolab_FreeBusy_Params_Freebusy_Resource_Kolab(
            $this->_getDbUser(), $folder
        );
    }

    private function _getNamedImapFolder($name)
    {
        require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Resource/Kolab.php';
        require_once 'Horde/Kolab/FreeBusy/Params/Owner.php';
        require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Folder.php';
        require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Folder/Named.php';
        return new Horde_Kolab_FreeBusy_Params_Freebusy_Resource_Kolab(
            $this->_getDbUser(),
            new Horde_Kolab_FreeBusy_Params_Freebusy_Folder_Named(
                $name
            )
        );
    }

    private function _getAccess()
    {
        if ($this->_access === null) {
            require_once 'Horde/Kolab/FreeBusy/Params/User.php';
            require_once 'Horde/Kolab/FreeBusy/Params/Owner.php';
            require_once 'Horde/Kolab/FreeBusy/Params/Owner/Request.php';
            require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Folder.php';
            require_once 'Horde/Kolab/FreeBusy/Params/Freebusy/Folder/Request.php';

            $this->_access = array(
                new Horde_Kolab_FreeBusy_Params_User(
                    $this->_getRequest()
                ),
                new Horde_Kolab_FreeBusy_Params_Owner_Request(
                    $this->_getRequest()
                ),
                new Horde_Kolab_FreeBusy_Params_Freebusy_Folder_Request(
                    $this->_getRequest()
                )
            );
        }
        return $this->_access;
    }

    private function _getRequest()
    {
        if ($this->_request === null) {
            require_once 'Horde/Kolab/FreeBusy/Request.php';
            $this->_request = new Horde_Kolab_FreeBusy_Request();
        }
        return $this->_request;
    }

    private function _getDbOwner($owner_name = null)
    {
        list($user, $owner, $folder) = $this->_getAccess();
        if (!empty($owner_name)) {
            require_once 'Horde/Kolab/FreeBusy/Params/Owner.php';
            require_once 'Horde/Kolab/FreeBusy/Params/Owner/Named.php';
            $owner = new Horde_Kolab_FreeBusy_Params_Owner_Named(
                $owner_name
            );
        }
        if ($this->_db_owner === null || !empty($owner_name)) {
            require_once 'Horde/Kolab/FreeBusy/UserDb.php';
            require_once 'Horde/Kolab/FreeBusy/UserDb/Kolab.php';
            require_once 'Horde/Kolab/FreeBusy/UserDb/User.php';
            require_once 'Horde/Kolab/FreeBusy/UserDb/User/Kolab.php';
            require_once 'Horde/Kolab/FreeBusy/Owner.php';
            require_once 'Horde/Kolab/FreeBusy/Owner/Kolab.php';
            require_once 'Horde/Kolab/FreeBusy/Owner/Event.php';
            require_once 'Horde/Kolab/FreeBusy/Owner/Event/Kolab.php';
            $id = $owner->getOwner();
            if (empty($id)) {
                $owner = $folder;
            }
            $id = $owner->getOwner();
            if (empty($id)) {
                //@todo: Hm.
                throw new Horde_Kolab_FreeBusy_Exception();
            }
            $this->_db_owner = new Horde_Kolab_FreeBusy_Owner_Event_Kolab(
                $owner,
                new Horde_Kolab_FreeBusy_UserDb_Kolab(),
                $this->_getDbUser()
            );
        }
        return $this->_db_owner;
    }

    private function _getDbUser()
    {
        list($user, $owner, $folder) = $this->_getAccess();
        if ($this->_db_user === null) {
            require_once 'Horde/Kolab/FreeBusy/UserDb.php';
            require_once 'Horde/Kolab/FreeBusy/UserDb/Kolab.php';
            require_once 'Horde/Kolab/FreeBusy/UserDb/User/Kolab.php';
            require_once 'Horde/Kolab/FreeBusy/User.php';
            require_once 'Horde/Kolab/FreeBusy/User/Kolab.php';
            $this->_db_user = new Horde_Kolab_FreeBusy_User_Kolab(
                $user,
                new Horde_Kolab_FreeBusy_UserDb_Kolab()
            );
        }
        return $this->_db_user;
    }

    public function _getCache($owner_name = null)
    {
        global $conf;

        /* Where is the cache data stored? */
        if (!empty($conf['fb']['cache_dir'])) {
            $cache_dir = $conf['fb']['cache_dir'];
        } else {
            if (class_exists('Horde')) {
                $cache_dir = Horde::getTempDir();
            } else {
                $cache_dir = '/tmp';
            }
        }

        /* Load the cache class now */
        require_once 'Horde/Kolab/FreeBusy/Logger.php';
        require_once 'Horde/Kolab/FreeBusy/Cache.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Acl.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Acl/Base.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Partial.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Structure.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Structure/Base.php';
        require_once 'Horde/Kolab/FreeBusy/Cache/Structure/Decorator/Log.php';

        return new Horde_Kolab_FreeBusy_Cache(
            new Horde_Kolab_FreeBusy_Cache_Structure_Decorator_Log(
                new Horde_Kolab_FreeBusy_Cache_Structure_Base(
                    $cache_dir
                ),
                new Horde_Kolab_FreeBusy_Logger()
            ),
            $this->_getDbOwner($owner_name)
        );
    }
}

