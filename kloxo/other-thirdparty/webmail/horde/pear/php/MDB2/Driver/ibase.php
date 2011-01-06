<?php
// vim: set et ts=4 sw=4 fdm=marker:
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
// $Id: ibase.php,v 1.224 2009/01/14 15:00:02 quipo Exp $

/**
 * MDB2 FireBird/InterBase driver
 *
 * @package MDB2
 * @category Database
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Driver_ibase extends MDB2_Driver_Common
{
    // {{{ properties

    var $string_quoting = array('start' => "'", 'end' => "'", 'escape' => "'", 'escape_pattern' => '\\');

    var $identifier_quoting = array('start' => '', 'end' => '', 'escape' => false);

    var $transaction_id = 0;

    var $query_parameters = array();

    var $query_parameter_values = array();

    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->phptype  = 'ibase';
        $this->dbsyntax = 'ibase';

        $this->supported['sequences'] = true;
        $this->supported['indexes'] = true;
        $this->supported['affected_rows'] = function_exists('ibase_affected_rows');
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['transactions'] = true;
        $this->supported['savepoints'] = true;
        $this->supported['current_id'] = true;
        $this->supported['limit_queries'] = 'emulated';
        $this->supported['LOBs'] = true;
        $this->supported['replace'] = false;
        $this->supported['sub_selects'] = true;
        $this->supported['triggers'] = true;
        $this->supported['auto_increment'] = true;
        $this->supported['primary_key'] = true;
        $this->supported['result_introspection'] = true;
        $this->supported['prepared_statements'] = true;
        $this->supported['identifier_quoting'] = false;
        $this->supported['pattern_escaping'] = true;
        $this->supported['new_link'] = false;

        $this->options['DBA_username'] = false;
        $this->options['DBA_password'] = false;
        $this->options['database_path'] = '';
        $this->options['database_extension'] = '.gdb';
        $this->options['server_version'] = '';
        $this->options['max_identifiers_length'] = 31;
    }

    // }}}
    // {{{ errorInfo()

    /**
     * This method is used to collect information about an error
     *
     * @param integer $error
     * @return array
     * @access public
     */
    function errorInfo($error = null)
    {
        $native_msg = @ibase_errmsg();

        if (function_exists('ibase_errcode')) {
            $native_code = @ibase_errcode();
        } else {
            // memo for the interbase php module hackers: we need something similar
            // to mysql_errno() to retrieve error codes instead of this ugly hack
            if (preg_match('/^([^0-9\-]+)([0-9\-]+)\s+(.*)$/', $native_msg, $m)) {
                $native_code = (int)$m[2];
            } else {
                $native_code = null;
            }
        }
        if (is_null($error)) {
            $error = MDB2_ERROR;
            if ($native_code) {
                // try to interpret Interbase error code (that's why we need ibase_errno()
                // in the interbase module to return the real error code)
                switch ($native_code) {
                case -204:
                    if (isset($m[3]) && is_int(strpos($m[3], 'Table unknown'))) {
                        $errno = MDB2_ERROR_NOSUCHTABLE;
                    }
                break;
                default:
                    static $ecode_map;
                    if (empty($ecode_map)) {
                        $ecode_map = array(
                            -104 => MDB2_ERROR_SYNTAX,
                            -150 => MDB2_ERROR_ACCESS_VIOLATION,
                            -151 => MDB2_ERROR_ACCESS_VIOLATION,
                            -155 => MDB2_ERROR_NOSUCHTABLE,
                            -157 => MDB2_ERROR_NOSUCHFIELD,
                            -158 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                            -170 => MDB2_ERROR_MISMATCH,
                            -171 => MDB2_ERROR_MISMATCH,
                            -172 => MDB2_ERROR_INVALID,
                            // -204 =>  // Covers too many errors, need to use regex on msg
                            -205 => MDB2_ERROR_NOSUCHFIELD,
                            -206 => MDB2_ERROR_NOSUCHFIELD,
                            -208 => MDB2_ERROR_INVALID,
                            -219 => MDB2_ERROR_NOSUCHTABLE,
                            -297 => MDB2_ERROR_CONSTRAINT,
                            -303 => MDB2_ERROR_INVALID,
                            -413 => MDB2_ERROR_INVALID_NUMBER,
                            -530 => MDB2_ERROR_CONSTRAINT,
                            -551 => MDB2_ERROR_ACCESS_VIOLATION,
                            -552 => MDB2_ERROR_ACCESS_VIOLATION,
                            // -607 =>  // Covers too many errors, need to use regex on msg
                            -625 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                            -803 => MDB2_ERROR_CONSTRAINT,
                            -804 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                            // -902 =>  // Covers too many errors, need to use regex on msg
                            -904 => MDB2_ERROR_CONNECT_FAILED,
                            -922 => MDB2_ERROR_NOSUCHDB,
                            -923 => MDB2_ERROR_CONNECT_FAILED,
                            -924 => MDB2_ERROR_CONNECT_FAILED
                        );
                    }
                    if (isset($ecode_map[$native_code])) {
                        $error = $ecode_map[$native_code];
                    }
                    break;
                }
            } else {
                static $error_regexps;
                if (!isset($error_regexps)) {
                    $error_regexps = array(
                        '/generator .* is not defined/'
                            => MDB2_ERROR_SYNTAX,  // for compat. w ibase_errcode()
                        '/table.*(not exist|not found|unknown)/i'
                            => MDB2_ERROR_NOSUCHTABLE,
                        '/table .* already exists/i'
                            => MDB2_ERROR_ALREADY_EXISTS,
                        '/unsuccessful metadata update .* failed attempt to store duplicate value/i'
                            => MDB2_ERROR_ALREADY_EXISTS,
                        '/unsuccessful metadata update .* not found/i'
                            => MDB2_ERROR_NOT_FOUND,
                        '/validation error for column .* value "\*\*\* null/i'
                            => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                        '/violation of [\w ]+ constraint/i'
                            => MDB2_ERROR_CONSTRAINT,
                        '/conversion error from string/i'
                            => MDB2_ERROR_INVALID_NUMBER,
                        '/no permission for/i'
                            => MDB2_ERROR_ACCESS_VIOLATION,
                        '/arithmetic exception, numeric overflow, or string truncation/i'
                            => MDB2_ERROR_INVALID,
                        '/feature is not supported/i'
                            => MDB2_ERROR_NOT_CAPABLE,
                    );
                }
                foreach ($error_regexps as $regexp => $code) {
                    if (preg_match($regexp, $native_msg, $m)) {
                        $error = $code;
                        break;
                    }
                }
            }
        }
        return array($error, $native_code, $native_msg);
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * Delimited identifiers are a nightmare with InterBase, so they're disabled
     *
     * @param string $str  identifier name to be quoted
     * @param bool   $check_option  check the 'quote_identifier' option
     *
     * @return string  quoted identifier string
     *
     * @access public
     */
    function quoteIdentifier($str, $check_option = false)
    {
        if ($check_option && !$this->options['quote_identifier']) {
            return $str;
        }
        return strtoupper($str);
    }

    // }}}
    // {{{ getConnection()

    /**
     * Returns a native connection
     *
     * @return  mixed   a valid MDB2 connection object,
     *                  or a MDB2 error object on error
     * @access  public
     */
    function getConnection()
    {
        $result = $this->connect();
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($this->in_transaction) {
            return $this->transaction_id;
        }
        return $this->connection;
    }

    // }}}
    // {{{ beginTransaction()

    /**
     * Start a transaction or set a savepoint.
     *
     * @param   string  name of a savepoint to set
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function beginTransaction($savepoint = null)
    {
        $this->debug('Starting transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!is_null($savepoint)) {
            if (!$this->in_transaction) {
                return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                    'savepoint cannot be released when changes are auto committed', __FUNCTION__);
            }
            $query = 'SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        } elseif ($this->in_transaction) {
            return MDB2_OK;  //nothing to do
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $result = @ibase_trans(IBASE_DEFAULT, $connection);
        if (!$result) {
            return $this->raiseError(null, null, null,
                'could not start a transaction', __FUNCTION__);
        }
        $this->transaction_id = $result;
        $this->in_transaction = true;
        return MDB2_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the database changes done during a transaction that is in
     * progress or release a savepoint. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after committing the pending changes.
     *
     * @param   string  name of a savepoint to release
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function commit($savepoint = null)
    {
        $this->debug('Committing transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'commit/release savepoint cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            $query = 'RELEASE SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        }

        if (!@ibase_commit($this->transaction_id)) {
            return $this->raiseError(null, null, null,
                'could not commit a transaction', __FUNCTION__);
        }
        $this->in_transaction = false;
        $this->transaction_id = 0;
        return MDB2_OK;
    }

    // }}}
    // {{{ rollback()

    /**
     * Cancel any database changes done during a transaction or since a specific
     * savepoint that is in progress. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after canceling the pending changes.
     *
     * @param   string  name of a savepoint to rollback to
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function rollback($savepoint = null)
    {
        $this->debug('Rolling back transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'rollback cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            $query = 'ROLLBACK TO SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        }

        if ($this->transaction_id && !@ibase_rollback($this->transaction_id)) {
            return $this->raiseError(null, null, null,
                'Could not rollback a pending transaction: '.@ibase_errmsg(), __FUNCTION__);
        }
        $this->in_transaction = false;
        $this->transaction_id = 0;
        return MDB2_OK;
    }

    // }}}
    // {{{ setTransactionIsolation()

    /**
     * Set the transacton isolation level.
     *
     * @param   string  standard isolation level (SQL-92)
     *                  READ UNCOMMITTED (allows dirty reads)
     *                  READ COMMITTED (prevents dirty reads)
     *                  REPEATABLE READ (prevents nonrepeatable reads)
     *                  SERIALIZABLE (prevents phantom reads)
     * @param   array some transaction options:
     *                  'wait' => 'WAIT' | 'NO WAIT'
     *                  'rw'   => 'READ WRITE' | 'READ ONLY'
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     * @since   2.1.1
     */
    function setTransactionIsolation($isolation, $options = array())
    {
        $this->debug('Setting transaction isolation level', __FUNCTION__, array('is_manip' => true));
        switch ($isolation) {
        case 'READ UNCOMMITTED':
            $ibase_isolation = 'READ COMMITTED RECORD_VERSION';
            break;
        case 'READ COMMITTED':
            $ibase_isolation = 'READ COMMITTED NO RECORD_VERSION';
            break;
        case 'REPEATABLE READ':
            $ibase_isolation = 'SNAPSHOT';
            break;
        case 'SERIALIZABLE':
            $ibase_isolation = 'SNAPSHOT TABLE STABILITY';
            break;
        default:
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'isolation level is not supported: '.$isolation, __FUNCTION__);
        }

        if (!empty($options['wait'])) {
            switch ($options['wait']) {
            case 'WAIT':
            case 'NO WAIT':
                $wait = $options['wait'];
                break;
            default:
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'wait option is not supported: '.$options['wait'], __FUNCTION__);
            }
        }

        if (!empty($options['rw'])) {
            switch ($options['rw']) {
            case 'READ ONLY':
            case 'READ WRITE':
                $rw = $options['wait'];
                break;
            default:
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'wait option is not supported: '.$options['rw'], __FUNCTION__);
            }
        }

        $query = "SET TRANSACTION $rw $wait ISOLATION LEVEL $ibase_isolation";
        return $this->_doQuery($query, true);
    }

    // }}}
    // {{{ getDatabaseFile($database_name)

    /**
     * Builds the string with path+dbname+extension
     *
     * @return string full database path+file
     * @access protected
     */
    function _getDatabaseFile($database_name)
    {
        if ($database_name == '') {
            return $database_name;
        }
        $ret = $this->options['database_path'] . $database_name;
        if (!preg_match('/\.[fg]db$/i', $database_name)) {
            $ret .= $this->options['database_extension'];
        }
        return $ret;
    }

    // }}}
    // {{{ _doConnect()

    /**
     * Does the grunt work of connecting to the database
     *
     * @return mixed connection resource on success, MDB2 Error Object on failure
     * @access protected
     */
    function _doConnect($username, $password, $database_name, $persistent = false)
    {
        if (!PEAR::loadExtension('interbase')) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'extension '.$this->phptype.' is not compiled into PHP', __FUNCTION__);
        }

        $database_file = $this->_getDatabaseFile($database_name);
        $dbhost  = $this->dsn['hostspec'] ?
            ($this->dsn['hostspec'].':'.$database_file) : $database_file;

        $params = array();
        $params[] = $dbhost;
        $params[] = !empty($username) ? $username : null;
        $params[] = !empty($password) ? $password : null;
        $params[] = isset($this->dsn['charset']) ? $this->dsn['charset'] : null;
        $params[] = isset($this->dsn['buffers']) ? $this->dsn['buffers'] : null;
        $params[] = isset($this->dsn['dialect']) ? $this->dsn['dialect'] : null;
        $params[] = isset($this->dsn['role'])    ? $this->dsn['role'] : null;

        $connect_function = $persistent ? 'ibase_pconnect' : 'ibase_connect';
        $connection = @call_user_func_array($connect_function, $params);
        if ($connection <= 0) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
                'unable to establish a connection', __FUNCTION__);
        }

       if (empty($this->dsn['disable_iso_date'])) {
            if (function_exists('ibase_timefmt')) {
                @ibase_timefmt("%Y-%m-%d %H:%M:%S", IBASE_TIMESTAMP);
                @ibase_timefmt("%Y-%m-%d", IBASE_DATE);
            } else {
                @ini_set("ibase.timestampformat", "%Y-%m-%d %H:%M:%S");
                //@ini_set("ibase.timeformat", "%H:%M:%S");
                @ini_set("ibase.dateformat", "%Y-%m-%d");
            }
       }

        return $connection;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return true on success, MDB2 Error Object on failure
     * @access public
     */
    function connect()
    {
        $database_file = $this->_getDatabaseFile($this->database_name);
        if (is_resource($this->connection)) {
            //if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
            if (MDB2::areEquals($this->connected_dsn, $this->dsn)
                && $this->connected_database_name == $database_file
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        if (empty($this->database_name)) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
            'unable to establish a connection', __FUNCTION__);
        }

        $connection = $this->_doConnect($this->dsn['username'],
                                        $this->dsn['password'],
                                        $this->database_name,
                                        $this->options['persistent']);
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $this->connection =& $connection;
        $this->connected_dsn = $this->dsn;
        $this->connected_database_name = $database_file;
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;
        $this->supported['limit_queries'] = ($this->dbsyntax == 'firebird') ? true : 'emulated';

        return MDB2_OK;
    }

    // }}}
    // {{{ databaseExists()

    /**
     * check if given database name is exists?
     *
     * @param string $name    name of the database that should be checked
     *
     * @return mixed true/false on success, a MDB2 error on failure
     * @access public
     */
    function databaseExists($name)
    {
        $database_file = $this->_getDatabaseFile($name);
        $result = file_exists($database_file);
        return $result;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @param  boolean $force if the disconnect should be forced even if the
     *                        connection is opened persistently
     * @return mixed true on success, false if not connected and error
     *               object on error
     * @access public
     */
    function disconnect($force = true)
    {
        if (is_resource($this->connection)) {
            if ($this->in_transaction) {
                $dsn = $this->dsn;
                $database_name = $this->database_name;
                $persistent = $this->options['persistent'];
                $this->dsn = $this->connected_dsn;
                $this->database_name = $this->connected_database_name;
                $this->options['persistent'] = $this->opened_persistent;
                $this->rollback();
                $this->dsn = $dsn;
                $this->database_name = $database_name;
                $this->options['persistent'] = $persistent;
            }

            if (!$this->opened_persistent || $force) {
                $ok = @ibase_close($this->connection);
                if (!$ok) {
                    return $this->raiseError(MDB2_ERROR_DISCONNECT_FAILED,
                           null, null, null, __FUNCTION__);
                }
            }
        } else {
            return false;
        }
        return parent::disconnect($force);
    }

    // }}}
    // {{{ standaloneQuery()

   /**
     * execute a query as DBA
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param boolean $is_manip  if the query is a manipulation query
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneQuery($query, $types = null, $is_manip = false)
    {
        $user = $this->options['DBA_username']? $this->options['DBA_username'] : $this->dsn['username'];
        $pass = $this->options['DBA_password']? $this->options['DBA_password'] : $this->dsn['password'];
        $connection = $this->_doConnect($user, $pass, $this->database_name, $this->options['persistent']);
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);
        
        $result =& $this->_doQuery($query, $is_manip, $connection);
        if (!PEAR::isError($result)) {
            $result = $this->_affectedRows($connection, $result);
        }

        @mysql_close($connection);
        return $result;
    }

    // }}}
    // {{{ _doQuery()

    /**
     * Execute a query
     * @param string $query  query
     * @param boolean $is_manip  if the query is a manipulation query
     * @param resource $connection
     * @param string $database_name
     * @return result or error object
     * @access protected
     */
    function &_doQuery($query, $is_manip = false, $connection = null, $database_name = null)
    {
        $this->last_query = $query;
        $result = $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        if ($this->getOption('disable_query')) {
            if ($is_manip) {
                return 0;
            }
            return null;
        }

        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        $result = @ibase_query($connection, $query);

        if ($result === false) {
            $err =& $this->raiseError(null, null, null,
                'Could not execute statement', __FUNCTION__);
            return $err;
        }

        $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }

    // }}}
    // {{{ _affectedRows()

    /**
     * Returns the number of rows affected
     *
     * @param resource $result
     * @param resource $connection
     * @return mixed MDB2 Error Object or the number of rows affected
     * @access private
     */
    function _affectedRows($connection, $result = null)
    {
        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        return (function_exists('ibase_affected_rows') ? @ibase_affected_rows($connection) : 0);
    }

    // }}}
    // {{{ _modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * @param string $query  query to modify
     * @param boolean $is_manip  if it is a DML query
     * @param integer $limit  limit the number of rows
     * @param integer $offset  start reading from given offset
     * @return string modified query
     * @access protected
     */
    function _modifyQuery($query, $is_manip, $limit, $offset)
    {
        if ($limit > 0 && $this->supports('limit_queries') === true) {
            $query = preg_replace('/^([\s(])*SELECT(?!\s*FIRST\s*\d+)/i',
                "SELECT FIRST $limit SKIP $offset", $query);
        }
        return $query;
    }

    // }}}
    // {{{ getServerVersion()

    /**
     * return version information about the server
     *
     * @param bool   $native  determines if the raw version string should be returned
     * @return mixed array/string with version information or MDB2 error object
     * @access public
     */
    function getServerVersion($native = false)
    {
        $server_info = false;
        if ($this->connected_server_info) {
            $server_info = $this->connected_server_info;
        } elseif ($this->options['server_version']) {
            $server_info = $this->options['server_version'];
        } else {
            $username = $this->options['DBA_username'] ? $this->options['DBA_username'] : $this->dsn['username'];
            $password = $this->options['DBA_password'] ? $this->options['DBA_password'] : $this->dsn['password'];
            $ibserv = @ibase_service_attach($this->dsn['hostspec'], $username, $password);
            $server_info = @ibase_server_info($ibserv, IBASE_SVC_SERVER_VERSION);
            @ibase_service_detach($ibserv);
        }
        if (!$server_info) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'Requires either "server_version" or "DBA_username"/"DBA_password" option', __FUNCTION__);
        }
        // cache server_info
        $this->connected_server_info = $server_info;
        if (!$native) {
            //WI-V1.5.3.4854 Firebird 1.5
            //WI-T2.1.0.16780 Firebird 2.1 Beta 2
            if (!preg_match('/-[VT]([\d\.]*)/', $server_info, $matches)) {
                return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                    'Could not parse version information:'.$server_info, __FUNCTION__);
            }
            $tmp = explode('.', $matches[1], 4);
            $server_info = array(
                'major' => isset($tmp[0]) ? $tmp[0] : null,
                'minor' => isset($tmp[1]) ? $tmp[1] : null,
                'patch' => isset($tmp[2]) ? $tmp[2] : null,
                'extra' => isset($tmp[3]) ? $tmp[3] : null,
                'native' => $server_info,
            );
        }
        return $server_info;
    }

    // }}}
    // {{{ prepare()

    /**
     * Prepares a query for multiple execution with execute().
     * With some database backends, this is emulated.
     * prepare() requires a generic query as string like
     * 'INSERT INTO numbers VALUES(?,?)' or
     * 'INSERT INTO numbers VALUES(:foo,:bar)'.
     * The ? and :name and are placeholders which can be set using
     * bindParam() and the query can be sent off using the execute() method.
     * The allowed format for :name can be set with the 'bindname_format' option.
     *
     * @param string $query the query to prepare
     * @param mixed   $types  array that contains the types of the placeholders
     * @param mixed   $result_types  array that contains the types of the columns in
     *                        the result set or MDB2_PREPARE_RESULT, if set to
     *                        MDB2_PREPARE_MANIP the query is handled as a manipulation query
     * @param mixed   $lobs   key (field) value (parameter) pair for all lob placeholders
     * @return mixed resource handle for the prepared query on success, a MDB2
     *        error on failure
     * @access public
     * @see bindParam, execute
     */
    function &prepare($query, $types = null, $result_types = null, $lobs = array())
    {
        if ($this->options['emulate_prepared']) {
            $obj =& parent::prepare($query, $types, $result_types, $lobs);
            return $obj;
        }
        $is_manip = ($result_types === MDB2_PREPARE_MANIP);
        $offset = $this->offset;
        $limit  = $this->limit;
        $this->offset = $this->limit = 0;
        $result = $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        $placeholder_type_guess = $placeholder_type = null;
        $question = '?';
        $colon = ':';
        $positions = array();
        $position = 0;
        while ($position < strlen($query)) {
            $q_position = strpos($query, $question, $position);
            $c_position = strpos($query, $colon, $position);
            if ($q_position && $c_position) {
                $p_position = min($q_position, $c_position);
            } elseif ($q_position) {
                $p_position = $q_position;
            } elseif ($c_position) {
                $p_position = $c_position;
            } else {
                break;
            }
            if (is_null($placeholder_type)) {
                $placeholder_type_guess = $query[$p_position];
            }
            
            $new_pos = $this->_skipDelimitedStrings($query, $position, $p_position);
            if (PEAR::isError($new_pos)) {
                return $new_pos;
            }
            if ($new_pos != $position) {
                $position = $new_pos;
                continue; //evaluate again starting from the new position
            }
            
            if ($query[$position] == $placeholder_type_guess) {
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                }
                if ($placeholder_type == ':') {
                    $regexp = '/^.{'.($position+1).'}('.$this->options['bindname_format'].').*$/s';
                    $parameter = preg_replace($regexp, '\\1', $query);
                    if ($parameter === '') {
                        $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                            'named parameter name must match "bindname_format" option', __FUNCTION__);
                        return $err;
                    }
                    $positions[] = $parameter;
                    $query = substr_replace($query, '?', $position, strlen($parameter)+1);
                } else {
                    $positions[] = count($positions);
                }
                $position = $p_position + 1;
            } else {
                $position = $p_position;
            }
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $statement = @ibase_prepare($connection, $query);
        if (!$statement) {
            $err =& $this->raiseError(null, null, null,
                'Could not create statement', __FUNCTION__);
            return $err;
        }

        $class_name = 'MDB2_Statement_'.$this->phptype;
        $obj = new $class_name($this, $statement, $positions, $query, $types, $result_types, $is_manip, $limit, $offset);
        $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'post', 'result' => $obj));
        return $obj;
    }

    // }}}
    // {{{ getSequenceName()

    /**
     * adds sequence name formatting to a sequence name
     *
     * @param string $sqn name of the sequence
     * @return string formatted sequence name
     * @access public
     */
    function getSequenceName($sqn)
    {
        return strtoupper(parent::getSequenceName($sqn));
    }

    // }}}
    // {{{ nextID()

    /**
     * Returns the next free id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @param boolean $ondemand when true the sequence is
     *                          automatic created, if it
     *                          not exists
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = 'SELECT GEN_ID('.$sequence_name.', 1) as the_value FROM RDB$DATABASE';
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->expectError('*');
        $result = $this->queryOne($query, 'integer');
        $this->popExpect();
        $this->popErrorHandling();
        if (PEAR::isError($result)) {
            if ($ondemand) {
                $this->loadModule('Manager', null, true);
                $result = $this->manager->createSequence($seq_name);
                if (PEAR::isError($result)) {
                    return $this->raiseError($result, null, null,
                        'on demand sequence could not be created', __FUNCTION__);
                } else {
                    return $this->nextID($seq_name, false);
                }
            }
        }
        return $result;
    }

    // }}}
    // {{{ lastInsertID()

    /**
     * Returns the autoincrement ID if supported or $id or fetches the current
     * ID in a sequence called: $table.(empty($field) ? '' : '_'.$field)
     *
     * @param string $table name of the table into which a new row was inserted
     * @param string $field name of the field into which a new row was inserted
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function lastInsertID($table = null, $field = null)
    {
        $seq = $table.(empty($field) ? '' : '_'.$field);
        return $this->currID($seq);
    }
    
    // }}}
    // {{{ currID()

    /**
     * Returns the current id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function currID($seq_name)
    {
        $sequence_name = $this->getSequenceName($seq_name);
        $query = 'SELECT GEN_ID('.$sequence_name.', 0) as the_value FROM RDB$DATABASE';
        $value = $this->queryOne($query);
        if (PEAR::isError($value)) {
            return $this->raiseError($value, null, null,
                'Unable to select from ' . $seq_name, __FUNCTION__);
        }
        if (!is_numeric($value)) {
            return $this->raiseError(MDB2_ERROR, null, null,
                'could not find value in sequence table', __FUNCTION__);
        }
        return $value;
    }

    // }}}
}

/**
 * MDB2 FireBird/InterBase result driver
 *
 * @package MDB2
 * @category Database
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Result_ibase extends MDB2_Result_Common
{
    // {{{ _skipLimitOffset()

    /**
     * Skip the first row of a result set.
     *
     * @param resource $result
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access protected
     */
    function _skipLimitOffset()
    {
        if ($this->db->supports('limit_queries') === true) {
            return true;
        }
        if ($this->limit) {
            if ($this->rownum > $this->limit) {
                return false;
            }
        }
        if ($this->offset) {
            while ($this->offset_count < $this->offset) {
                ++$this->offset_count;
                if (!is_array(@ibase_fetch_row($this->result))) {
                    $this->offset_count = $this->offset;
                    return false;
                }
            }
        }
        return true;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int  $fetchmode how the array data should be indexed
     * @param int  $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($this->result === true) {
            //query successfully executed, but without results...
            $null = null;
            return $null;
        }
        if (!$this->_skipLimitOffset()) {
            $null = null;
            return $null;
        }
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $row = @ibase_fetch_assoc($this->result);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
            $row = @ibase_fetch_row($this->result);
        }
        if (!$row) {
            if ($this->result === false) {
                $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
                return $err;
            }
            $null = null;
            return $null;
        }
        $mode = $this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL;
        $rtrim = false;
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            if (empty($this->types)) {
                $mode += MDB2_PORTABILITY_RTRIM;
            } else {
                $rtrim = true;
            }
        }
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row, $rtrim);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = &new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ _getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result.
     *
     * @return  mixed   Array variable that holds the names of columns as keys
     *                  or an MDB2 error on failure.
     *                  Some DBMS may not return any columns when the result set
     *                  does not contain any rows.
     * @access private
     */
    function _getColumnNames()
    {
        $columns = array();
        $numcols = $this->numCols();
        if (PEAR::isError($numcols)) {
            return $numcols;
        }
        for ($column = 0; $column < $numcols; $column++) {
            $column_info = @ibase_field_info($this->result, $column);
            $columns[$column_info['alias']] = $column;
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_change_key_case($columns, $this->db->options['field_case']);
        }
        return $columns;
    }

    // }}}
    // {{{ numCols()

    /**
     * Count the number of columns returned by the DBMS in a query result.
     *
     * @return mixed integer value with the number of columns, a MDB2 error
     *      on failure
     * @access public
     */
    function numCols()
    {
        if ($this->result === true) {
            //query successfully executed, but without results...
            return 0;
        }

        if (!is_resource($this->result)) {
            return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'numCols(): not a valid ibase resource', __FUNCTION__);
        }
        $cols = @ibase_num_fields($this->result);
        if (is_null($cols)) {
            if ($this->result === false) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            } elseif (is_null($this->result)) {
                return count($this->types);
            }
            return $this->db->raiseError(null, null, null,
                'Could not get column count', __FUNCTION__);
        }
        return $cols;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        if (is_resource($this->result) && $this->db->connection) {
            $free = @ibase_free_result($this->result);
            if ($free === false) {
                return $this->db->raiseError(null, null, null,
                    'Could not free result', __FUNCTION__);
            }
        }
        $this->result = false;
        return MDB2_OK;
    }

    // }}}
}

