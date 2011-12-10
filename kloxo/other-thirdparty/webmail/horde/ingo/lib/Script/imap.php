<?php
/**
 * The Ingo_Script_imap:: class represents an IMAP client-side script
 * generator.
 *
 * $Horde: ingo/lib/Script/imap.php,v 1.49.10.22 2010/06/15 10:02:54 jan Exp $
 *
 * Copyright 2003-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (ASL).  If you
 * did not receive this file, see http://www.horde.org/licenses/asl.php.
 *
 * @author  Michael Slusarz <slusarz@horde.org>
 * @package Ingo
 */
class Ingo_Script_imap extends Ingo_Script {

    /**
     * The list of actions allowed (implemented) for this driver.
     *
     * @var array
     */
    var $_actions = array(
        INGO_STORAGE_ACTION_KEEP,
        INGO_STORAGE_ACTION_MOVE,
        INGO_STORAGE_ACTION_DISCARD,
        INGO_STORAGE_ACTION_MOVEKEEP
    );

    /**
     * The categories of filtering allowed.
     *
     * @var array
     */
    var $_categories = array(
        INGO_STORAGE_ACTION_BLACKLIST,
        INGO_STORAGE_ACTION_WHITELIST
    );

    /**
     * The list of tests allowed (implemented) for this driver.
     *
     * @var array
     */
    var $_tests = array(
        'contains', 'not contain'
    );

    /**
     * The types of tests allowed (implemented) for this driver.
     *
     * @var array
     */
    var $_types = array(
        INGO_STORAGE_TYPE_HEADER,
        INGO_STORAGE_TYPE_SIZE,
        INGO_STORAGE_TYPE_BODY
    );

    /**
     * Does the driver support setting IMAP flags?
     *
     * @var boolean
     */
    var $_supportIMAPFlags = true;

    /**
     * Does the driver support the stop-script option?
     *
     * @var boolean
     */
    var $_supportStopScript = true;

    /**
     * This driver can perform on demand filtering (in fact, that is all
     * it can do).
     *
     * @var boolean
     */
    var $_ondemand = true;

    /**
     * The API to use for IMAP functions.
     *
     * @var Ingo_Script_imap_api
     */
    var $_api;

