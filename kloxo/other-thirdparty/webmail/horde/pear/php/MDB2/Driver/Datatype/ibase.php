<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2007 Manuel Lemos, Tomas V.V.Cox,                 |
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
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// |         Lorenzo Alberton <l.alberton@quipo.it>                       |
// +----------------------------------------------------------------------+
//
// $Id: ibase.php,v 1.90 2008/11/09 18:41:32 quipo Exp $

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 Firebird/Interbase driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Driver_Datatype_ibase extends MDB2_Driver_Datatype_Common
{
    // {{{ _baseConvertResult()

    /**
     * General type conversion method
     *
     * @param mixed   $value refernce to a value to be converted
     * @param string  $type  specifies which type to convert to
     * @param boolean $rtrim [optional] when TRUE [default], apply rtrim() to text
     * @return object a MDB2 error on failure
     * @access protected
     */
    function _baseConvertResult($value, $type, $rtrim = true)
    {
        if (is_null($value)) {
            return null;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch ($type) {
        case 'text':
            $blob_info = @ibase_blob_info($value);
            if (is_array($blob_info) && $blob_info['length'] > 0) {
                //LOB => fetch into variable
                $clob = $this->_baseConvertResult($value, 'clob', $rtrim);
                if (!PEAR::isError($clob) && is_resource($clob)) {
                    $clob_value = '';
                    while (!feof($clob)) {
                        $clob_value .= fread($clob, 8192);
                    }
                    $this->destroyLOB($clob);
                }
                $value = $clob_value;
            }
            if ($rtrim) {
                $value = rtrim($value);
            }
            return $value;
        case 'timestamp':
            return substr($value, 0, strlen('YYYY-MM-DD HH:MM:SS'));
        }
        return parent::_baseConvertResult($value, $type, $rtrim);
    }

    // }}}
    // {{{ _getCharsetFieldDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to set the CHARACTER SET
     * of a field declaration to be used in statements like CREATE TABLE.
     *
     * @param string $charset   name of the charset
     * @return string  DBMS specific SQL code portion needed to set the CHARACTER SET
     *                 of a field declaration.
     */
    function _getCharsetFieldDeclaration($charset)
    {
        return 'CHARACTER SET '.$charset;
    }

    // }}}
    // {{{ _getCollationFieldDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to set the COLLATION
     * of a field declaration to be used in statements like CREATE TABLE.
     *
     * @param string $collation   name of the collation
     * @return string  DBMS specific SQL code portion needed to set the COLLATION
     *                 of a field declaration.
     */
    function _getCollationFieldDeclaration($collation)
    {
        return 'COLLATE '.$collation;
    }

    // }}}
    // {{{ getTypeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access public
     */
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch ($field['type']) {
        case 'text':
            $length = !empty($field['length'])
                ? $field['length'] : $db->options['default_text_field_length'];
            $fixed = !empty($field['fixed']) ? $field['fixed'] : false;
            return $fixed ? 'CHAR('.$length.')' : 'VARCHAR('.$length.')';
        case 'clob':
            return 'BLOB SUB_TYPE 1';
        case 'blob':
            return 'BLOB SUB_TYPE 0';
        case 'integer':
            return 'INT';
        case 'boolean':
            return 'SMALLINT';
        case 'date':
            return 'DATE';
        case 'time':
            return 'TIME';
        case 'timestamp':
            return 'TIMESTAMP';
        case 'float':
            return 'DOUBLE PRECISION';
        case 'decimal':
            $length = !empty($field['length']) ? $field['length'] : 18;
            $scale = !empty($field['scale']) ? $field['scale'] : $db->options['decimal_places'];
            return 'DECIMAL('.$length.','.$scale.')';
        }
        return '';
    }

    // }}}
    // {{{ _quoteLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @param bool $escape_wildcards if to escape escape wildcards
     * @return string text string that represents the given argument value in
     *      a DBMS specific format.
     * @access protected
     */
    function _quoteLOB($value, $quote, $escape_wildcards)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $connection = $db->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $close = true;
        if (is_resource($value)) {
            $close = false;
        } elseif (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
            if ($match[1] == 'file://') {
                $value = $match[2];
            }
            $value = @fopen($value, 'r');
        } else {
            $fp = @tmpfile();
            @fwrite($fp, $value);
            @rewind($fp);
            $value = $fp;
        }
        $blob_id = @ibase_blob_import($connection, $value);

        if ($close) {
            @fclose($value);
        }
        return $blob_id;
    }

    // }}}
    // {{{ _retrieveLOB()

    /**
     * retrieve LOB from the database
     *
     * @param array $lob array
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function _retrieveLOB(&$lob)
    {
        if (empty($lob['handle'])) {
            $lob['handle'] = @ibase_blob_open($lob['resource']);
            if (!$lob['handle']) {
                $db =& $this->getDBInstance();
                if (PEAR::isError($db)) {
                    return $db;
                }

                return $db->raiseError(null, null, null,
                    'Could not open fetched large object field', __FUNCTION__);
            }
        }
        $lob['loaded'] = true;
        return MDB2_OK;
    }

    // }}}
    // {{{ _readLOB()

    /**
     * Read data from large object input stream.
     *
     * @param array $lob array
     * @param blob $data reference to a variable that will hold data to be
     *      read from the large object input stream
     * @param int $length integer value that indicates the largest ammount of
     *      data to be read from the large object input stream.
     * @return mixed length on success, a MDB2 error on failure
     * @access protected
     */
    function _readLOB(&$lob, $length)
    {
        $data = @ibase_blob_get($lob['handle'], $length);
        if (!is_string($data)) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }

            return $db->raiseError(null, null, null,
                    'Unable to read LOB', __FUNCTION__);
        }
        return $data;
    }

    // }}}
    // {{{ _destroyLOB()

    /**
     * Free any resources allocated during the lifetime of the large object
     * handler object.
     *
     * @param array $lob array
     * @access protected
     */
    function _destroyLOB(&$lob)
    {
        if (isset($lob['handle'])) {
           @ibase_blob_close($lob['handle']);
            unset($lob['handle']);
        }
    }

    // }}}
    // {{{ patternEscapeString()

    /**
     * build string to define escape pattern string
     *
     * @access public
     *
     * @return string define escape pattern
     */
    function patternEscapeString()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return " ESCAPE '". $db->string_quoting['escape_pattern'] ."'";
    }

    // }}}
    // {{{ _mapNativeDatatype()

    /**
     * Maps a native array description of a field to a MDB2 datatype and length
     *
     * @param array  $field native field description
     * @return array containing the various possible types, length, sign, fixed
     * @access public
     */
    function _mapNativeDatatype($field)
    {
        $length = $field['length'];
        if ((int)$length <= 0) {
            $length = null;
        }
        $type = array();
        $unsigned = $fixed = null;
        $db_type = strtolower($field['type']);
        $field['field_sub_type'] = !empty($field['field_sub_type'])
            ? strtolower($field['field_sub_type']) : null;
        switch ($db_type) {
        case 'smallint':
        case 'integer':
        case 'int64':
            //these may be 'numeric' or 'decimal'
            if (isset($field['field_sub_type'])) {
                $field['type'] = $field['field_sub_type'];
                return $this->mapNativeDatatype($field);
            }
        case 'bigint':
        case 'quad':
            $type[] = 'integer';
            if ($length == '1') {
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', $field['name'])) {
                    $type = array_reverse($type);
                }
            }
            break;
        case 'varchar':
            $fixed = false;
        case 'char':
        case 'cstring':
            $type[] = 'text';
            if ($length == '1') {
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', $field['name'])) {
                    $type = array_reverse($type);
                }
            }
            if ($fixed !== false) {
                $fixed = true;
            }
            break;
        case 'date':
            $type[] = 'date';
            $length = null;
            break;
        case 'timestamp':
            $type[] = 'timestamp';
            $length = null;
            break;
        case 'time':
            $type[] = 'time';
            $length = null;
            break;
        case 'float':
        case 'double':
        case 'double precision':
        case 'd_float':
            $type[] = 'float';
            break;
        case 'decimal':
        case 'numeric':
            $type[] = 'decimal';
            $length = $field['precision'].','.$field['scale'];
            break;
        case 'blob':
            $type[] = ($field['field_sub_type'] == 'text') ? 'clob' : 'blob';
            $length = null;
            break;
        default:
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'unknown database attribute type: '.$db_type, __FUNCTION__);
        }

        if ((int)$length <= 0) {
            $length = null;
        }

        return array($type, $length, $unsigned, $fixed);
    }

    // }}}
}
?>