<?php

require_once dirname(__FILE__) . '/sql.php';

/**
 * Horde_Share_sqlng provides the next-generation SQL backend driver for the
 * Horde_Share library.
 *
 * Copyright 2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Jan Schneider <jan@horde.org>
 * @package Horde_Share
 */

/**
 * @package Horde_Share
 */
class Horde_Share_sqlng extends Horde_Share_sql
{
    /**
     * The Horde_Share_Object subclass to instantiate objects as
     *
     * @var string
     */
    var $_shareObject = 'Horde_Share_Object_sqlng';

    /**
     * A list of available permission.
     *
     * This is necessary to unset certain permission when updating existing
     * share objects.
     *
     * @param array
     */
    var $_availablePermissions = array();

    /**
     *
     * @see Horde_Share_Base()
     */
    function Horde_Share_sqlng($app)
    {
        parent::Horde_Share($app);
        $this->_table = $this->_app . '_sharesng';
    }

    /**
     * Initializes the object.
     */
    function __wakeup()
    {
        parent::__wakeup();
        $this->_table = $this->_app . '_sharesng';
    }

    /**
     * Passes the available permissions to the share object.
     *
     * @param Horde_Share_Object $object
     */
    function initShareObject($object)
    {
        parent::initShareObject($object);
        $object->availablePermissions = array_keys($this->_availablePermissions);
    }

    /**
     * Returns an array of all shares that $userid has access to.
     *
     * @param string $userid  The userid of the user to check access for.
     * @param array $params   Additional parameters for the search.
     *  - 'perm':       Require this level of permissions. Horde_Perms constant.
     *  - 'attributes': Restrict shares to these attributes. A hash or username.
     *  - 'from':       Offset. Start at this share
     *  - 'count':      Limit.  Only return this many.
     *  - 'sort_by':    Sort by attribute.
     *  - 'direction':  Sort by direction.
     *
     * @return array  The shares the user has access to.
     * @throws Horde_Share_Exception
     */
    function &listShares($userid, $perm = PERMS_SHOW, $attributes = null,
                         $from = 0, $count = 0, $sort_by = null, $direction = 0)
    {
        $key = md5(serialize(func_get_args()));
        if (!empty($this->_listcache[$key])) {
            return $this->_listcache[$key];
        }

        $perms = $this->convertBitmaskToArray($perm);
        $shareids = null;
        if (!empty($userid)) {
            list($users, $groups, $shareids) = $this->_getUserAndGroupShares($userid, $perms);
        }

        if (is_null($sort_by)) {
            $sortfield = 'share_name';
        } elseif ($sort_by == 'owner' || $sort_by == 'id') {
            $sortfield = 'share_' . $sort_by;
        } else {
            $sortfield = 'attribute_' . $sort_by;
        }

        $query = 'SELECT * FROM ' . $this->_table . ' WHERE '
            . $this->_getShareCriteria($userid, $perms, $attributes, $shareids)
            . ' ORDER BY ' . $sortfield
            . (($direction == 0) ? ' ASC' : ' DESC');

        if ($from > 0 || $count > 0) {
            $this->_db->setLimit(array('limit' => $count, 'offset' => $from));
        }
        $rows = $this->_db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (is_a($rows, 'PEAR_Error')) {
            return $rows;
        }

        $sharelist = array();
        foreach ($rows as $share) {
            $share = $this->_fromDriverCharset($share);
            $this->_loadPermissions($share);
            $sharelist[$share['share_name']] = new $this->_shareObject($share);
        }

        // Run the results through the callback, if configured.
        $result = Horde::callHook('_horde_hook_share_list',
                                  array($userid, $perm, $attributes, $sharelist),
                                  'horde', false);
        if (is_a($result, 'PEAR_Error')) {
            Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $result;
        }

        $this->_listcache[$key] = $sharelist;

        return $this->_listcache[$key];
    }

    /**
     * Returns the number of shares that $userid has access to.
     *
     * @param string $userid     The userid of the user to check access for.
     * @param integer $perm      The level of permissions required.
     * @param mixed $attributes  Restrict the shares counted to those
     *                           matching $attributes. An array of
     *                           attribute/values pairs or a share owner
     *                           username.
     *
     * @return integer  The number of shares
     * @throws Horde_Share_Exception
     */
    function countShares($userid, $perm = PERMS_SHOW, $attributes = null)
    {
        $perms = $this->convertBitmaskToArray($perm);
        $shareids = null;
        if (!empty($userid)) {
            list(, , $shareids) = $this->_getUserAndGroupShares($userid, $perms);
        }

        $query = 'SELECT COUNT(share_id) FROM '
            . $this->_table . ' WHERE '
            . $this->_getShareCriteria($userid, $perms, $attributes, $shareids);

        return $this->_db->queryOne($query);
    }