    /**
     * Perform the filtering specified in the rules.
     *
     * @param array $params  The parameter array. It MUST contain:
     *                       - 'imap': An open IMAP stream.
     *                       - 'mailbox': The name of the mailbox to filter.
     *                       - 'show_filter_msg': Show detailed filter status
     *                          messages?
     *                       - 'filter_seen': Only filter seen messages?
     *
     * @return boolean  True if filtering performed, false if not.
     */
    function perform($params)
    {
        global $ingo_storage, $notification, $prefs;

        if (empty($params['api'])) {
            $this->_api = Ingo_Script_imap_api::factory('live', $params);
        } else {
            $this->_api = &$params['api'];
        }

        /* Indices that will be ignored by subsequent rules. */
        $ignore_ids = array();

        /* Only do filtering if:
           1. We have not done filtering before -or-
           2. The mailbox has changed -or-
           3. The rules have changed. */
        $cache = $this->_api->getCache();
        if (($cache !== false) && ($cache == $_SESSION['ingo']['change'])) {
            return true;
        }

        require_once 'Horde/MIME.php';

        /* Grab the rules list. */
        $filters = &$ingo_storage->retrieve(INGO_STORAGE_ACTION_FILTERS);

        /* Parse through the rules, one-by-one. */
        foreach ($filters->getFilterlist() as $rule) {
            /* Check to make sure this is a valid rule and that the rule is
               not disabled. */
            if (!$this->_validRule($rule['action']) ||
                !empty($rule['disable'])) {
                continue;
            }

            $search_array = array();

            switch ($rule['action']) {
            case INGO_STORAGE_ACTION_BLACKLIST:
            case INGO_STORAGE_ACTION_WHITELIST:
                $bl_folder = null;

                if ($rule['action'] == INGO_STORAGE_ACTION_BLACKLIST) {
                    $blacklist = $ingo_storage->retrieve(INGO_STORAGE_ACTION_BLACKLIST);
                    $addr = $blacklist->getBlacklist();
                    $bl_folder = $blacklist->getBlacklistFolder();
                } else {
                    $whitelist = $ingo_storage->retrieve(INGO_STORAGE_ACTION_WHITELIST);
                    $addr = $whitelist->getWhitelist();
                }

                /* If list is empty, move on. */
                if (empty($addr)) {
                    continue;
                }

                require_once INGO_BASE . '/lib/IMAP/Search.php';
                $query = new Ingo_IMAP_Search_Query();
                foreach ($addr as $val) {
                    $ob = $this->_getQuery($params);
                    $ob->header('from', $val);
                    $search_array[] = $ob;
                }
                $query->imapOr($search_array);
                $indices = $this->_api->search($query);

                /* Remove any indices that got in there by way of partial
                 * address match. */
                $sequence = implode(',', $indices);
                $msgs = $this->_api->fetchMessageOverviews($sequence);
                foreach ($msgs as $msg) {
                    $from_addr = MIME::bareAddress($msg->from);
                    $found = false;
                    foreach ($addr as $val) {
                        if (strtolower($from_addr) == strtolower($val)) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $indices = array_diff($indices, array($msg->uid));
                    }
                }

                if ($rule['action'] == INGO_STORAGE_ACTION_BLACKLIST) {
                    $indices = array_diff($indices, $ignore_ids);
                    if (!empty($indices)) {
                        $sequence = implode(',', $indices);
                        if (!empty($bl_folder)) {
                            $this->_api->moveMessages($sequence, $bl_folder);
                        } else {
                            $this->_api->deleteMessages($sequence);
                        }
                        $this->_api->expunge($indices);
                        $notification->push(sprintf(_("Filter activity: %s message(s) that matched the blacklist were deleted."), count($indices)), 'horde.message');
                    }
                } else {
                    $ignore_ids = $indices;
                }
                break;

            case INGO_STORAGE_ACTION_KEEP:
            case INGO_STORAGE_ACTION_MOVE:
            case INGO_STORAGE_ACTION_DISCARD:
                require_once INGO_BASE . '/lib/IMAP/Search.php';
                $query = new Ingo_IMAP_Search_Query();
                foreach ($rule['conditions'] as $val) {
                    $ob = $this->_getQuery($params);
                    if (!empty($val['type']) &&
                        ($val['type'] == INGO_STORAGE_TYPE_SIZE)) {
                        if ($val['match'] == 'greater than') {
                            $operator = '>';
                        } elseif ($val['match'] == 'less than') {
                            $operator = '<';
                        }
                        $ob->size($val['value'], $operator);
                    } elseif (!empty($val['type']) &&
                              ($val['type'] == INGO_STORAGE_TYPE_BODY)) {
                        if ($val['match'] == 'contains') {
                            $ob->body($val['value'], false);
                        } elseif ($val['match'] == 'not contain') {
                            $ob->body($val['value'], true);
                        }
                    } else {
                        if (strpos($val['field'], ',') == false) {
                            if ($val['match'] == 'contains') {
                                $ob->header($val['field'], $val['value'], false);
                            } elseif ($val['match'] == 'not contain') {
                                $ob->header($val['field'], $val['value'], true);
                            }
                        } else {
                            $headers = array();
                            foreach (explode(',', $val['field']) as $header) {
                                $headerOb = $this->_getQuery($params);
                                if ($val['match'] == 'contains') {
                                    $headerOb->header($header, $val['value'], false);
                                } elseif ($val['match'] == 'not contain') {
                                    $headerOb->header($header, $val['value'], true);
                                }
                                $headers[] = $headerOb;
                            }
                            if ($val['match'] == 'contains') {
                                $ob->imapOr($headers);
                            } elseif ($val['match'] == 'not contain') {
                                $ob->imapAnd($headers);
                            }
                        }
                    }
                    $search_array[] = $ob;
                }

                if ($rule['combine'] == INGO_STORAGE_COMBINE_ALL) {
                    $query->imapAnd($search_array);
                } else {
                    $query->imapOr($search_array);
                }

                $indices = $this->_api->search($query);

                if (($indices = array_diff($indices, $ignore_ids))) {
                    if ($rule['stop']) {
                        /* If the stop action is set, add these
                         * indices to the list of ids that will be
                         * ignored by subsequent rules. */
                        $ignore_ids = array_unique($indices + $ignore_ids);
                    }

                    $sequence = implode(',', $indices);

                    /* Set the flags. */
                    if (!empty($rule['flags']) &&
                        ($rule['action'] != INGO_STORAGE_ACTION_DISCARD)) {
                        $flags = array();
                        if ($rule['flags'] & INGO_STORAGE_FLAG_ANSWERED) {
                            $flags[] = '\\Answered';
                        }
                        if ($rule['flags'] & INGO_STORAGE_FLAG_DELETED) {
                            $flags[] = '\\Deleted';
                        }
                        if ($rule['flags'] & INGO_STORAGE_FLAG_FLAGGED) {
                            $flags[] = '\\Flagged';
                        }
                        if ($rule['flags'] & INGO_STORAGE_FLAG_SEEN) {
                            $flags[] = '\\Seen';
                        }
                        $this->_api->setMessageFlags($sequence,
                                                     implode(' ', $flags));
                    }

                    if ($rule['action'] == INGO_STORAGE_ACTION_KEEP) {
                        /* Add these indices to the ignore list. */
                        $ignore_ids = array_unique($indices + $ignore_ids);
                    } elseif ($rule['action'] == INGO_STORAGE_ACTION_MOVE) {
                        /* We need to grab the overview first. */
                        if ($params['show_filter_msg']) {
                            $overview = $this->_api->fetchMessageOverviews($sequence);
                        }

                        /* Move the messages to the requested mailbox. */
                        $this->_api->moveMessages($sequence,
                                                  $rule['action-value']);
                        $this->_api->expunge($indices);

                        /* Display notification message(s). */
                        if ($params['show_filter_msg']) {
                            foreach ($overview as $msg) {
                                $notification->push(
                                    sprintf(_("Filter activity: The message \"%s\" from \"%s\" has been moved to the folder \"%s\"."),
                                            isset($msg->subject) ? MIME::decode($msg->subject, NLS::getCharset()) : _("[No Subject]"),
                                            isset($msg->from) ? MIME::decode($msg->from, NLS::getCharset()) : _("[No Sender]"),
                                            String::convertCharset($rule['action-value'], 'UTF7-IMAP', NLS::getCharset())),
                                    'horde.message');
                            }
                        } else {
                            $notification->push(sprintf(_("Filter activity: %s message(s) have been moved to the folder \"%s\"."),
                                                        count($indices),
                                                        String::convertCharset($rule['action-value'], 'UTF7-IMAP', NLS::getCharset())), 'horde.message');
                        }
                    } elseif ($rule['action'] == INGO_STORAGE_ACTION_DISCARD) {
                        /* We need to grab the overview first. */
                        if ($params['show_filter_msg']) {
                            $overview = $this->_api->fetchMessageOverviews($sequence);
                        }

                        /* Delete the messages now. */
                        $this->_api->deleteMessages($sequence);
                        $this->_api->expunge($indices);

                        /* Display notification message(s). */
                        if ($params['show_filter_msg']) {
                            foreach ($overview as $msg) {
                                $notification->push(
                                    sprintf(_("Filter activity: The message \"%s\" from \"%s\" has been deleted."),
                                            isset($msg->subject) ? MIME::decode($msg->subject, NLS::getCharset()) : _("[No Subject]"),
                                            isset($msg->from) ? MIME::decode($msg->from, NLS::getCharset()) : _("[No Sender]")),
                                    'horde.message');
                            }
                        } else {
                            $notification->push(sprintf(_("Filter activity: %s message(s) have been deleted."), count($indices)), 'horde.message');
                        }
                    } elseif ($rule['action'] == INGO_STORAGE_ACTION_MOVEKEEP) {
                        /* Copy the messages to the requested mailbox. */
                        $this->_api->copyMessages($sequence,
                                                 $rule['action-value']);

                        /* Display notification message(s). */
                        if ($params['show_filter_msg']) {
                            $overview = $this->_api->fetchMessageOverviews($sequence);
                            foreach ($overview as $msg) {
                                $notification->push(
                                    sprintf(_("Filter activity: The message \"%s\" from \"%s\" has been copied to the folder \"%s\"."),
                                            isset($msg->subject) ? MIME::decode($msg->subject, NLS::getCharset()) : _("[No Subject]"),
                                            isset($msg->from) ? MIME::decode($msg->from, NLS::getCharset()) : _("[No Sender]"),
                                            String::convertCharset($rule['action-value'], 'UTF7-IMAP', NLS::getCharset())),
                                    'horde.message');
                            }
                        } else {
                            $notification->push(sprintf(_("Filter activity: %s message(s) have been copied to the folder \"%s\"."), count($indices), String::convertCharset($rule['action-value'], 'UTF7-IMAP', NLS::getCharset())), 'horde.message');
                        }
                    }
                }
                break;
            }
        }

        /* Set cache flag. */
        $this->_api->storeCache($_SESSION['ingo']['change']);
        return true;
    }

