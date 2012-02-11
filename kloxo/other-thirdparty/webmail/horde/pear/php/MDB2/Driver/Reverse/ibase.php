<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2007 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Frank M. Kromann, Lorenzo Alberton     |
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
// $Id: ibase.php,v 1.79 2008/11/17 00:00:15 quipo Exp $
//

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 InterbaseBase driver for the reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author Lorenzo Alberton  <l.alberton@quipo.it>
 */
class MDB2_Driver_Reverse_ibase extends MDB2_Driver_Reverse_Common
{
    /**
     * Array for converting constant values to text values
     * @var    array
     * @access public
     */
    var $types = array(
        7   => 'smallint',
        8   => 'integer',
        9   => 'quad',
        10  => 'float',
        11  => 'd_float',
        12  => 'date',      //dialect 3 DATE
        13  => 'time',
        14  => 'char',
        16  => 'int64',
        27  => 'double',
        35  => 'timestamp', //DATE in older versions
        37  => 'varchar',
        40  => 'cstring',
        261 => 'blob',
    );

    /**
     * Array for converting constant values to text values
     * @var    array
     * @access public
     */
    var $subtypes = array(
        //char subtypes
        14 => array(
            0 => 'unspecified',
            1 => 'fixed', //BINARY data
        ),
        //blob subtypes
        261 => array(
            0 => 'unspecified',
            1 => 'text',
            2 => 'BLR', //Binary Language Representation
            3 => 'access control list',
            4 => 'reserved for future use',
            5 => 'encoded description of a table\'s current metadata',
            6 => 'description of multi-database transaction that finished irregularly',
        ),
        //smallint subtypes
        7 => array(
            0 => 'RDB$FIELD_TYPE',
            1 => 'numeric',
            2 => 'decimal',
        ),
        //integer subtypes
        8 => array(
            0 => 'RDB$FIELD_TYPE',
            1 => 'numeric',
            2 => 'decimal',
        ),
        //int64 subtypes
        16 => array(
            0 => 'RDB$FIELD_TYPE',
            1 => 'numeric',
            2 => 'decimal',
        ),
    );

    // {{{ getTableFieldDefinition()