    /**
     * Converts a bit mask number to a bit mask array.
     *
     * @param integer  A bit mask.
     *
     * @return array  The bit mask as an array.
     */
    function convertBitmaskToArray($perm)
    {
        $perms = array();
        for ($bit = 1; $perm; $bit *= 2, $perm >>= 1) {
            if ($perm % 2) {
                $perms[] = $bit;
            }
        }
        return $perms;
    }

    /**
     * Builds a permission bit mask from all columns in a data row prefixed
     * with "perm_".
     *
     * @param array $row     A data row including permission columns.
     *
     * @return integer  A permission mask.
     */
    function _buildPermsFromRow($row)
    {
        $perms = 0;
        foreach ($row as $column => $value) {
            if (substr($column, 0, 5) != 'perm_') {
                continue;
            }
            $perm = (int)substr($column, 5);
            $this->_availablePermissions[$perm] = true;
            if ($value) {
                $perms |= $perm;
            }
        }
        return $perms;
    }

    /**
     * Converts the permissions from the database table format into the
     * Horde_Share format.
     *
     * @param array $data  The share object data to convert.
     */
    function _getSharePerms(&$data)
    {
        $data['perm']['type'] = 'matrix';
        $data['perm']['default'] = $data['perm']['guest'] = $data['perm']['creator'] = 0;
        foreach ($data as $column => $value) {
            $perm = explode('_', $column, 3);
            if ($perm[0] != 'perm' || count($perm) != 3) {
                continue;
            }
            $permvalue = (int)$perm[2];
            $this->_availablePermissions[$permvalue] = true;
            if ($value) {
                $data['perm'][$perm[1]] |= $permvalue;
            }
            unset($data[$column]);
        }
    }

    /**
     * Returns the records and share IDs from the user and group tables that
     * match the search criteria.
     *
     * @param string $userid     The userid of the user to check access for.
     * @param array $perms       The level of permissions required.
     *
     * @return array  A set of user, groups, and shareids.
     */
    function _getUserAndGroupShares($userid, array $perms)
    {
        $shareids = array();

        // Get users permissions.
        $query = 'SELECT * FROM ' . $this->_table
            . '_users WHERE user_uid = ' .  $this->_db->quote($userid)
            . ' AND (' . $this->_getPermsCriteria('perm', $perms) . ')';
        $users = $this->_db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (is_a($users, 'PEAR_Error')) {
            return $users;
        }
        foreach ($users as $user) {
            $shareids[] = $user['share_id'];
        }

        // Get groups permissions.
        $groups = array();
        require_once 'Horde/Group.php';
        $groupOb = &Group::singleton();
        $groupNames = $groupOb->getGroupMemberships($userid, true);
        if (!is_a($groupNames, 'PEAR_Error') && $groupNames) {
            $group_ids = array();
            foreach (array_keys($groupNames) as $id) {
                $group_ids[] = $this->_db->quote((string)$id);
            }
            $query = 'SELECT * FROM ' . $this->_table
                . '_groups WHERE group_uid IN ('
                . implode(',', $group_ids) . ')' . ' AND ('
                . $this->_getPermsCriteria('perm', $perms) . ')';
            $groups = $this->_db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
            if (is_a($groups, 'PEAR_Error')) {
                return $groups;
            }
            foreach ($groups as $group) {
                $shareids[] = $group['share_id'];
            }
        }

        return array($users, $groups, array_unique($shareids));
    }

    /**
     * Returns a criteria statement for querying shares.
     *
     * @param string $userid     The userid of the user to check access for.
     * @param array $perms       The level of permissions required.
     * @param array $attributes  Restrict the shares returned to those who
     *                           have these attribute values.
     * @param array $shareids    Additional share IDs from user and group
     *                           permissions.
     *
     * @return string  The criteria string for fetching this user's shares.
     */
    function _getShareCriteria($userid, $perms, $attributes, $shareids = null)
    {
        /* Convert to driver's keys */
        $attributes = $this->_toDriverKeys($attributes);

        /* ...and to driver charset */
        $attributes = $this->_toDriverCharset($attributes);

        $where = '';
        if (empty($userid)) {
            $where = $this->_getPermsCriteria('perm_guest', $perms);
        } else {
            // (owner == $userid)
            $where .= 'share_owner = ' . $this->_db->quote($userid);

            // (name == perm_creator and val & $perm)
            $where .= ' OR ' . $this->_getPermsCriteria('perm_creator', $perms);

            // (name == perm_creator and val & $perm)
            $where .= ' OR ' . $this->_getPermsCriteria('perm_default', $perms);

            if ($shareids) {
                $where .= ' OR share_id IN (' . implode(',', $shareids) . ')';
            }
        }

        if (is_array($attributes)) {
            // Build attribute/key filter.
            $where = '(' . $where . ') ';
            foreach ($attributes as $key => $value) {
                if (is_array($value)) {
                    $value = array_map(array($this->_db, 'quote'), $value);
                    $where .= ' AND ' . $key . ' IN (' . implode(', ', $value) . ')';
                } else {
                    $where .= ' AND ' . $key . ' = ' . $this->_db->quote($value);
                }
            }
        } elseif (!empty($attributes)) {
            // Restrict to shares owned by the user specified in the
            // $attributes string.
            $where = '(' . $where . ') AND share_owner = ' . $this->_db->quote($attributes);
        }

        return $where;
    }