    /**
     * Is the apply() function available?
     * The 'mail/getStream' API function must be available.
     *
     * @return boolean  True if apply() is available, false if not.
     */
    function canApply()
    {
        global $registry;

        return ($this->performAvailable() && $registry->hasMethod('mail/getStream'));
    }

    /**
     * Apply the filters now.
     *
     * @return boolean  See perform().
     */
    function apply()
    {
        global $registry, $prefs;

        if ($this->canApply()) {
            $res = $registry->call('mail/getStream', array('INBOX'));
            if ($res !== false) {
                $ob = @imap_check($res);
                return $this->perform(array('imap' => $res, 'mailbox' => $ob->Mailbox, 'filter_seen' => $prefs->getValue('filter_seen'), 'show_filter_msg' => $prefs->getValue('show_filter_msg')));
            }
        }

        return false;
    }

    /**
     * Returns a query object prepared for adding further criteria.
     *
     * @param $params
     *
     * @return Ingo_IMAP_Search_Query  A query object.
     */
    function _getQuery($params)
    {
        $ob = new Ingo_IMAP_Search_Query();
        $ob->deleted(false);

        if ($params['filter_seen'] == INGO_SCRIPT_FILTER_SEEN || $params['filter_seen'] == INGO_SCRIPT_FILTER_UNSEEN) {
            $ob->seen($params['filter_seen'] == INGO_SCRIPT_FILTER_SEEN);
	}    

        return $ob;
    }

}

class Ingo_Script_imap_api {

    var $_params;

    function Ingo_Script_imap_api($params = array())
    {
        $this->_params = $params;
    }

    function factory($type, $params)
    {
        $class = 'Ingo_Script_imap_' . $type;
        if (!class_exists($class)) {
            require dirname(__FILE__) . '/imap/' . $type . '.php';
        }
        return new $class($params);
    }

    function deleteMessages($sequence)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function expunge($indices)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function moveMessages($sequence, $folder)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function copyMessages($sequence, $folder)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function setMessageFlags($sequence, $flags)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function fetchMessageOverviews($sequence)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function search($query)
    {
        return PEAR::raiseError('Not implemented.');
    }

    function getCache()
    {
        return false;
    }

    function storeCache($timestamp)
    {
    }

}