/**
 * MDB2 FireBird/InterBase buffered result driver
 *
 * @package MDB2
 * @category Database
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_BufferedResult_ibase extends MDB2_Result_ibase
{
    // {{{ class vars

    var $buffer;
    var $buffer_rownum = - 1;

    // }}}
    // {{{ _fillBuffer()

    /**
     * Fill the row buffer
     *
     * @param int $rownum   row number upto which the buffer should be filled
     *                      if the row number is null all rows are ready into the buffer
     * @return boolean true on success, false on failure
     * @access protected
     */
    function _fillBuffer($rownum = null)
    {
        if (isset($this->buffer) && is_array($this->buffer)) {
            if (is_null($rownum)) {
                if (!end($this->buffer)) {
                    return false;
                }
            } elseif (isset($this->buffer[$rownum])) {
                return (bool) $this->buffer[$rownum];
            }
        }

        if (!$this->_skipLimitOffset()) {
            return false;
        }

        $buffer = true;
        while ((is_null($rownum) || $this->buffer_rownum < $rownum)
            && (!$this->limit || $this->buffer_rownum < $this->limit)
            && ($buffer = @ibase_fetch_row($this->result))
        ) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = $buffer;
        }

        if (!$buffer) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = false;
            return false;
        } elseif ($this->limit && $this->buffer_rownum >= $this->limit) {
            ++$this->buffer_rownum;
            $this->buffer[$this->buffer_rownum] = false;
        }
        return true;
    }

    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($this->result === true || is_null($this->result)) {
            //query successfully executed, but without results...
            $null = null;
            return $null;
        }
        if ($this->result === false) {
            $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
            return $err;
        }
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        $target_rownum = $this->rownum + 1;
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if (!$this->_fillBuffer($target_rownum)) {
            $null = null;
            return $null;
        }
        $row = $this->buffer[$target_rownum];
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $column_names = $this->getColumnNames();
            foreach ($column_names as $name => $i) {
                $column_names[$name] = $row[$i];
            }
            $row = $column_names;
        }
        $mode = $this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL;
        $rtrim = false;
        if ($this->db->options['portability'] & MDB2_PORTABILITY_RTRIM) {
            if (empty($this->types)) {
                $mode += MDB2_PORTABILITY_RTRIM;
            } else {
                $rtrim = true;
            }
        }
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row, $rtrim);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = &new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ seek()

    /**
     * Seek to a specific row in a result set
     *
     * @param int    $rownum    number of the row where the data can be found
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function seek($rownum = 0)
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        }
        $this->rownum = $rownum - 1;
        return MDB2_OK;
    }

    // }}}
    // {{{ valid()

    /**
     * Check if the end of the result set has been reached
     *
     * @return mixed true or false on sucess, a MDB2 error on failure
     * @access public
     */
    function valid()
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        } elseif (is_null($this->result)) {
            return true;
        }
        if ($this->_fillBuffer($this->rownum + 1)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ numRows()

    /**
     * Returns the number of rows in a result object
     *
     * @return mixed MDB2 Error Object or the number of rows
     * @access public
     */
    function numRows()
    {
        if ($this->result === false) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        } elseif (is_null($this->result)) {
            return 0;
        }
        $this->_fillBuffer();
        return $this->buffer_rownum;
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with $result.
     *
     * @return boolean true on success, false if $result is invalid
     * @access public
     */
    function free()
    {
        $this->buffer = null;
        $this->buffer_rownum = null;
        return parent::free();
    }

    // }}}
}

