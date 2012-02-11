<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Lorenzo Alberton                       |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lorenzo Alberton <l.alberton@quipo.it>                       |
// +----------------------------------------------------------------------+
//
// $Id: ibase.php,v 1.116 2008/08/25 19:57:10 afz Exp $

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 FireBird/InterBase driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Driver_Manager_ibase extends MDB2_Driver_Manager_Common
{
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param string $name    name of the database that should be created
     * @param array  $options array with charset info
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createDatabase($name, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null, 'Create database',
                'PHP Interbase API does not support direct queries. You have to '.
                'create the db manually by using isql command or a similar program', __FUNCTION__);
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param string $name  name of the database that should be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null, 'Drop database',
                'PHP Interbase API does not support direct queries. You have '.
                'to drop the db manually by using isql command or a similar program', __FUNCTION__);
    }

    // }}}
    // {{{ _silentCommit()

    /**
     * conditional COMMIT query to make changes permanent, when auto
     * @access private
     */
    function _silentCommit()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!$db->in_transaction) {
            @$db->exec('COMMIT');
        }
    }

    // }}}
    // {{{ _makeAutoincrement()

    /**
     * add an autoincrement sequence + trigger
     *
     * @param string $name  name of the PK field
     * @param string $table name of the table
     * @param string $start start value for the sequence
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access private
     */
    function _makeAutoincrement($name, $table, $start = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $table_quoted = $db->quoteIdentifier($table, true);
        if (is_null($start)) {
            $db->beginTransaction();
            $query = 'SELECT MAX(' . $db->quoteIdentifier($name, true) . ') FROM ' . $table_quoted;
            $start = $this->db->queryOne($query, 'integer');
            if (PEAR::isError($start)) {
                return $start;
            }
            ++$start;
            $result = $db->manager->createSequence($table, $start);
            $db->commit();
        } else {
            $result = $db->manager->createSequence($table, $start);
        }
        if (PEAR::isError($result)) {
            return $db->raiseError(null, null, null,
                'sequence for autoincrement PK could not be created', __FUNCTION__);
        }

        $sequence_name = $db->getSequenceName($table);
        $trigger_name  = $db->quoteIdentifier($table . '_AI_PK', true);
        $name  = $db->quoteIdentifier($name, true);
        $trigger_sql = 'CREATE TRIGGER ' . $trigger_name . ' FOR ' . $table_quoted . '
                        ACTIVE BEFORE INSERT POSITION 0
                        AS
                        BEGIN
                        IF (NEW.' . $name . ' IS NULL OR NEW.' . $name . ' = 0) THEN
                            NEW.' . $name . ' = GEN_ID('.$sequence_name.', 1);
                        END';
        $result = $db->exec($trigger_sql);
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->_silentCommit();
        return MDB2_OK;
    }

    // }}}
    // {{{ _dropAutoincrement()

    /**
     * drop an existing autoincrement sequence + trigger
     *
     * @param string $table name of the table
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access private
     */
    function _dropAutoincrement($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $result = $db->manager->dropSequence($table);
        if (PEAR::isError($result)) {
            return $db->raiseError(null, null, null,
                'sequence for autoincrement PK could not be dropped', __FUNCTION__);
        }
        //remove autoincrement trigger associated with the table
        $table = $db->quote(strtoupper($table), 'text');
        $trigger_name = $db->quote(strtoupper($table) . '_AI_PK', 'text');
        $trigger_name_old = $db->quote(strtoupper($table) . '_AUTOINCREMENT_PK', 'text');
        $query = "DELETE FROM RDB\$TRIGGERS
                   WHERE UPPER(RDB\$RELATION_NAME)=$table
                     AND (UPPER(RDB\$TRIGGER_NAME)=$trigger_name
                      OR UPPER(RDB\$TRIGGER_NAME)=$trigger_name_old)";
        $result = $db->exec($query);
        if (PEAR::isError($result)) {
            return $db->raiseError(null, null, null,
                'trigger for autoincrement PK could not be dropped', __FUNCTION__);
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTable()

    /**
     * create a new table
     *
     * @param string $name    Name of the database that should be created
     * @param array  $fields  Associative array that contains the definition of each field of the new table
     *                        The indexes of the array entries are the names of the fields of the table an
     *                        the array entry values are associative arrays like those that are meant to be
     *                        passed with the field definitions to get[Type]Declaration() functions.
     *
     *                        Example
     *                        array(
     *                            'id' => array(
     *                                'type' => 'integer',
     *                                'unsigned' => 1,
     *                                'notnull' => 1,
     *                                'default' => 0,
     *                            ),
     *                            'name' => array(
     *                                'type' => 'text',
     *                                'length' => 12,
     *                            ),
     *                            'description' => array(
     *                                'type' => 'text',
     *                                'length' => 12,
     *                            )
     *                       );
     * @param array  $options An associative array of table options:
     *                        array(
     *                            'comment' => 'Foo',
     *                            'temporary' => true|false,
     *                        );
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createTable($name, $fields, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = $this->_getCreateTableQuery($name, $fields, $options);
        if (PEAR::isError($query)) {
            return $query;
        }

        $options_strings = array();

        if (!empty($options['comment'])) {
            $options_strings['comment'] = '/* '.$db->quote($options['comment'], 'text'). ' */';
        }

        if (!empty($options_strings)) {
            $query.= ' '.implode(' ', $options_strings);
        }
        
        $result = $db->exec($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->_silentCommit();
        foreach ($fields as $field_name => $field) {
            if (!empty($field['autoincrement'])) {
                //create PK constraint
                $pk_definition = array(
                    'fields' => array($field_name => array()),
                    'primary' => true,
                );
                //$pk_name = $name.'_PK';
                $pk_name = null;
                $result = $this->createConstraint($name, $pk_name, $pk_definition);
                if (PEAR::isError($result)) {
                    return $result;
                }
                //create autoincrement sequence + trigger
                return $this->_makeAutoincrement($field_name, $name, 1);
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ checkSupportedChanges()

    /**
     * Check if planned changes are supported
     *
     * @param string $name name of the database that should be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function checkSupportedChanges(&$changes)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'notnull':
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'it is not supported changes to field not null constraint', __FUNCTION__);
            case 'default':
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'it is not supported changes to field default value', __FUNCTION__);
            case 'length':
                /*
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'it is not supported changes to field default length', __FUNCTION__);
                */
            case 'unsigned':
            case 'type':
            case 'declaration':
            case 'definition':
                break;
            default:
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'it is not supported change of type' . $change_name, __FUNCTION__);
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ _getTemporaryTableQuery()

    /**
     * A method to return the required SQL string that fits between CREATE ... TABLE
     * to create the table as a temporary table.
     *
     * @return string The string required to be placed between "CREATE" and "TABLE"
     *                to generate a temporary table, if possible.
     */
    function _getTemporaryTableQuery()
    {
        return 'GLOBAL TEMPORARY';
    }

    // }}}
    // {{{ _getAdvancedFKOptions()

    /**
     * Return the FOREIGN KEY query section dealing with non-standard options
     * as MATCH, INITIALLY DEFERRED, ON UPDATE, ...
     *
     * @param array $definition
     *
     * @return string
     * @access protected
     */
    function _getAdvancedFKOptions($definition)
    {
        $query = '';
        if (!empty($definition['onupdate'])) {
            $query .= ' ON UPDATE '.$definition['onupdate'];
        }
        if (!empty($definition['ondelete'])) {
            $query .= ' ON DELETE '.$definition['ondelete'];
        }
        return $query;
    }

    // }}}
    // {{{ dropTable()

    /**
     * drop an existing table
     *
     * @param string $name name of the table that should be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropTable($name)
    {
        $result = $this->_dropAutoincrement($name);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result = parent::dropTable($name);
        $this->_silentCommit();
        return $result;
    }

    // }}}
    // {{{ vacuum()

    /**
     * Optimize (vacuum) all the tables in the db (or only the specified table)
     * and optionally run ANALYZE.
     *
     * @param string $table table name (all the tables if empty)
     * @param array  $options an array with driver-specific options:
     *               - timeout [int] (in seconds) [mssql-only]
     *               - analyze [boolean] [pgsql and mysql]
     *               - full [boolean] [pgsql-only]
     *               - freeze [boolean] [pgsql-only]
     *
     * @return mixed MDB2_OK success, a MDB2 error on failure
     * @access public
     */
    function vacuum($table = null, $options = array())
    {
        // not needed in Interbase/Firebird
        return MDB2_OK;
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param string  $name    name of the table that is intended to be changed.
     * @param array   $changes associative array that contains the details of each type
     *                         of change that is intended to be performed. The types of
     *                         changes that are currently supported are defined as follows:
     *
     *                         name
     *
     *                             New name for the table.
     *
     *                         add
     *
     *                             Associative array with the names of fields to be added as
     *                             indexes of the array. The value of each entry of the array
     *                             should be set to another associative array with the properties
     *                             of the fields to be added. The properties of the fields should
     *                             be the same as defined by the MDB2 parser.
     *
     *
     *                         remove
     *
     *                             Associative array with the names of fields to be removed as indexes
     *                             of the array. Currently the values assigned to each entry are ignored.
     *                             An empty array should be used for future compatibility.
     *
     *                         rename
     *
     *                             Associative array with the names of fields to be renamed as indexes
     *                             of the array. The value of each entry of the array should be set to
     *                             another associative array with the entry named name with the new
     *                             field name and the entry named Declaration that is expected to contain
     *                             the portion of the field declaration already in DBMS specific SQL code
     *                             as it is used in the CREATE TABLE statement.
     *
     *                         change
     *
     *                             Associative array with the names of the fields to be changed as indexes
     *                             of the array. Keep in mind that if it is intended to change either the
     *                             name of a field and any other properties, the change array entries
     *                             should have the new names of the fields as array indexes.
     *
     *                             The value of each entry of the array should be set to another associative
     *                             array with the properties of the fields to that are meant to be changed as
     *                             array entries. These entries should be assigned to the new values of the
     *                             respective properties. The properties of the fields should be the same
     *                             as defined by the MDB2 parser.
     *
     *                             Example
     *                                array(
     *                                    'name' => 'userlist',
     *                                    'add' => array(
     *                                        'quota' => array(
     *                                            'type' => 'integer',
     *                                            'unsigned' => 1
     *                                        )
     *                                    ),
     *                                    'remove' => array(
     *                                        'file_limit' => array(),
     *                                        'time_limit' => array()
     *                                    ),
     *                                    'change' => array(
     *                                        'name' => array(
     *                                            'length' => '20',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 20,
     *                                            ),
     *                                        )
     *                                    ),
     *                                    'rename' => array(
     *                                        'sex' => array(
     *                                            'name' => 'gender',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 1,
     *                                                'default' => 'M',
     *                                            ),
     *                                        )
     *                                    )
     *                                )
     *
     * @param boolean $check   indicates whether the function should just check if the DBMS driver
     *                         can perform the requested table alterations if the value is true or
     *                         actually perform them otherwise.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'add':
            case 'remove':
            case 'rename':
                break;
            case 'change':
                foreach ($changes['change'] as $field) {
                    if (PEAR::isError($err = $this->checkSupportedChanges($field))) {
                        return $err;
                    }
                }
                break;
            default:
                return $db->raiseError(MDB2_ERROR_CANNOT_ALTER, null, null,
                    'change type ' . $change_name . ' not yet supported', __FUNCTION__);
            }
        }
        if ($check) {
            return MDB2_OK;
        }
        $query = '';
        if (!empty($changes['add']) && is_array($changes['add'])) {
            foreach ($changes['add'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $query.= 'ADD ' . $db->getDeclaration($field['type'], $field_name, $field);
            }
        }

        if (!empty($changes['remove']) && is_array($changes['remove'])) {
            foreach ($changes['remove'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $field_name = $db->quoteIdentifier($field_name, true);
                $query.= 'DROP ' . $field_name;
            }
        }

        if (!empty($changes['rename']) && is_array($changes['rename'])) {
            foreach ($changes['rename'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $field_name = $db->quoteIdentifier($field_name, true);
                $query.= 'ALTER ' . $field_name . ' TO ' . $db->quoteIdentifier($field['name'], true);
            }
        }

        if (!empty($changes['change']) && is_array($changes['change'])) {
            // missing support to change DEFAULT and NULLability
            foreach ($changes['change'] as $field_name => $field) {
                if (PEAR::isError($err = $this->checkSupportedChanges($field))) {
                    return $err;
                }
                if ($query) {
                    $query.= ', ';
                }
                $db->loadModule('Datatype', null, true);
                $field_name = $db->quoteIdentifier($field_name, true);
                $query.= 'ALTER ' . $field_name.' TYPE ' . $db->datatype->getTypeDeclaration($field['definition']);
            }
        }

        if (!strlen($query)) {
            return MDB2_OK;
        }

        $name = $db->quoteIdentifier($name, true);
        $result = $db->exec("ALTER TABLE $name $query");
        $this->_silentCommit();
        return $result;
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed array of table names on success, a MDB2 error on failure
     * @access public
     */
    function listTables()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'SELECT RDB$RELATION_NAME
                    FROM RDB$RELATIONS
                   WHERE (RDB$SYSTEM_FLAG=0 OR RDB$SYSTEM_FLAG IS NULL)
                     AND RDB$VIEW_BLR IS NULL
                ORDER BY RDB$RELATION_NAME';
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a table in the current database
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of field names on success, a MDB2 error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quote(strtoupper($table), 'text');
        $query = "SELECT RDB\$FIELD_NAME
                    FROM RDB\$RELATION_FIELDS
                   WHERE UPPER(RDB\$RELATION_NAME)=$table
                     AND (RDB\$SYSTEM_FLAG=0 OR RDB\$SYSTEM_FLAG IS NULL)";
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listUsers()

    /**
     * list all users
     *
     * @return mixed array of user names on success, a MDB2 error on failure
     * @access public
     */
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->queryCol('SELECT DISTINCT RDB$USER FROM RDB$USER_PRIVILEGES');
    }

    // }}}
    // {{{ listViews()

    /**
     * list all views in the current database
     *
     * @return mixed array of view names on success, a MDB2 error on failure
     * @access public
     */
    function listViews()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->queryCol('SELECT DISTINCT RDB$VIEW_NAME FROM RDB$VIEW_RELATIONS');
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listTableViews()

    /**
     * list the views in the database that reference a given table
     *
     * @param string table for which all referenced views should be found
     *
     * @return mixed array of view names on success, a MDB2 error on failure
     * @access public
     */
    function listTableViews($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT DISTINCT RDB$VIEW_NAME FROM RDB$VIEW_RELATIONS';
        $table = $db->quote(strtoupper($table), 'text');
        $query .= " WHERE UPPER(RDB\$RELATION_NAME)=$table";
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listFunctions()

    /**
     * list all functions (and stored procedures) in the current database
     *
     * @return mixed array of function names on success, a MDB2 error on failure
     * @access public
     */
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT RDB$FUNCTION_NAME FROM RDB$FUNCTIONS WHERE (RDB$SYSTEM_FLAG IS NULL OR RDB$SYSTEM_FLAG = 0)
                  UNION
                  SELECT RDB$PROCEDURE_NAME FROM RDB$PROCEDURES';
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ listTableTriggers()

    /**
     * list all triggers in the database that reference a given table
     *
     * @param string table for which all referenced triggers should be found
     *
     * @return mixed array of trigger names on success, a MDB2 error on failure
     * @access public
     */
    function listTableTriggers($table = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT RDB$TRIGGER_NAME
                    FROM RDB$TRIGGERS
                   WHERE (RDB$SYSTEM_FLAG IS NULL
                      OR RDB$SYSTEM_FLAG = 0)';
        if (!is_null($table)) {
            $table = $db->quote(strtoupper($table), 'text');
            $query .= " AND UPPER(RDB\$RELATION_NAME)=$table";
        }
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
    // {{{ createIndex()

    /**
     * Get the stucture of a field into an array
     *
     * @param string $table      name of the table on which the index is to be created
     * @param string $name       name of the index to be created
     * @param array  $definition associative array that defines properties of the index to be created.
     *                           Currently, only one property named FIELDS is supported. This property
     *                           is also an associative with the names of the index fields as array
     *                           indexes. Each entry of this array is set to another type of associative
     *                           array that specifies properties of the index that are specific to
     *                           each field.
     *
     *                           Currently, only the sorting property is supported. It should be used
     *                           to define the sorting direction of the index. It may be set to either
     *                           ascending or descending.
     *
     *                           Not all DBMS support index sorting direction configuration. The DBMS
     *                           drivers of those that do not support it ignore this property. Use the
     *                           function support() to determine whether the DBMS driver can manage indexes.

     *                           Example
     *                           array(
     *                               'fields' => array(
     *                                   'user_name' => array(
     *                                       'sorting' => 'ascending'
     *                                    ),
     *                                    'last_login' => array()
     *                                )
     *                            )
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createIndex($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'CREATE';

        $query_sort = '';
        foreach ($definition['fields'] as $field) {
            if (!strcmp($query_sort, '') && isset($field['sorting'])) {
                switch ($field['sorting']) {
                case 'ascending':
                    $query_sort = ' ASC';
                    break;
                case 'descending':
                    $query_sort = ' DESC';
                    break;
                }
            }
        }
        $table = $db->quoteIdentifier($table, true);
        $name  = $db->quoteIdentifier($db->getIndexName($name), true);
        $query .= $query_sort. " INDEX $name ON $table";
        $fields = array();
        foreach (array_keys($definition['fields']) as $field) {
            $fields[] = $db->quoteIdentifier($field, true);
        }
        $query .= ' ('.implode(', ', $fields) . ')';
        $result = $db->exec($query);
        $this->_silentCommit();
        return $result;
    }

    // }}}
    // {{{ listTableIndexes()

    /**
     * list all indexes in a table
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of index names on success, a MDB2 error on failure
     * @access public
     */
    function listTableIndexes($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quote(strtoupper($table), 'text');
        $query = "SELECT RDB\$INDEX_NAME
                    FROM RDB\$INDICES
                   WHERE UPPER(RDB\$RELATION_NAME)=$table
                     AND (RDB\$SYSTEM_FLAG=0 OR RDB\$SYSTEM_FLAG IS NULL)
                     AND RDB\$UNIQUE_FLAG IS NULL
                     AND RDB\$FOREIGN_KEY IS NULL";
        $indexes = $db->queryCol($query, 'text');
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $index) {
            $index = $this->_fixIndexName($index);
            if (!empty($index)) {
                $result[$index] = true;
            }
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }

    // }}}
    // {{{ createConstraint()

    /**
     * create a constraint on a table
     *
     * @param string $table      name of the table on which the constraint is to be created
     * @param string $name       name of the constraint to be created
     * @param array  $definition associative array that defines properties of the constraint to be created.
     *                           Currently, only one property named FIELDS is supported. This property
     *                           is also an associative with the names of the constraint fields as array
     *                           constraints. Each entry of this array is set to another type of associative
     *                           array that specifies properties of the constraint that are specific to
     *                           each field.
     *
     *                           Example
     *                               array(
     *                                   'fields' => array(
     *                                       'user_name' => array(),
     *                                       'last_login' => array(),
     *                                   )
     *                               )
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createConstraint($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        if (!empty($name)) {
            $name = $db->quoteIdentifier($db->getIndexName($name), true);
        }
        $query = "ALTER TABLE $table ADD";
        if (!empty($definition['primary'])) {
            if (!empty($name)) {
                $query.= ' CONSTRAINT '.$name;
            }
            $query.= ' PRIMARY KEY';
        } else {
            $query.= ' CONSTRAINT '. $name;
            if (!empty($definition['unique'])) {
               $query.= ' UNIQUE';
            } elseif (!empty($definition['foreign'])) {
                $query.= ' FOREIGN KEY';
            }
        }
        $fields = array();
        foreach (array_keys($definition['fields']) as $field) {
            $fields[] = $db->quoteIdentifier($field, true);
        }
        $query .= ' ('. implode(', ', $fields) . ')';
        if (!empty($definition['foreign'])) {
            $query.= ' REFERENCES ' . $db->quoteIdentifier($definition['references']['table'], true);
            $referenced_fields = array();
            foreach (array_keys($definition['references']['fields']) as $field) {
                $referenced_fields[] = $db->quoteIdentifier($field, true);
            }
            $query .= ' ('. implode(', ', $referenced_fields) . ')';
            $query .= $this->_getAdvancedFKOptions($definition);
        }
        $result = $db->exec($query);
        $this->_silentCommit();
        return $result;
    }

    // }}}
    // {{{ listTableConstraints()

    /**
     * list all constraints in a table
     *
     * @param string $table name of table that should be used in method
     *
     * @return mixed array of constraint names on success, a MDB2 error on failure
     * @access public
     */
    function listTableConstraints($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quote(strtoupper($table), 'text');
        $query = "SELECT RDB\$INDEX_NAME
                    FROM RDB\$INDICES
                   WHERE UPPER(RDB\$RELATION_NAME)=$table
                     AND (
                           RDB\$UNIQUE_FLAG IS NOT NULL
                        OR RDB\$FOREIGN_KEY IS NOT NULL
                     )";
        $constraints = $db->queryCol($query);
        if (PEAR::isError($constraints)) {
            return $constraints;
        }

        $result = array();
        foreach ($constraints as $constraint) {
            $constraint = $this->_fixIndexName($constraint);
            if (!empty($constraint)) {
                $result[$constraint] = true;
            }
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param string $seq_name name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        if (PEAR::isError($result = $db->exec('CREATE GENERATOR '.$sequence_name))) {
            return $result;
        }
        if (PEAR::isError($result = $db->exec('SET GENERATOR '.$sequence_name.' TO '.($start-1)))) {
            if (PEAR::isError($err = $db->dropSequence($seq_name))) {
                return $db->raiseError($result, null, null,
                    'Could not setup sequence start value and then it was not possible to drop it', __FUNCTION__);
            }
        }
        return $result;
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param string $seq_name name of the sequence to be dropped
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        $sequence_name = $db->quote($sequence_name, 'text');
        $query = "DELETE FROM RDB\$GENERATORS WHERE UPPER(RDB\$GENERATOR_NAME)=$sequence_name";
        return $db->exec($query);
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed array of sequence names on success, a MDB2 error on failure
     * @access public
     */
    function listSequences()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'SELECT RDB$GENERATOR_NAME FROM RDB$GENERATORS WHERE (RDB$SYSTEM_FLAG IS NULL OR RDB$SYSTEM_FLAG = 0)';
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        foreach ($table_names as $table_name) {
            $result[] = $this->_fixSequenceName($table_name);
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
}
?>