    /**
     * Get the structure of a field into an array
     *
     * @param string $table_name name of table that should be used in method
     * @param string $field_name name of field that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableFieldDefinition($table_name, $field_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }

        list($schema, $table) = $this->splitTableSchema($table_name);

        $table = $db->quote(strtoupper($table), 'text');
        $field_name = $db->quote(strtoupper($field_name), 'text');
        $query = "SELECT RDB\$RELATION_FIELDS.RDB\$FIELD_NAME AS name,
                         RDB\$FIELDS.RDB\$FIELD_LENGTH AS \"length\",
                         RDB\$FIELDS.RDB\$FIELD_PRECISION AS \"precision\",
                         (RDB\$FIELDS.RDB\$FIELD_SCALE * -1) AS \"scale\",
                         RDB\$FIELDS.RDB\$FIELD_TYPE AS field_type_code,
                         RDB\$FIELDS.RDB\$FIELD_SUB_TYPE AS field_sub_type_code,
                         RDB\$RELATION_FIELDS.RDB\$DESCRIPTION AS description,
                         RDB\$RELATION_FIELDS.RDB\$NULL_FLAG AS null_flag,
                         RDB\$FIELDS.RDB\$DEFAULT_SOURCE AS default_source,
                         RDB\$CHARACTER_SETS.RDB\$CHARACTER_SET_NAME AS \"charset\",
                         RDB\$COLLATIONS.RDB\$COLLATION_NAME AS \"collation\"
                    FROM RDB\$FIELDS
               LEFT JOIN RDB\$RELATION_FIELDS ON RDB\$FIELDS.RDB\$FIELD_NAME = RDB\$RELATION_FIELDS.RDB\$FIELD_SOURCE
               LEFT JOIN RDB\$CHARACTER_SETS ON RDB\$FIELDS.RDB\$CHARACTER_SET_ID = RDB\$CHARACTER_SETS.RDB\$CHARACTER_SET_ID
               LEFT JOIN RDB\$COLLATIONS ON RDB\$FIELDS.RDB\$COLLATION_ID = RDB\$COLLATIONS.RDB\$COLLATION_ID
                   WHERE UPPER(RDB\$RELATION_FIELDS.RDB\$RELATION_NAME)=$table
                     AND UPPER(RDB\$RELATION_FIELDS.RDB\$FIELD_NAME)=$field_name;";
        $column = $db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($column)) {
            return $column;
        }
        if (empty($column)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table column', __FUNCTION__);
        }
        $column = array_change_key_case($column, CASE_LOWER);
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $column['name'] = strtolower($column['name']);
            } else {
                $column['name'] = strtoupper($column['name']);
            }
        }

        $column['type'] = array_key_exists((int)$column['field_type_code'], $this->types)
            ? $this->types[(int)$column['field_type_code']] : 'undefined';
        if ($column['field_sub_type_code']
            && array_key_exists((int)$column['field_type_code'], $this->subtypes)
            && array_key_exists($column['field_sub_type_code'], $this->subtypes[(int)$column['field_type_code']])
        ) {
            $column['field_sub_type'] = $this->subtypes[(int)$column['field_type_code']][$column['field_sub_type_code']];
        } else {
            $column['field_sub_type'] = null;
        }
        $mapped_datatype = $db->datatype->mapNativeDatatype($column);
        if (PEAR::isError($mapped_datatype)) {
            return $mapped_datatype;
        }
        list($types, $length, $unsigned, $fixed) = $mapped_datatype;
        $notnull = !empty($column['null_flag']);
        $default = $column['default_source'];
        if (is_null($default) && $notnull) {
            $default = ($types[0] == 'integer') ? 0 : '';
        }

        $definition[0] = array(
            'notnull'    => $notnull,
            'nativetype' => $column['type'],
            'charset'    => $column['charset'],
            'collation'  => $column['collation'],
        );
        if (!is_null($length)) {
            $definition[0]['length'] = $length;
        }
        if (!is_null($unsigned)) {
            $definition[0]['unsigned'] = $unsigned;
        }
        if (!is_null($fixed)) {
            $definition[0]['fixed'] = $fixed;
        }
        if ($default !== false) {
            $definition[0]['default'] = $default;
        }
        foreach ($types as $key => $type) {
            $definition[$key] = $definition[0];
            if ($type == 'clob' || $type == 'blob') {
                unset($definition[$key]['default']);
            }
            $definition[$key]['type'] = $type;
            $definition[$key]['mdb2type'] = $type;
        }
        return $definition;
    }

    // }}}
    // {{{ getTableIndexDefinition()

    /**
     * Get the structure of an index into an array
     *
     * @param string $table_name name of table that should be used in method
     * @param string $index_name name of index that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableIndexDefinition($table_name, $index_name, $format_index_name = true)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        list($schema, $table) = $this->splitTableSchema($table_name);

        $table = $db->quote(strtoupper($table), 'text');
        $query = "SELECT RDB\$INDEX_SEGMENTS.RDB\$FIELD_NAME AS field_name,
                         RDB\$INDICES.RDB\$DESCRIPTION AS description,
                         (RDB\$INDEX_SEGMENTS.RDB\$FIELD_POSITION + 1) AS field_position
                    FROM RDB\$INDEX_SEGMENTS
               LEFT JOIN RDB\$INDICES ON RDB\$INDICES.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
               LEFT JOIN RDB\$RELATION_CONSTRAINTS ON RDB\$RELATION_CONSTRAINTS.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
                   WHERE UPPER(RDB\$INDICES.RDB\$RELATION_NAME)=$table
                     AND UPPER(RDB\$INDICES.RDB\$INDEX_NAME)=%s
                     AND RDB\$RELATION_CONSTRAINTS.RDB\$CONSTRAINT_TYPE IS NULL
                ORDER BY RDB\$INDEX_SEGMENTS.RDB\$FIELD_POSITION";
        $index_name_mdb2 = $db->quote(strtoupper($db->getIndexName($index_name)), 'text');
        $result = $db->queryRow(sprintf($query, $index_name_mdb2));
        if (!PEAR::isError($result) && !is_null($result)) {
            // apply 'idxname_format' only if the query succeeded, otherwise
            // fallback to the given $index_name, without transformation
            $index_name = $index_name_mdb2;
        } else {
            $index_name = $db->quote(strtoupper($index_name), 'text');
        }
        $result = $db->query(sprintf($query, $index_name));
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $column_name = $row['field_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column_name = strtolower($column_name);
                } else {
                    $column_name = strtoupper($column_name);
                }
            }
            $definition['fields'][$column_name] = array(
                'position' => (int)$row['field_position'],
            );
            /*
            if (!empty($row['collation'])) {
                $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                    ? 'ascending' : 'descending');
            }
            */
        }

        $result->free();
        if (empty($definition)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table index', __FUNCTION__);
        }
        return $definition;
    }

    // }}}
    // {{{ getTableConstraintDefinition()

    /**
     * Get the structure of a constraint into an array
     *
     * @param string $table_name      name of table that should be used in method
     * @param string $constraint_name name of constraint that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableConstraintDefinition($table_name, $constraint_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        list($schema, $table) = $this->splitTableSchema($table_name);
        
        $table = $db->quote(strtoupper($table), 'text');
        $query = "SELECT rc.RDB\$CONSTRAINT_NAME,
                         s.RDB\$FIELD_NAME AS field_name,
                         CASE WHEN rc.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY' THEN 1 ELSE 0 END AS \"primary\",
                         CASE WHEN rc.RDB\$CONSTRAINT_TYPE = 'FOREIGN KEY' THEN 1 ELSE 0 END AS \"foreign\",
                         CASE WHEN rc.RDB\$CONSTRAINT_TYPE = 'UNIQUE'      THEN 1 ELSE 0 END AS \"unique\",
                         CASE WHEN rc.RDB\$CONSTRAINT_TYPE = 'CHECK'       THEN 1 ELSE 0 END AS \"check\",
                         i.RDB\$DESCRIPTION AS description,
                         CASE WHEN rc.RDB\$DEFERRABLE = 'NO' THEN 0 ELSE 1 END AS deferrable,
                         CASE WHEN rc.RDB\$INITIALLY_DEFERRED = 'NO' THEN 0 ELSE 1 END AS initiallydeferred,
                         refc.RDB\$UPDATE_RULE AS onupdate,
                         refc.RDB\$DELETE_RULE AS ondelete,
                         refc.RDB\$MATCH_OPTION AS \"match\",
                         i2.RDB\$RELATION_NAME AS references_table,
                         s2.RDB\$FIELD_NAME AS references_field,
                         (s.RDB\$FIELD_POSITION + 1) AS field_position
                    FROM RDB\$INDEX_SEGMENTS s
               LEFT JOIN RDB\$INDICES i ON i.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
               LEFT JOIN RDB\$RELATION_CONSTRAINTS rc ON rc.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
               LEFT JOIN RDB\$REF_CONSTRAINTS refc ON rc.RDB\$CONSTRAINT_NAME = refc.RDB\$CONSTRAINT_NAME
               LEFT JOIN RDB\$RELATION_CONSTRAINTS rc2 ON rc2.RDB\$CONSTRAINT_NAME = refc.RDB\$CONST_NAME_UQ
               LEFT JOIN RDB\$INDICES i2 ON i2.RDB\$INDEX_NAME = rc2.RDB\$INDEX_NAME
               LEFT JOIN RDB\$INDEX_SEGMENTS s2 ON i2.RDB\$INDEX_NAME = s2.RDB\$INDEX_NAME
                     AND s.RDB\$FIELD_POSITION = s2.RDB\$FIELD_POSITION
                   WHERE UPPER(i.RDB\$RELATION_NAME)=$table
                     AND UPPER(rc.RDB\$CONSTRAINT_NAME)=%s
                     AND rc.RDB\$CONSTRAINT_TYPE IS NOT NULL
                ORDER BY s.RDB\$FIELD_POSITION";
        $constraint_name_mdb2 = $db->quote(strtoupper($db->getIndexName($constraint_name)), 'text');
        $result = $db->queryRow(sprintf($query, $constraint_name_mdb2));
        if (!PEAR::isError($result) && !is_null($result)) {
            // apply 'idxname_format' only if the query succeeded, otherwise
            // fallback to the given $index_name, without transformation
            $constraint_name = $constraint_name_mdb2;
        } else {
            $constraint_name = $db->quote(strtoupper($constraint_name), 'text');
        }
        $result = $db->query(sprintf($query, $constraint_name));
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $column_name = $row['field_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column_name = strtolower($column_name);
                } else {
                    $column_name = strtoupper($column_name);
                }
            }
            $definition['fields'][$column_name] = array(
                'position' => (int)$row['field_position']
            );
            if ($row['foreign']) {
                $ref_column_name = $row['references_field'];
                $ref_table_name  = $row['references_table'];
                if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                    if ($db->options['field_case'] == CASE_LOWER) {
                        $ref_column_name = strtolower($ref_column_name);
                        $ref_table_name  = strtolower($ref_table_name);
                    } else {
                        $ref_column_name = strtoupper($ref_column_name);
                        $ref_table_name  = strtoupper($ref_table_name);
                    }
                }
                $definition['references']['table'] = $ref_table_name;
                $definition['references']['fields'][$ref_column_name] = array(
                    'position' => (int)$row['field_position']
                );
            }
            //collation?!?
            /*
            if (!empty($row['collation'])) {
                $definition['fields'][$field]['sorting'] = ($row['collation'] == 'A'
                    ? 'ascending' : 'descending');
            }
            */
            $lastrow = $row;
            // otherwise $row is no longer usable on exit from loop
        }
        $result->free();
        if (empty($definition)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }
        
        $definition['primary'] = (boolean)$lastrow['primary'];
        $definition['unique']  = (boolean)$lastrow['unique'];
        $definition['foreign'] = (boolean)$lastrow['foreign'];
        $definition['check']   = (boolean)$lastrow['check'];
        $definition['deferrable'] = (boolean)$lastrow['deferrable'];
        $definition['initiallydeferred'] = (boolean)$lastrow['initiallydeferred'];
        $definition['onupdate'] = $lastrow['onupdate'];
        $definition['ondelete'] = $lastrow['ondelete'];
        $definition['match']    = $lastrow['match'];
        
		return $definition;
    }

    // }}}
    // {{{ getTriggerDefinition()

    /**
     * Get the structure of a trigger into an array
     *
     * EXPERIMENTAL
     *
     * WARNING: this function is experimental and may change the returned value
     * at any time until labelled as non-experimental
     *
     * @param string    $trigger    name of trigger that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTriggerDefinition($trigger)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $trigger = $db->quote(strtoupper($trigger), 'text');
        $query = "SELECT RDB\$TRIGGER_NAME AS trigger_name,
                         RDB\$RELATION_NAME AS table_name,
                         RDB\$TRIGGER_SOURCE AS trigger_body,
                         CASE RDB\$TRIGGER_TYPE
                            WHEN 1 THEN 'BEFORE'
                            WHEN 2 THEN 'AFTER'
                            WHEN 3 THEN 'BEFORE'
                            WHEN 4 THEN 'AFTER'
                            WHEN 5 THEN 'BEFORE'
                            WHEN 6 THEN 'AFTER'
                         END AS trigger_type,
                         CASE RDB\$TRIGGER_TYPE
                            WHEN 1 THEN 'INSERT'
                            WHEN 2 THEN 'INSERT'
                            WHEN 3 THEN 'UPDATE'
                            WHEN 4 THEN 'UPDATE'
                            WHEN 5 THEN 'DELETE'
                            WHEN 6 THEN 'DELETE'
                         END AS trigger_event,
                         CASE RDB\$TRIGGER_INACTIVE
                            WHEN 1 THEN 0 ELSE 1
                         END AS trigger_enabled,
                         RDB\$DESCRIPTION AS trigger_comment
                    FROM RDB\$TRIGGERS
                   WHERE UPPER(RDB\$TRIGGER_NAME)=$trigger";
        $types = array(
            'trigger_name'    => 'text',
            'table_name'      => 'text',
            'trigger_body'    => 'clob',
            'trigger_type'    => 'text',
            'trigger_event'   => 'text',
            'trigger_comment' => 'text',
            'trigger_enabled' => 'boolean',
        );

        $def = $db->queryRow($query, $types, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($def)) {
            return $def;
        }

        $clob = $def['trigger_body'];
        if (!PEAR::isError($clob) && is_resource($clob)) {
            $value = '';
            while (!feof($clob)) {
                $data = fread($clob, 8192);
                $value.= $data;
            }
            $db->datatype->destroyLOB($clob);
            $def['trigger_body'] = $value;
        }

        return $def;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
     *
     * @param object|string  $result  MDB2_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_Driver_Common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
           return parent::tableInfo($result, $mode);
        }

        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $resource = MDB2::isResultCommon($result) ? $result->getResource() : $result;
        if (!is_resource($resource)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'Could not generate result resource', __FUNCTION__);
        }

        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $case_func = 'strtolower';
            } else {
                $case_func = 'strtoupper';
            }
        } else {
            $case_func = 'strval';
        }

        $count = @ibase_num_fields($resource);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        $db->loadModule('Datatype', null, true);
        for ($i = 0; $i < $count; $i++) {
            $info = @ibase_field_info($resource, $i);
            if (($pos = strpos($info['type'], '(')) !== false) {
                $info['type'] = substr($info['type'], 0, $pos);
            }
            $res[$i] = array(
                'table'  => $case_func($info['relation']),
                'name'   => $case_func($info['name']),
                'type'   => $info['type'],
                'length' => $info['length'],
                'flags'  => '',
            );
            $mdb2type_info = $db->datatype->mapNativeDatatype($res[$i]);
            if (PEAR::isError($mdb2type_info)) {
               return $mdb2type_info;
            }
            $res[$i]['mdb2type'] = $mdb2type_info[0][0];
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        return $res;
    }
}
?>