/**
 * MDB2 FireBird/InterBase statement driver
 *
 * @package MDB2
 * @category Database
 * @author  Lorenzo Alberton <l.alberton@quipo.it>
 */
class MDB2_Statement_ibase extends MDB2_Statement_Common
{
    // {{{ _execute()

    /**
     * Execute a prepared query statement helper method.
     *
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     *
     * @return mixed MDB2_Result or integer (affected rows) on success,
     *               a MDB2 error on failure
     * @access private
     */
    function &_execute($result_class = true, $result_wrap_class = false)
    {
        if (is_null($this->statement)) {
            $result =& parent::_execute($result_class, $result_wrap_class);
            return $result;
        }
        $this->db->last_query = $this->query;
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'pre', 'parameters' => $this->values));
        if ($this->db->getOption('disable_query')) {
            $result = $this->is_manip ? 0 : null;
            return $result;
        }

        $connection = $this->db->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }

        $parameters = array(0 => $this->statement);
        foreach ($this->positions as $parameter) {
            if (!array_key_exists($parameter, $this->values)) {
                return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
            }
            $value = $this->values[$parameter];
            $type = !empty($this->types[$parameter]) ? $this->types[$parameter] : null;
            $quoted = $this->db->quote($value, $type, false);
            if (PEAR::isError($quoted)) {
                return $quoted;
            }
            $parameters[] = $quoted;
        }

        $result = @call_user_func_array('ibase_execute', $parameters);
        if ($result === false) {
            $err =& $this->db->raiseError(null, null, null,
                'Could not execute statement', __FUNCTION__);
            return $err;
        }

        if ($this->is_manip) {
            $affected_rows = $this->db->_affectedRows($connection);
            return $affected_rows;
        }

        $result =& $this->db->_wrapResult($result, $this->result_types,
            $result_class, $result_wrap_class, $this->limit, $this->offset);
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }

    // }}}

    // }}}
    // {{{ free()

    /**
     * Release resources allocated for the specified prepared query.
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function free()
    {
        if (is_null($this->positions)) {
            return $this->db->raiseError(MDB2_ERROR, null, null,
                'Prepared statement has already been freed', __FUNCTION__);
        }
        $result = MDB2_OK;

        if (!is_null($this->statement) && !@ibase_free_query($this->statement)) {
            $result = $this->db->raiseError(null, null, null,
                'Could not free statement', __FUNCTION__);
        }

        parent::free();
        return $result;
    }
}
?>