    /**
     * Builds an ANDed criteria snippet for a set or permissions.
     *
     * @param string $base  A column name prefix.
     * @param array $perms  A list of permissions.
     *
     * @return string  The generated criteria string.
     */
    function _getPermsCriteria($base, $perms)
    {
        $criteria = array();
        foreach ($perms as $perm) {
            $criteria[] = $base . '_' . $perm . ' = ' . $this->_db->quote(true);
        }
        return implode(' OR ', $criteria);
    }
}

/**
 * Extension of the Horde_Share_Object class for storing share information in
 * the Sqlng driver.
 *
 * @author  Jan Schneider <jan@horde.org>
 * @package Horde_Share
 */
class Horde_Share_Object_sqlng extends Horde_Share_Object_sql
{
    /**
     * A list of available permission.
     *
     * This is necessary to unset certain permission when updating existing
     * share objects.
     *
     * @param array
     */
    var $availablePermissions = array();

    /**
     * Constructor.
     *
     * @param array $data Share data array.
     */
    function Horde_Share_Object_sqlng($data)
    {
        parent::Horde_Share_Object_sql($data);
        $this->_setAvailablePermissions();
    }

    /**
     * Serialize this object.
     *
     * @return string  The serialized data.
     */
    function serialize()
    {
        return serialize(array(
            self::VERSION,
            $this->data,
            $this->_shareCallback,
            $this->availablePermissions,
        ));
    }

    /**
     * Reconstruct the object from serialized data.
     *
     * @param string $data  The serialized data.
     */
    function unserialize($data)
    {
        $data = @unserialize($data);
        if (!is_array($data) ||
            !isset($data[0]) ||
            ($data[0] != self::VERSION)) {
            throw new Exception('Cache version change');
        }

        $this->data = $data[1];
        if (empty($data[2])) {
            throw new Exception('Missing callback for Horde_Share_Object unserializing');
        }
        $this->_shareCallback = $data[2];
        $this->availablePermissions = $data[3];
    }

    /**
     * Saves the current attribute values.
     */
    function _save()
    {
        $db = $this->_shareOb->getWriteDb();
        $table = $this->_shareOb->getTable();

        // Build the parameter arrays for the sql statement.
        $fields = $params = array();
        foreach ($this->_shareOb->_toDriverCharset($this->data) as $key => $value) {
            if ($key != 'share_id' && $key != 'perm' && $key != 'share_flags') {
                $fields[] = $key;
                $params[] = $value;
            }
        }

        $fields[] = 'share_flags';
        $flags = 0;
        if (!empty($this->data['perm']['users'])) {
            $flags |= HORDE_SHARE_SQL_FLAG_USERS;
        }
        if (!empty($this->data['perm']['groups'])) {
            $flags |= HORDE_SHARE_SQL_FLAG_GROUPS;
        }
        $params[] = $flags;

        // Insert new share record, or update existing
        if (empty($this->data['share_id'])) {
            foreach ($this->data['perm'] as $base => $perms) {
                if ($base == 'type' || $base == 'users' || $base == 'groups') {
                    continue;
                }
                foreach (Horde_Share_sqlng::convertBitmaskToArray($perms) as $perm) {
                    $fields[] = 'perm_' . $base . '_' . $perm;
                    $params[] = true;
                }
            }
            $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (?' . str_repeat(', ?', count($fields) - 1) . ')';
        } else {
            foreach ($this->data['perm'] as $base => $perms) {
                if ($base == 'type' || $base == 'users' || $base == 'groups') {
                    continue;
                }
                $perms = array_flip(Horde_Share_sqlng::convertBitmaskToArray($perms));
                foreach ($this->availablePermissions as $perm) {
                    $fields[] = 'perm_' . $base . '_' . $perm;
                    $params[] = isset($perms[$perm]) ? true : false;
                }
            }
            $sql = 'UPDATE ' . $table . ' SET ' . implode(' = ?, ', $fields) . ' = ? WHERE share_id = ?';
            $params[] = $this->data['share_id'];
        }
        $stmt = $db->prepare($sql, null, MDB2_PREPARE_MANIP);
        if (is_a($stmt, 'PEAR_Error')) {
            Horde::logMessage($stmt, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (is_a($result, 'PEAR_Error')) {
            Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $result;
        }
        $stmt->free();

        if (empty($this->data['share_id'])) {
            $this->data['share_id'] = $db->lastInsertID($table, 'share_id');
        }

        // Update the share's user permissions
        $stmt = $db->prepare('DELETE FROM ' . $table . '_users WHERE share_id = ?', null, MDB2_PREPARE_MANIP);
        if (is_a($stmt, 'PEAR_Error')) {
            Horde::logMessage($stmt, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $stmt;
        }
        $result = $stmt->execute(array($this->data['share_id']));
        if (is_a($result, 'PEAR_Error')) {
            Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $result;
        }
        $stmt->free();

        if (!empty($this->data['perm']['users'])) {
            $data = array();
            foreach ($this->data['perm']['users'] as $user => $perms) {
                $fields = $params = array();
                foreach (Horde_Share_sqlng::convertBitmaskToArray($perms) as $perm) {
                    $fields[] = 'perm_' . $perm;
                    $params[] = true;
                }
                if (!$fields) {
                    continue;
                }
                array_unshift($params, $user);
                array_unshift($params, $this->data['share_id']);
                $stmt = $db->prepare('INSERT INTO ' . $table . '_users (share_id, user_uid, ' . implode(', ', $fields) . ') VALUES (?, ?' . str_repeat(', ?', count($fields)) . ')', null, MDB2_PREPARE_MANIP);
                if (is_a($stmt, 'PEAR_Error')) {
                    Horde::logMessage($stmt, __FILE__, __LINE__, PEAR_LOG_ERR);
                    return $stmt;
                }
                $result = $stmt->execute($params);
                if (is_a($result, 'PEAR_Error')) {
                    Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
                    return $result;
                }
                $stmt->free();
            }
        }

        // Update the share's group permissions
        $stmt = $db->prepare('DELETE FROM ' . $table . '_groups WHERE share_id = ?', null, MDB2_PREPARE_MANIP);
        if (is_a($stmt, 'PEAR_Error')) {
            Horde::logMessage($stmt, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $stmt;
        }
        $result = $stmt->execute(array($this->data['share_id']));
        if (is_a($result, 'PEAR_Error')) {
            Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
            return $result;
        }
        $stmt->free();

        if (!empty($this->data['perm']['groups'])) {
            $data = array();
            foreach ($this->data['perm']['groups'] as $group => $perms) {
                $fields = $params = array();
                foreach (Horde_Share_sqlng::convertBitmaskToArray($perms) as $perm) {
                    $fields[] = 'perm_' . $perm;
                    $params[] = true;
                }
                if (!$fields) {
                    continue;
                }
                array_unshift($params, $group);
                array_unshift($params, $this->data['share_id']);
                $stmt = $db->prepare('INSERT INTO ' . $table . '_groups (share_id, group_uid, ' . implode(', ', $fields) . ') VALUES (?, ?' . str_repeat(', ?', count($fields)) . ')', null, MDB2_PREPARE_MANIP);
                if (is_a($stmt, 'PEAR_Error')) {
                    Horde::logMessage($stmt, __FILE__, __LINE__, PEAR_LOG_ERR);
                    return $stmt;
                }
                $result = $stmt->execute($params);
                if (is_a($result, 'PEAR_Error')) {
                    Horde::logMessage($result, __FILE__, __LINE__, PEAR_LOG_ERR);
                    return $result;
                }
                $stmt->free();
            }
        }

        return true;
    }

    /**
     * Sets the permission of this share.
     *
     * @param Horde_Perms_Permission $perm  Permission object.
     * @param boolean $update               Should the share be saved
     *                                      after this operation?
     */
    function setPermission($perm, $update = true)
    {
        parent::setPermission($perm, $update);
        $this->_setAvailablePermissions();
    }

    /**
     * Populates the $availablePermissions property with all seen permissions.
     *
     * This is necessary because the share tables might be extended with
     * arbitrary permissions.
     */
    function _setAvailablePermissions()
    {
        $available = array();
        foreach ($this->availablePermissions as $perm) {
            $available[$perm] = true;
        }
        foreach ($this->data['perm'] as $base => $perms) {
            if ($base == 'type') {
                continue;
            }
            if ($base != 'users' && $base != 'groups') {
                $perms = array($perms);
            }
            foreach ($perms as $subperms) {
                foreach (Horde_Share_sqlng::convertBitmaskToArray($subperms) as $perm) {
                    $available[$perm] = true;
                }
            }
        }
        $this->availablePermissions = array_keys($available);
    }
}
