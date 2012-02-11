<?php
/**
 * Net_IMAP provides an implementation of the IMAP protocol
 *
 * PHP Version 4
 *
 * @category  Networking
 * @package   Net_IMAP
 * @author    Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 * @copyright 1997-2003 The PHP Group
 * @license   PHP license
 * @version   CVS: $Id: IMAPProtocol.php,v 1.25 2008/03/09 22:47:35 hudeldudel Exp $
 * @link      http://pear.php.net/package/Net_IMAP
 */

/**
 * Net_IMAP requires Net_Socket
 */
require_once 'Net/Socket.php';


/**
 * Provides an implementation of the IMAP protocol using PEAR's
 * Net_Socket:: class.
 *
 * @category Networking
 * @package  Net_IMAP
 * @author   Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 * @license  PHP license
 * @link     http://pear.php.net/package/Net_IMAP
 */
class Net_IMAPProtocol
{
    /**
     * The auth methods this class support
     * @var array
     */
    var $supportedAuthMethods = array('DIGEST-MD5', 'CRAM-MD5', 'LOGIN');


    /**
     * The auth methods this class support
     * @var array
     */
    var $supportedSASLAuthMethods = array('DIGEST-MD5', 'CRAM-MD5');


    /**
     * _serverAuthMethods
     * @var boolean
     */
    var $_serverAuthMethods = null;


    /**
     * The the current mailbox
     * @var string
     */
    var $currentMailbox = 'INBOX';


    /**
     * The socket resource being used to connect to the IMAP server.
     * @var resource
     */
    var $_socket = null;


    /**
     * The timeout for the connection to the IMAP server.
     * @var int
     */
    var $_timeout = null;


    /**
     * The options for SSL/TLS connection 
     * (see documentation for stream_context_create)
     * @var array
     */
    var $_streamContextOptions = null;


    /**
     * To allow class debuging
     * @var boolean
     */
    var $_debug = false;
    var $dbgDialog = '';


    /**
     * Print error messages
     * @var boolean
     */
    var $_printErrors = false;


    /**
     * Command Number
     * @var int
     */
    var $_cmd_counter = 1;


    /**
     * Command Number for IMAP commands
     * @var int
     */
    var $_lastCmdID = 1;


    /**
     * Command Number
     * @var boolean
     */
    var $_unParsedReturn = false;


    /**
     * _connected: checks if there is a connection made to a imap server or not
     * @var boolean
     */
    var $_connected = false;


    /**
     * Capabilities
     * @var boolean
     */
    var $_serverSupportedCapabilities = null;


    /**
     * Use UTF-7 funcionallity
     * @var boolean
     */
    var $_useUTF_7 = true;



    /**
     * Constructor
     *
     * Instantiates a new Net_IMAP object.
     *
     * @since  1.0
     */
    function Net_IMAPProtocol()
    {
        $this->_socket = new Net_Socket();

        /*
         * Include the Auth_SASL package.  If the package is not available,
         * we disable the authentication methods that depend upon it.
         */


        if ((@include_once 'Auth/SASL.php') == false) {
            foreach ($this->supportedSASLAuthMethods as $SASLMethod) {
                $pos = array_search($SASLMethod, $this->supportedAuthMethods);
                unset($this->supportedAuthMethods[$pos]);
            }
        }
    }



    /**
     * Attempt to connect to the IMAP server.
     *
     * @param string $host Hostname of the IMAP server
     * @param int    $port Port of the IMAP server (default = 143)
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since  1.0
     */
    function cmdConnect($host= 'localhost', $port = 143)
    {
        if ($this->_connected) {
            return new PEAR_Error('already connected, logout first!');
        }
        if (PEAR::isError($error = $this->_socket->connect($host, 
                                                           $port, 
                                                           null, 
                                                           $this->_timeout, 
                                                           $this->_streamContextOptions))) {
            return $error;
        }
        if (PEAR::isError($this->_getRawResponse())) {
            return new PEAR_Error('unable to open socket');
        }
        $this->_connected = true;
        return true;
    }



    /**
     * get the cmd ID
     *
     * @return string Returns the CmdID and increment the counter
     *
     * @access private
     * @since  1.0
     */
    function _getCmdId()
    {
        $this->_lastCmdID = 'A000' . $this->_cmd_counter;
        $this->_cmd_counter++;
        return $this->_lastCmdID;
    }



    /**
     * get the last cmd ID
     *
     * @return string Returns the last cmdId
     *
     * @access public
     * @since  1.0
     */
    function getLastCmdId()
    {
        return $this->_lastCmdID;
    }



    /**
     * get current mailbox name
     *
     * @return string Returns the current mailbox
     *
     * @access public
     * @since  1.0
     */
    function getCurrentMailbox()
    {
        return $this->currentMailbox;
    }



    /**
     * Sets the debuging information on or off
     *
     * @param boolean $debug Turn debug on (true) or off (false)
     *
     * @return nothing
     * @access public
     * @since  1.0
     */
    function setDebug($debug = true)
    {
        $this->_debug = $debug;
    }



    /**
     * get the debug dialog
     *
     * @return string debug dialog
     * @access public
     */
    function getDebugDialog()
    {
        return $this->dbgDialog;
    }



    /**
     * Sets printed output of errors on or of
     *
     * @param boolean $printErrors true to turn on, 
     *                             false to turn off printed output
     * 
     * @return nothing
     * @access public
     * @since 1.1
     */
    function setPrintErrors($printErrors = true)
    {
        $this->_printErrors = $printErrors;
    }



    /**
     * Send the given string of data to the server.
     *
     * @param string $data The string of data to send.
     *
     * @return mixed True on success or a PEAR_Error object on failure.
     *
     * @access private
     * @since 1.0
     */
    function _send($data)
    {
        if ($this->_socket->eof()) {
            return new PEAR_Error('Failed to write to socket: (connection lost!)');
        }
        if (PEAR::isError($error = $this->_socket->write($data))) {
            return new PEAR_Error('Failed to write to socket: ' 
                                  . $error->getMessage());
        }

        if ($this->_debug) {
            // C: means this data was sent by  the client (this class)
            echo 'C: ' . $data;
            $this->dbgDialog .= 'C: ' . $data;
        }
        return true;
    }

    /**
     * Receive the given string of data from the server.
     *
     * @return mixed a line of response on success or a PEAR_Error object on failure.
     *
     * @access private
     * @since 1.0
     */
    function _recvLn()
    {
        if (PEAR::isError($this->lastline = $this->_socket->gets(8192))) {
            return new PEAR_Error('Failed to write to socket: ' 
                                  . $this->lastline->getMessage());
        }
        if ($this->_debug) {
            // S: means this data was sent by  the IMAP Server
            echo 'S: ' . $this->lastline;
            $this->dbgDialog .= 'S: ' . $this->lastline;
        }
        if ($this->lastline == '') {
            return new PEAR_Error('Failed to receive from the  socket: ');
        }
        return $this->lastline;
    }



    /**
     * Send a command to the server with an optional string of arguments.
     * A carriage return / linefeed (CRLF) sequence will be appended to each
     * command string before it is sent to the IMAP server.
     *
     * @param string $commandId The IMAP cmdID to send to the server.
     * @param string $command   The IMAP command to send to the server.
     * @param string $args      A string of optional arguments to append
     *                          to the command.
     *
     * @return mixed The result of the _send() call.
     *
     * @access private
     * @since 1.0
     */
    function _putCMD($commandId , $command, $args = '')
    {
        if (!empty($args)) {
            return $this->_send($commandId 
                                . ' ' 
                                . $command 
                                . ' ' 
                                . $args 
                                . "\r\n");
        }
        return $this->_send($commandId . ' ' . $command . "\r\n");
    }



    /**
     * Get a response from the server with an optional string of commandID.
     * A carriage return / linefeed (CRLF) sequence will be appended to each
     * command string before it is sent to the IMAP server.
     *
     * @param string $commandId The IMAP commandid retrive from the server.
     *
     * @return string The result response.
     * @access private
     */
    function _getRawResponse($commandId = '*')
    {
        $arguments = '';
        while (!PEAR::isError($this->_recvLn())) {
            $reply_code = strtok($this->lastline, ' ');
            $arguments .= $this->lastline;
            if (!(strcmp($commandId, $reply_code))) {
                return $arguments;
            }
        }
        return $arguments;
    }



     /**
     * get the "returning of the unparsed response" feature status
     *
     * @return boolean return if the unparsed response is returned or not
     *
     * @access public
     * @since  1.0
     *
     */
    function getUnparsedResponse()
    {
        return $this->_unParsedReturn;
    }



    /**
     * set the options for a SSL/TLS connection 
     * (see documentation for stream_context_create)
     *
     * @param array $options The options for the SSL/TLS connection
     *
     * @return nothing
     * @access public
     * @since  1.1
     */
    function setStreamContextOptions($options)
    {
        $this->_streamContextOptions = $options;
    }



    /**
     * set the the timeout for the connection to the IMAP server.
     *
     * @param int $timeout The timeout
     *
     * @return nothing
     * @access public
     * @since  1.1
     */
    function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }



    /**
     * set the "returning of the unparsed response" feature on or off
     *
     * @param boolean $status true: feature is on
     *
     * @return nothing
     * @access public
     * @since  1.0
     */
    function setUnparsedResponse($status)
    {
        $this->_unParsedReturn = $status;
    }



    /**
     * Attempt to login to the iMAP server.
     *
     * @param string $uid The userid to authenticate as.
     * @param string $pwd The password to authenticate with.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdLogin($uid, $pwd)
    {
        $param = '"' . $uid . '" "'. $pwd .'"';
        return $this->_genericCommand('LOGIN', $param);
    }



    /**
     * Attempt to authenticate to the iMAP server.
     *
     * @param string $uid        The userid to authenticate as.
     * @param string $pwd        The password to authenticate with.
     * @param string $userMethod The cmdID.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdAuthenticate($uid, $pwd, $userMethod = null)
    {
        if (!$this->_connected) {
            return new PEAR_Error('not connected!');
        }

        $cmdid = $this->_getCmdId();

        if (PEAR::isError($method = $this->_getBestAuthMethod($userMethod))) {
            return $method;
        }


        switch ($method) {
        case 'DIGEST-MD5':
            $result = $this->_authDigestMD5($uid, $pwd, $cmdid);
            break;
        case 'CRAM-MD5':
            $result = $this->_authCramMD5($uid, $pwd, $cmdid);
            break;
        case 'LOGIN':
            $result = $this->_authLOGIN($uid, $pwd, $cmdid);
            break;
        default:
            $result = new PEAR_Error($method 
                                     . ' is not a supported authentication'
                                     . ' method');
            break;
        }

        $args = $this->_getRawResponse($cmdid);
        return $this->_genericImapResponseParser($args, $cmdid);

    }



    /** 
     * Authenticates the user using the DIGEST-MD5 method.
     *
     * @param string $uid   The userid to authenticate as.
     * @param string $pwd   The password to authenticate with.
     * @param string $cmdid The cmdID.
     *
     * @return array Returns an array containing the response
     * @access private
     * @since  1.0
     */
    function _authDigestMD5($uid, $pwd, $cmdid)
    {
        if (PEAR::isError($error = $this->_putCMD($cmdid,
                                                  'AUTHENTICATE',
                                                  'DIGEST-MD5'))) {
            return $error;
        }

        if (PEAR::isError($args = $this->_recvLn())) {
            return $args;
        }

        $this->_getNextToken($args, $plus);
        $this->_getNextToken($args, $space);
        $this->_getNextToken($args, $challenge);

        $challenge = base64_decode($challenge);
        $digest    = &Auth_SASL::factory('digestmd5');
        $auth_str  = base64_encode($digest->getResponse($uid, 
                                                        $pwd, 
                                                        $challenge,
                                                        'localhost', 
                                                        'imap'));

        if (PEAR::isError($error = $this->_send($auth_str . "\r\n"))) {
            return $error;
        }

        if (PEAR::isError($args = $this->_recvLn())) {
            return $args;
        }

        // We don't use the protocol's third step because IMAP doesn't allow
        // subsequent authentication, so we just silently ignore it.
        if (PEAR::isError($error = $this->_send("\r\n"))) {
            return $error;
        }
    }



    /**
     * Authenticates the user using the CRAM-MD5 method.
     *
     * @param string $uid   The userid to authenticate as.
     * @param string $pwd   The password to authenticate with.
     * @param string $cmdid The cmdID.
     *
     * @return array Returns an array containing the response
     * @access private
     * @since 1.0
     */
    function _authCramMD5($uid, $pwd, $cmdid)
    {
        if (PEAR::isError($error = $this->_putCMD($cmdid,
                                                  'AUTHENTICATE',
                                                  'CRAM-MD5'))) {
            return $error;
        }

        if (PEAR::isError($args = $this->_recvLn())) {
            return $args;
        }

        $this->_getNextToken($args, $plus);
        $this->_getNextToken($args, $space);
        $this->_getNextToken($args, $challenge);

        $challenge = base64_decode($challenge);
        $cram      = &Auth_SASL::factory('crammd5');
        $auth_str  = base64_encode($cram->getResponse($uid, 
                                                      $pwd,
                                                      $challenge));

        if (PEAR::isError($error = $this->_send($auth_str . "\r\n"))) {
            return $error;
        }
    }



    /**
     * Authenticates the user using the LOGIN method.
     *
     * @param string $uid   The userid to authenticate as.
     * @param string $pwd   The password to authenticate with.
     * @param string $cmdid The cmdID.
     *
     * @return array Returns an array containing the response
     * @access private
     * @since 1.0
     */
    function _authLOGIN($uid, $pwd, $cmdid)
    {
        if (PEAR::isError($error = $this->_putCMD($cmdid,
                                                  'AUTHENTICATE',
                                                  'LOGIN'))) {
            return $error;
        }

        if (PEAR::isError($args = $this->_recvLn())) {
            return $args;
        }

        $this->_getNextToken($args, $plus);
        $this->_getNextToken($args, $space);
        $this->_getNextToken($args, $challenge);

        $challenge = base64_decode($challenge);
        $auth_str  = base64_encode($uid);

        if (PEAR::isError($error = $this->_send($auth_str . "\r\n"))) {
            return $error;
        }

        if (PEAR::isError($args = $this->_recvLn())) {
            return $args;
        }

        $auth_str = base64_encode($pwd);

        if (PEAR::isError($error = $this->_send($auth_str . "\r\n"))) {
            return $error;
        }
    }



    /**
     * Returns the name of the best authentication method that the server
     * has advertised.
     *
     * @param string $userMethod If !=null, authenticate with this 
     *                           method ($userMethod).
     *
     * @return mixed Returns a string containing the name of the best
     *               supported authentication method or a PEAR_Error object
     *               if a failure condition is encountered.
     * @access private
     * @since 1.0
     */
    function _getBestAuthMethod($userMethod = null)
    {
        $this->cmdCapability();

        if ($userMethod != null) {
            $methods   = array();
            $methods[] = $userMethod;
        } else {
            $methods = $this->supportedAuthMethods;
        }

        if (($methods != null) && ($this->_serverAuthMethods != null)) {
            foreach ($methods as $method) {
                if (in_array($method, $this->_serverAuthMethods)) {
                    return $method;
                }
            }
            $serverMethods = implode(',', $this->_serverAuthMethods);
            $myMethods     = implode(',', $this->supportedAuthMethods);

            return new PEAR_Error($method . ' NOT supported authentication'
                                  . ' method! This IMAP server supports these'
                                  . ' methods: ' . $serverMethods . ', but I'
                                  . ' support ' . $myMethods);
        } else {
            return new PEAR_Error('This IMAP server don\'t support any Auth'
                                  . ' methods');
        }
    }



    /**
     * Attempt to disconnect from the iMAP server.
     *
     * @return array Returns an array containing the response
     *
     * @access public
     * @since  1.0
     */
    function cmdLogout()
    {
        if (!$this->_connected) {
            return new PEAR_Error('not connected!');
        }

        if (PEAR::isError($args = $this->_genericCommand('LOGOUT'))) {
            return $args;
        }
        if (PEAR::isError($this->_socket->disconnect())) {
            return new PEAR_Error('socket disconnect failed');
        }

        return $args;
        // not for now
        //return $this->_genericImapResponseParser($args,$cmdid);
    }



    /**
     * Send the NOOP command.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdNoop()
    {
        return $this->_genericCommand('NOOP');
    }



    /**
     * Send the CHECK command.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdCheck()
    {
        return $this->_genericCommand('CHECK');
    }



    /**
     * Send the  Select Mailbox Command
     *
     * @param string $mailbox The mailbox to select.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdSelect($mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        if (!PEAR::isError($ret = $this->_genericCommand('SELECT', 
                                                         $mailbox_name))) {
            $this->currentMailbox = $mailbox;
        }
        return $ret;
    }



    /**
     * Send the  EXAMINE  Mailbox Command
     *
     * @param string $mailbox The mailbox to examine.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdExamine($mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        $ret          = $this->_genericCommand('EXAMINE', $mailbox_name);
        $parsed       = '';

        if (isset($ret['PARSED'])) {
            for ($i=0; $i<count($ret['PARSED']); $i++) {
                $command               = $ret['PARSED'][$i]['EXT'];
                $parsed[key($command)] = $command[key($command)];
            }
        }

        return array('PARSED'   => $parsed, 
                     'RESPONSE' => $ret['RESPONSE']);
    }



    /**
     * Send the  CREATE Mailbox Command
     *
     * @param string $mailbox The mailbox to create.
     * @param array  $options Options to pass to create
     *
     * @return array Returns an array containing the response
     * @access public
     * @since 1.0
     */
    function cmdCreate($mailbox, $options = null)
    {
        $args         = '';
        $mailbox_name = $this->_createQuotedString($mailbox);
        $args         = $this->_getCreateParams($options);

        return $this->_genericCommand('CREATE', $mailbox_name . $args);
    }



    /**
     * Send the  RENAME Mailbox Command
     *
     * @param string $mailbox     The old mailbox name.
     * @param string $new_mailbox The new (renamed) mailbox name.
     * @param array  $options     options to pass to create
     *
     * @return array Returns an array containing the response
     * @access public
     * @since  1.0
     */
    function cmdRename($mailbox, $new_mailbox, $options = null)
    {
        $mailbox_name     = $this->_createQuotedString($mailbox);
        $new_mailbox_name = $this->_createQuotedString($new_mailbox);
        $args             = $this->_getCreateParams($options);

        return $this->_genericCommand('RENAME', 
                                      $mailbox_name . ' ' . $new_mailbox_name
                                      . $args);
    }



    /**
     * Send the  DELETE Mailbox Command
     *
     * @param string $mailbox The mailbox name to delete.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since 1.0
     */
    function cmdDelete($mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        return $this->_genericCommand('DELETE', $mailbox_name);
    }



    /**
     * Send the  SUSCRIBE  Mailbox Command
     *
     * @param string $mailbox The mailbox name to suscribe.
     *
     * @return array Returns an array containing the response
     * @access public
     * @since 1.0
     */
    function cmdSubscribe($mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        return $this->_genericCommand('SUBSCRIBE', $mailbox_name);
    }



    /**
     * Send the  UNSUBSCRIBE  Mailbox Command
     *
     * @param string $mailbox The mailbox name to unsubscribe
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdUnsubscribe($mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        return $this->_genericCommand('UNSUBSCRIBE', $mailbox_name);
    }



    /**
     * Send the  FETCH Command
     *
     * @param string $msgset     msgset
     * @param string $fetchparam fetchparam
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdFetch($msgset, $fetchparam)
    {
        return $this->_genericCommand('FETCH', $msgset . ' ' . $fetchparam);
    }



    /**
     * Send the  CAPABILITY Command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdCapability()
    {
        $ret = $this->_genericCommand('CAPABILITY');

        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT']['CAPABILITY'];

            // fill the $this->_serverAuthMethods 
            // and $this->_serverSupportedCapabilities arrays
            foreach ($ret['PARSED']['CAPABILITIES'] as $auth_method) {
                if (strtoupper(substr($auth_method, 0, 5)) == 'AUTH=') {
                    $this->_serverAuthMethods[] = substr($auth_method, 5);
                }
            }

            // Keep the capabilities response to use ir later
            $this->_serverSupportedCapabilities = $ret['PARSED']['CAPABILITIES'];
        }

        return $ret;
    }



    /**
     * Send the  CAPABILITY Command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdNamespace()
    {
        $ret = $this->_genericCommand('NAMESPACE');

        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT']['NAMESPACE'];

            // Keep the namespace response for later use
            $this->_namespace = $ret['PARSED']['NAMESPACES'];
        }

        return $ret;
    }



    /**
     * Send the  STATUS Mailbox Command
     *
     * @param string $mailbox The mailbox name
     * @param mixed  $request The request status 
     *                        it could be an array or space separated string of
     *                        MESSAGES | RECENT | UIDNEXT
     *                        UIDVALIDITY | UNSEEN
     *
     * @return array Returns a Parsed Response
     * @access public
     * @since 1.0
     */
    function cmdStatus($mailbox, $request)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);

        // make array from $request if it is none
        if (!is_array($request)) {
            $request = explode(' ', $request);
        }

        // see RFC 3501
        $valid_status_data = array('MESSAGES', 
                                   'RECENT', 
                                   'UIDNEXT', 
                                   'UIDVALIDITY', 
                                   'UNSEEN');

        foreach ($request as $status_data) {
            if (!in_array($status_data, $valid_status_data)) {
                $this->_protError('request "' . $status_data . '" is invalid! '
                                  . 'See RFC 3501!!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
        }
        
        // back to space separated string
        $request = implode(' ', $request);

        $ret = $this->_genericCommand('STATUS', 
                                      $mailbox_name . ' (' . $request . ')');
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][count($ret['PARSED'])-1]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the  LIST  Command
     *
     * @param string $mailbox_base mailbox_base
     * @param string $mailbox      The mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since  1.0
     */
    function cmdList($mailbox_base, $mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        $mailbox_base = $this->_createQuotedString($mailbox_base);
        return $this->_genericCommand('LIST', 
                                      $mailbox_base . ' ' . $mailbox_name);
    }



    /**
     * Send the  LSUB  Command
     *
     * @param string $mailbox_base mailbox_base
     * @param string $mailbox      The mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdLsub($mailbox_base, $mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        $mailbox_base = $this->_createQuotedString($mailbox_base);
        return $this->_genericCommand('LSUB', 
                                      $mailbox_base . ' ' . $mailbox_name);
    }



    /**
     * Send the  APPEND  Command
     *
     * @param string $mailbox    Mailbox name
     * @param string $msg        Message
     * @param string $flags_list Flags list
     * @param string $time       Time
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdAppend($mailbox, $msg, $flags_list = '', $time = '')
    {
        if (!$this->_connected) {
            return new PEAR_Error('not connected!');
        }

        $cmdid    = $this->_getCmdId();
        $msg_size = $this->_getLineLength($msg);

        $mailbox_name = $this->_createQuotedString($mailbox);
        if ($flags_list != '') {
            $flags_list = ' (' . $flags_list . ')';
        }

        if ($this->hasCapability('LITERAL+') == true) {
            if ($time != '') {
                $timeAsString = date("d-M-Y H:i:s O", $time);
                $param = sprintf("%s %s\"%s\"{%s+}\r\n%s",
                                 $mailbox_name,
                                 $flags_list,
                                 $timeAsString, 
                                 $msg_size,
                                 $msg);
            } else {
                $param = sprintf("%s%s {%s+}\r\n%s",
                                 $mailbox_name,
                                 $flags_list,
                                 $msg_size,
                                 $msg);
            }
            if (PEAR::isError($error = $this->_putCMD($cmdid, 
                                                      'APPEND', 
                                                      $param))) {
                return $error;
            }
        } else {
            $param = sprintf("%s%s {%s}",
                             $mailbox_name,
                             $flags_list,
                             $msg_size);
            if (PEAR::isError($error = $this->_putCMD($cmdid, 
                                                      'APPEND', 
                                                      $param))) {
                return $error;
            }
            if (PEAR::isError($error = $this->_recvLn())) {
                return $error;
            }

            if (PEAR::isError($error = $this->_send($msg . "\r\n"))) {
                return $error;
            }
        }

        $args = $this->_getRawResponse($cmdid);
        $ret  = $this->_genericImapResponseParser($args, $cmdid);
        return $ret;
    }



    /**
     * Send the CLOSE command.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdClose()
    {
        return $this->_genericCommand('CLOSE');
    }



    /**
     * Send the EXPUNGE command.
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since  1.0
     */
    function cmdExpunge()
    {
        $ret = $this->_genericCommand('EXPUNGE');

        if (isset($ret['PARSED'])) {
            $parsed = $ret['PARSED'];
            unset($ret["PARSED"]);
            foreach ($parsed as $command) {
                if (strtoupper($command['COMMAND']) == 'EXPUNGE') {
                    $ret['PARSED'][$command['COMMAND']][] = $command['NRO'];
                } else {
                    $ret['PARSED'][$command['COMMAND']] = $command['NRO'];
                }
            }
        }
        return $ret;
    }



    /**
     * Send the SEARCH command.
     *
     * @param string $search_cmd Search command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdSearch($search_cmd)
    {
        /*        if($_charset != '' )
                    $_charset = "[$_charset] ";
                $param=sprintf("%s%s",$charset,$search_cmd);
        */
        $ret = $this->_genericCommand('SEARCH', $search_cmd);
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the SORT command.
     *
     * @param string $sort_cmd Sort command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.1
     */
    function cmdSort($sort_cmd)
    {
        /* 
        if ($_charset != '' )
            $_charset = "[$_charset] ";
        $param = sprintf("%s%s",$charset,$search_cmd);
        */
        $ret = $this->_genericCommand('SORT', $sort_cmd);
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the STORE command.
     *
     * @param string $message_set The sessage_set
     * @param string $dataitem    The way we store the flags
     *                            FLAGS: replace the flags whith $value
     *                            FLAGS.SILENT: replace the flags whith $value
     *                             but don't return untagged responses
     *                            +FLAGS: Add the flags whith $value
     *                            +FLAGS.SILENT: Add the flags whith $value 
     *                             but don't return untagged responses
     *                            -FLAGS: Remove the flags whith $value
     *                            -FLAGS.SILENT: Remove the flags whith $value
     *                             but don't return untagged responses
     * @param string $value       Value
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdStore($message_set, $dataitem, $value)
    {
        /* As said in RFC2060...
        C: A003 STORE 2:4 +FLAGS (\Deleted)
        S: * 2 FETCH FLAGS (\Deleted \Seen)
        S: * 3 FETCH FLAGS (\Deleted)
        S: * 4 FETCH FLAGS (\Deleted \Flagged \Seen)
        S: A003 OK STORE completed
        */
        if ($dataitem != 'FLAGS' 
            && $dataitem != 'FLAGS.SILENT' 
            && $dataitem != '+FLAGS' 
            && $dataitem != '+FLAGS.SILENT' 
            && $dataitem != '-FLAGS' 
            && $dataitem != '-FLAGS.SILENT') {
            $this->_protError('dataitem "' . $dataitem . '" is invalid! '
                              . 'See RFC2060!!!!',
                              __LINE__, 
                              __FILE__);
        }
        $param = sprintf("%s %s (%s)", $message_set, $dataitem, $value);
        return $this->_genericCommand('STORE', $param);
    }



    /**
     * Send the COPY command.
     *
     * @param string $message_set Message set
     * @param string $mailbox     Mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdCopy($message_set, $mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        return $this->_genericCommand('COPY', 
                                      sprintf("%s %s", 
                                              $message_set,
                                              $mailbox_name));
    }



    /**
     * The UID FETH command
     *
     * @param string $msgset     Msgset
     * @param string $fetchparam Fetchparm
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdUidFetch($msgset, $fetchparam)
    {
        return $this->_genericCommand('UID FETCH', 
                                      sprintf("%s %s", $msgset, $fetchparam));
    }



    /**
     * The UID COPY command
     *
     * @param string $message_set Msgset
     * @param string $mailbox     Mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdUidCopy($message_set, $mailbox)
    {
        $mailbox_name = $this->_createQuotedString($mailbox);
        return $this->_genericCommand('UID COPY', 
                                      sprintf("%s %s",
                                              $message_set,
                                              $mailbox_name));
    }



    /**
     * Send the UID STORE command.
     *
     * @param string $message_set The sessage_set
     * @param string $dataitem    The way we store the flags
     *                            FLAGS: replace the flags whith $value
     *                            FLAGS.SILENT: replace the flags whith $value 
     *                             but don't return untagged responses
     *                            +FLAGS: Add the flags whith $value
     *                            +FLAGS.SILENT: Add the flags whith $value 
     *                             but don't return untagged responses
     *                            -FLAGS: Remove the flags whith $value
     *                            -FLAGS.SILENT: Remove the flags whith $value 
     *                             but don't return untagged responses
     * @param string $value       Value
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since  1.0
     */
    function cmdUidStore($message_set, $dataitem, $value)
    {
        /* As said in RFC2060...
        C: A003 STORE 2:4 +FLAGS (\Deleted)
        S: * 2 FETCH FLAGS (\Deleted \Seen)
        S: * 3 FETCH FLAGS (\Deleted)
        S: * 4 FETCH FLAGS (\Deleted \Flagged \Seen)
        S: A003 OK STORE completed
        */
        if ($dataitem != 'FLAGS' 
            && $dataitem != 'FLAGS.SILENT' 
            && $dataitem != '+FLAGS' 
            && $dataitem != '+FLAGS.SILENT' 
            && $dataitem != '-FLAGS' 
            && $dataitem != '-FLAGS.SILENT') {
                $this->_protError('dataitem "' . $dataitem . '" is invalid! '
                                  . 'See RFC2060!!!!', 
                                  __LINE__, 
                                  __FILE__);
        }

        return $this->_genericCommand('UID STORE', 
                                      sprintf("%s %s (%s)",
                                              $message_set,
                                              $dataitem,
                                              $value));
    }



    /**
     * Send the SEARCH command.
     *
     * @param string $search_cmd Search command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdUidSearch($search_cmd)
    {
        $ret = $this->_genericCommand('UID SEARCH', 
                                      sprintf("%s", $search_cmd));
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the UID SORT command.
     *
     * @param string $sort_cmd Sort command
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.1
     */
    function cmdUidSort($sort_cmd)
    {
        $ret = $this->_genericCommand('UID SORT', sprintf("%s", $sort_cmd));
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the X command.
     *
     * @param string $atom       Atom
     * @param string $parameters Parameters
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success.
     * @access public
     * @since 1.0
     */
    function cmdX($atom, $parameters)
    {
        return $this->_genericCommand('X' . $atom, $parameters);
    }



    /********************************************************************
    ***
    **             HERE ENDS the RFC2060 IMAPS FUNCTIONS
    **             AND BEGIN THE EXTENSIONS FUNCTIONS
    **
    *******************************************************************/



    /*******************************************************************
    **             RFC2087 IMAP4 QUOTA extension BEGINS HERE
    *******************************************************************/

    /**
     * Send the GETQUOTA command.
     *
     * @param string $mailbox_name The mailbox name to query for quota data
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or quota data on success
     * @access public
     * @since 1.0
     */
    function cmdGetQuota($mailbox_name)
    {
        //Check if the IMAP server has QUOTA support
        if (!$this->hasQuotaSupport()) {
            return new PEAR_Error('This IMAP server doen\'t support QUOTA\'s!');
        }

        $mailbox_name = sprintf("%s", $this->utf7Encode($mailbox_name));
        $ret          = $this->_genericCommand('GETQUOTA', $mailbox_name);

        if (isset($ret['PARSED'])) {
            // remove the array index because the quota response returns 
            // only 1 line of output
            $ret['PARSED'] = $ret['PARSED'][0];
        }
        return $ret;
    }



    /**
     * Send the GETQUOTAROOT command.
     *
     * @param string $mailbox_name The ailbox name to query for quota data
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or quota data on success
     * @access public
     * @since 1.0
     */
    function cmdGetQuotaRoot($mailbox_name)
    {
        //Check if the IMAP server has QUOTA support
        if (!$this->hasQuotaSupport()) {
            return new PEAR_Error('This IMAP server doesn\'t support QUOTA\'s!');
        }

        $mailbox_name = sprintf("%s", $this->utf7Encode($mailbox_name));
        $ret          = $this->_genericCommand('GETQUOTAROOT', $mailbox_name);

        if (isset($ret['PARSED'])) {
            // remove the array index because the quota response returns 
            // only 1 line of output
            $ret['PARSED'] = $ret['PARSED'][1];
        }
        return $ret;
    }



    /**
     * Send the SETQUOTA command.
     *
     * @param string $mailbox_name  The mailbox name to query for quota data
     * @param string $storageQuota  Sets the max number of bytes this mailbox 
     *                              can handle
     * @param string $messagesQuota Sets the max number of messages this 
     *                              mailbox can handle
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or quota data on success
     * @access public
     * @since 1.0
     */
    function cmdSetQuota($mailbox_name, 
                         $storageQuota = null, 
                         $messagesQuota = null)
    {
        // ToDo:  implement the quota by number of emails!!

        //Check if the IMAP server has QUOTA support
        if (!$this->hasQuotaSupport()) {
            return new PEAR_Error('This IMAP server doesn\'t support QUOTA\'s!');
        }

        if (($messagesQuota == null) && ($storageQuota == null)) {
            return new PEAR_Error('$storageQuota and $messagesQuota parameters '
                                  . 'can\'t be both null if you want to use '
                                  . 'quota');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        //Make the command request
        $param = sprintf("%s (", $mailbox_name);

        if ($storageQuota != null) {
            if ($storageQuota == -1) {
                // set -1 to remove a quota
                $param = sprintf("%s", $param);
            } elseif ($storageQuota == strtolower('remove')) {
                // this is a cyrus rmquota specific feature
                // see http://email.uoa.gr/projects/cyrus/quota-patches/rmquota/
                $param = sprintf("%sREMOVE 1", $param);
            } else {
                $param = sprintf("%sSTORAGE %s", $param, $storageQuota);
            }

            if ($messagesQuota != null) {
                // if we have both types of quota on the same call we must 
                // append an space between those parameters
                $param = sprintf("%s ", $param);
            }
        }
        if ($messagesQuota != null) {
            $param = sprintf("%sMESSAGES %s", $param, $messagesQuota);
        }
        $param = sprintf("%s)", $param);

        return $this->_genericCommand('SETQUOTA', $param);
    }



    /**
     * Send the SETQUOTAROOT command.
     *
     * @param string $mailbox_name  The mailbox name to query for quota data
     * @param string $storageQuota  Sets the max number of bytes this mailbox 
     *                              can handle
     * @param string $messagesQuota Sets the max number of messages this 
     *                              mailbox can handle
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or quota data on success
     * @access public
     * @since 1.0
     */
    function cmdSetQuotaRoot($mailbox_name, 
                             $storageQuota = null,
                             $messagesQuota = null)
    {
        //Check if the IMAP server has QUOTA support
        if (!$this->hasQuotaSupport()) {
            return new PEAR_Error('This IMAP server doesn\'t support QUOTA\'s!');
        }

        if (($messagesQuota == null) && ($storageQuota == null)) {
            return new PEAR_Error('$storageQuota and $messagesQuota parameters '
                                  . 'can\'t be both null if you want to use '
                                  . 'quota');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        //Make the command request
        $param = sprintf("%s (", $mailbox_name);

        if ($storageQuota != null) {
            $param = sprintf("%sSTORAGE %s", $param, $storageQuota);
            if ($messagesQuota != null) {
                // if we have both types of quota on the same call we must 
                // append an space between those parameters
                $param = sprintf("%s ", $param);
            }
        }

        if ($messagesQuota != null) {
            $param = sprintf("%sMESSAGES %s", $param, $messagesQuota);
        }
        $param = sprintf("%s)", $param);

        return $this->_genericCommand('SETQUOTAROOT', $param);
    }



    /********************************************************************
    ***             RFC2087 IMAP4 QUOTA extension ENDS HERE
    ********************************************************************/



    /********************************************************************
    ***             RFC2086 IMAP4 ACL extension BEGINS HERE
    ********************************************************************/

    /**
     * Send the SETACL command.
     *
     * @param string $mailbox_name Mailbox name
     * @param string $user         User
     * @param string $acl          ACL string
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success
     * @access public
     * @since 1.0
     */
    function cmdSetACL($mailbox_name, $user, $acl)
    {
        //Check if the IMAP server has ACL support
        if (!$this->hasAclSupport()) {
            return new PEAR_Error('This IMAP server does not support ACL\'s!');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        $user_name    = $this->_createQuotedString($user);

        if (is_array($acl)) {
            $acl = implode('', $acl);
        }

        return $this->_genericCommand('SETACL', 
                                      sprintf("%s %s \"%s\"",
                                              $mailbox_name,
                                              $user_name,
                                              $acl));
    }



    /**
     * Send the DELETEACL command.
     *
     * @param string $mailbox_name Mailbox name
     * @param string $user         User
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success
     * @access public
     * @since 1.0
     */
    function cmdDeleteACL($mailbox_name, $user)
    {
        //Check if the IMAP server has ACL support
        if (!$this->hasAclSupport()) {
            return new PEAR_Error('This IMAP server does not support ACL\'s!');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        
        return $this->_genericCommand('DELETEACL', 
                                      sprintf("%s \"%s\"", 
                                              $mailbox_name, 
                                              $user));
    }



    /**
     * Send the GETACL command.
     *
     * @param string $mailbox_name Mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or ACL list on success
     * @access public
     * @since 1.0
     */
    function cmdGetACL($mailbox_name)
    {
        //Check if the IMAP server has ACL support
        if (!$this->hasAclSupport()) {
            return new PEAR_Error('This IMAP server does not support ACL\'s!');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        $ret          = $this->_genericCommand('GETACL', 
                                               sprintf("%s", $mailbox_name));

        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the LISTRIGHTS command.
     *
     * @param string $mailbox_name Mailbox name
     * @param string $user         User
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or list of users rights
     * @access public
     * @since 1.0
     */
    function cmdListRights($mailbox_name, $user)
    {
        //Check if the IMAP server has ACL support
        if (!$this->hasAclSupport()) {
            return new PEAR_Error('This IMAP server does not support ACL\'s!');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        $ret          = $this->_genericCommand('LISTRIGHTS', 
                                               sprintf("%s \"%s\"",
                                                       $mailbox_name,
                                                       $user));
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /**
     * Send the MYRIGHTS command.
     *
     * @param string $mailbox_name Mailbox name
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or MYRIGHTS response on success
     * @access public
     * @since 1.0
     */
    function cmdMyRights($mailbox_name)
    {
        // Check if the IMAP server has ACL support
        if (!$this->hasAclSupport()) {
            return new PEAR_Error('This IMAP server does not support ACL\'s!');
        }

        $mailbox_name = $this->_createQuotedString($mailbox_name);
        $ret          = $this->_genericCommand('MYRIGHTS', 
                                               sprintf("%s", $mailbox_name));
        if (isset($ret['PARSED'])) {
            $ret['PARSED'] = $ret['PARSED'][0]['EXT'];
        }
        return $ret;
    }



    /********************************************************************
    ***             RFC2086 IMAP4 ACL extension ENDs HERE
    ********************************************************************/


    /********************************************************************
    ***  draft-daboo-imap-annotatemore-05 IMAP4 ANNOTATEMORE extension 
    ***  BEGINS HERE
    ********************************************************************/

    /**
     * Send the SETANNOTATION command.
     *
     * @param string $mailboxName Mailbox name
     * @param string $entry       Entry
     * @param string $values      Value
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success
     * @access public
     * @since 1.0
     */
    function cmdSetAnnotation($mailboxName, $entry, $values)
    {
        // Check if the IMAP server has ANNOTATEMORE support
        if (!$this->hasAnnotateMoreSupport()) {
            return new PEAR_Error('This IMAP server does not support the '
                                  . 'ANNOTATEMORE extension!');
        }

        if (!is_array($values)) {
            return new PEAR_Error('Invalid $values argument passed to '
                                  . 'cmdSetAnnotation');
        }

        $mailboxName = $this->_createQuotedString($mailboxName);

        $vallist = '';
        foreach ($values as $name => $value) {
            $vallist .= '"' . $name . '" "' . $value . '"';
        }
        $vallist = rtrim($vallist);

        return $this->_genericCommand('SETANNOTATION', 
                                      sprintf('%s "%s" (%s)', 
                                              $mailboxName, 
                                              $entry, 
                                              $vallist));
    }



    /**
     * Send the DELETEANNOTATION command.
     *
     * @param string $mailboxName Mailbox name
     * @param string $entry       Entry
     * @param string $values      Value
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or true on success
     * @access public
     * @since 1.0
     */
    function cmdDeleteAnnotation($mailboxName, $entry, $values)
    {
        // Check if the IMAP server has ANNOTATEMORE support
        if (!$this->hasAnnotateMoreSupport()) {
            return new PEAR_Error('This IMAP server does not support the '
                                  . 'ANNOTATEMORE extension!');
        }

        if (!is_array($values)) {
            return new PEAR_Error('Invalid $values argument passed to '
                                  . 'cmdDeleteAnnotation');
        }

        $mailboxName = $this->_createQuotedString($mailboxName);

        $vallist = '';
        foreach ($values as $name) {
            $vallist .= '"' . $name . '" NIL';
        }
        $vallist = rtrim($vallist);

        return $this->_genericCommand('SETANNOTATION', 
                                      sprintf('%s "%s" (%s)', 
                                              $mailboxName, 
                                              $entry, 
                                              $vallist));
    }



    /**
     * Send the GETANNOTATION command.
     *
     * @param string $mailboxName Mailbox name
     * @param string $entries     Entries
     * @param string $values      Value
     *
     * @return mixed Returns a PEAR_Error with an error message on any
     *               kind of failure, or GETANNOTATION result on success
     * @access public
     * @since 1.0
     */
    function cmdGetAnnotation($mailboxName, $entries, $values)
    {
        // Check if the IMAP server has ANNOTATEMORE support
        if (!$this->hasAnnotateMoreSupport()) {
            return new PEAR_Error('This IMAP server does not support the '
                                  . 'ANNOTATEMORE extension!');
        }

        $entlist = '';

        if (!is_array($entries)) {
            $entries = array($entries);
        }

        foreach ($entries as $name) {
            $entlist .= '"' . $name . '"';
        }
        $entlist = rtrim($entlist);
        if (count($entries) > 1) {
            $entlist = '(' . $entlist . ')';
        }

        $vallist = '';
        if (!is_array($values)) {
            $values = array($values);
        }

        foreach ($values as $name) {
            $vallist .= '"' . $name . '"';
        }
        $vallist = rtrim($vallist);
        if (count($values) > 1) {
            $vallist = '(' . $vallist . ')';
        }

        $mailboxName = $this->_createQuotedString($mailboxName);

        return $this->_genericCommand('GETANNOTATION', 
                                      sprintf('%s %s %s', 
                                              $mailboxName, 
                                              $entlist, 
                                              $vallist));
    }


    /***********************************************************************
    ***  draft-daboo-imap-annotatemore-05 IMAP4 ANNOTATEMORE extension 
    ***  ENDs HERE
    ************************************************************************/


    /********************************************************************
    ***
    ***             HERE ENDS THE EXTENSIONS FUNCTIONS
    ***             AND BEGIN THE AUXILIARY FUNCTIONS
    ***
    ********************************************************************/

    /**
     * tell if the server has capability $capability
     *
     * @return true or false
     * @access public
     * @since 1.0
     */
    function getServerAuthMethods()
    {
        if ($this->_serverAuthMethods == null) {
            $this->cmdCapability();
            return $this->_serverAuthMethods;
        }
        return false;
    }



    /**
     * tell if the server has capability $capability
     *
     * @param string $capability Capability
     *
     * @return true or false
     * @access public
     * @since 1.0
     */
    function hasCapability($capability)
    {
        if ($this->_serverSupportedCapabilities == null) {
            $this->cmdCapability();
        }
        if ($this->_serverSupportedCapabilities != null) {
            if (in_array($capability, $this->_serverSupportedCapabilities)) {
                return true;
            }
        }
        return false;
    }



    /**
     * tell if the server has Quota support
     *
     * @return true or false
     * @access public
     * @since 1.0
     */
    function hasQuotaSupport()
    {
        return $this->hasCapability('QUOTA');
    }



    /**
     * tell if the server has Quota support
     *
     * @return true or false
     * @access public
     * @since 1.0
     */
    function hasAclSupport()
    {
        return $this->hasCapability('ACL');
    }



    /**
     * tell if the server has support for the ANNOTATEMORE extension
     *
     * @return true or false
     * @access public
     * @since 1.0
     */
    function hasAnnotateMoreSupport()
    {
        return $this->hasCapability('ANNOTATEMORE');
    }


    /**
     * Create a quoted string
     *
     * @param string $str String
     *
     * @return string Quoted $str
     * @access public
     * @since 1.0
     */
    function _createQuotedString($str) 
    {
        $search  = array('\\', '"');
        $replace = array('\\\\', '\\"');

        $mailbox_name = str_replace($search, $replace, $str);
        $mailbox_name = sprintf("\"%s\"", $this->utf7Encode($mailbox_name));

        return $mailbox_name;
    }



    /**
     * Parses the responses like RFC822.SIZE and INTERNALDATE
     *
     * @param string &$str The IMAP's server response
     * @param int    $line Line number
     * @param string $file File
     *
     * @return string next token
     * @access private
     * @since 1.0
     */
    function _parseOneStringResponse(&$str, $line, $file)
    {
        $this->_parseSpace($str, $line, $file);
        $size = $this->_getNextToken($str, $uid);
        return $uid;
    }



    /**
     * Parses the FLAG response
     *
     * @param string &$str The IMAP's server response
     *
     * @return Array containing  the parsed  response
     * @access private
     * @since 1.0
     */
    function _parseFLAGSresponse(&$str)
    {
        $this->_parseSpace($str, __LINE__, __FILE__);
        $params_arr[] = $this->_arrayfyContent($str);
        $flags_arr    = array();
        for ($i = 0; $i < count($params_arr[0]); $i++) {
            $flags_arr[] = $params_arr[0][$i];
        }
        return $flags_arr;
    }



    /**
     * Parses the BODY response
     *
     * @param string &$str    The IMAP's server response
     * @param string $command Command
     *
     * @return array The parsed response
     * @access private
     * @since 1.0
     */
    function _parseBodyResponse(&$str, $command) 
    {
        $this->_parseSpace($str, __LINE__, __FILE__);
        while ($str[0] != ')' && $str != '') {
            $params_arr[] = $this->_arrayfyContent($str);
        }

        return $params_arr;
    }



    /**
     * Makes the content an Array
     *
     * @param string &$str The IMAP's server response
     *
     * @return array The parsed response
     * @access private
     * @since 1.0
     */
    function _arrayfyContent(&$str)
    {
        $params_arr = array();
        $this->_getNextToken($str, $params);
        if ($params != '(') {
            return $params;
        }
        $this->_getNextToken($str, $params, false, false);
        while ($str != '' && $params != ')') {
            if ($params != '') {
                if ($params[0] == '(') {
                    $params = $this->_arrayfyContent($params);
                }
                if ($params != ' ') {
                    // I don't remove the colons (") to handle the case of 
                    // retriving " "
                    // If I remove the colons the parser will interpret this 
                    // field as an imap separator (space) instead of a valid 
                    // field so I remove the colons here
                    if ($params == '""') {
                        $params = '';
                    } else {
                        if ($params[0] == '"') {
                            $params = $this->_getSubstr($params, 
                                                        1, 
                                                        $this->_getLineLength($params)-2);
                        }
                    }
                    $params_arr[] = $params;
                }
            } else {
                // if params if empty (for example i'm parsing 2 quotes ("")
                // I'll append an array entry to mantain compatibility
                $params_arr[] = $params;
            }
            $this->_getNextToken($str, $params, false, false);
        }
        $this->arrayfy_content_level--;
        return $params_arr;
    }



    /**
     * Parses the BODY[],BODY[TEXT],.... responses
     *
     * @param string &$str    The IMAP's server response
     * @param string $command Command
     *
     * @return array The parsed response
     * @access private
     * @since 1.0
    */
    function _parseContentresponse(&$str, $command)
    {
        $content = '';
        $this->_parseSpace($str, __LINE__, __FILE__);
        $size = $this->_getNextToken($str, $content);
        return array('CONTENT' => $content, 'CONTENT_SIZE' => $size);
    }



    /**
     * Parses the ENVELOPE response
     *
     * @param string &$str The IMAP's server response
     *
     * @return array The parsed response
     * @access private
     * @since 1.0
     */
    function _parseENVELOPEresponse(&$str)
    {
        $content = '';
        $this->_parseSpace($str, __LINE__, __FILE__);

        $this->_getNextToken($str, $parenthesis);
        if ($parenthesis != '(') {
            $this->_protError('must be a "(" but is a "' . $parenthesis .'" '
                              . '!!!!', 
                              __LINE__, 
                              __FILE__);
        }
        // Get the email's Date
        $this->_getNextToken($str, $date);

        $this->_parseSpace($str, __LINE__, __FILE__);

        // Get the email's Subject:
        $this->_getNextToken($str, $subject);
        //$subject = $this->decode($subject);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //FROM LIST;
        $from_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //"SENDER LIST\n";
        $sender_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //"REPLY-TO LIST\n";
        $reply_to_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //"TO LIST\n";
        $to_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //"CC LIST\n";
        $cc_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        //"BCC LIST|$str|\n";
        $bcc_arr = $this->_getAddressList($str);

        $this->_parseSpace($str, __LINE__, __FILE__);

        $this->_getNextToken($str, $in_reply_to);

        $this->_parseSpace($str, __LINE__, __FILE__);

        $this->_getNextToken($str, $message_id);

        $this->_getNextToken($str, $parenthesis);

        if ($parenthesis != ')') {
            $this->_protError('must be a ")" but is a "' . $parenthesis .'" '
                              . '!!!!', 
                              __LINE__, 
                              __FILE__);
        }

        return array('DATE'        => $date, 
                     'SUBJECT'     => $subject,
                     'FROM'        => $from_arr,
                     'SENDER'      => $sender_arr, 
                     'REPLY_TO'    => $reply_to_arr, 
                     'TO'          => $to_arr,
                     'CC'          => $cc_arr, 
                     'BCC'         => $bcc_arr, 
                     'IN_REPLY_TO' => $in_reply_to, 
                     'MESSAGE_ID'  => $message_id);
    }



    /**
     * Parses the ARRDLIST as defined in RFC
     *
     * @param string &$str The IMAP's server response
     *
     * @return array The parsed response
     * @access private
     * @since 1.0
     */
    function _getAddressList(&$str)
    {
        $params_arr = $this->_arrayfyContent($str);
        if (!isset($params_arr)) {
            return $params_arr;
        }

        if (is_array($params_arr)) {
            foreach ($params_arr as $index => $address_arr) {
                $personal_name  = $address_arr[0];
                $at_domain_list = $address_arr[1];
                $mailbox_name   = $address_arr[2];
                $host_name      = $address_arr[3];
                if ($mailbox_name != '' && $host_name != '') {
                    $email = $mailbox_name . "@" . $host_name;
                } else {
                    $email = false;
                }
                if ($email == false) {
                    $rfc822_email = false;
                } else {
                    if (!isset($personal_name)) {
                        $rfc822_email = '<' . $email . '>';
                    } else {
                        $rfc822_email = '"' . $personal_name . '" <'
                                        . $email . '>';
                    }
                }
                $email_arr[] = array('PERSONAL_NAME'  => $personal_name, 
                                     'AT_DOMAIN_LIST' => $at_domain_list,
                                     'MAILBOX_NAME'   => $this->utf7Decode($mailbox_name),
                                     'HOST_NAME'      => $host_name,
                                     'EMAIL'          => $email , 
                                     'RFC822_EMAIL'   => $rfc822_email );
            }
            return $email_arr;
        }
        return array();
    }



    /**
     * Utility funcion to find the closing parenthesis ")" Position it takes 
     * care of quoted ones
     *
     * @param string $str_line   String
     * @param string $startDelim Start delimiter
     * @param string $stopDelim  Stop delimiter
     *
     * @return int the pos of the closing parenthesis ")"
     * @access private
     * @since 1.0
    */
    function _getClosingBracesPos($str_line, 
                                  $startDelim = '(', 
                                  $stopDelim = ')')
    {
        $len = $this->_getLineLength($str_line);
        $pos = 0;
        // ignore all extra characters
        // If inside of a string, skip string -- Boundary IDs and other
        // things can have ) in them.
        if ($str_line[$pos] != $startDelim) {
            $this->_protError('_getClosingParenthesisPos: must start with a '
                              . '"' . $startDelim . '" but is a '
                              . '"' . $str_line[$pos] . '"!!!!'
                              . 'STR_LINE: ' . $str_line
                              . ' |size: ' . $len
                              . ' |POS: ' . $pos, 
                              __LINE__, 
                              __FILE__);
            return( $len );
        }
        for ($pos = 1; $pos < $len; $pos++) {
            if ($str_line[$pos] == $stopDelim) {
                break;
            }
            if ($str_line[$pos] == '"') {
                $this->_advanceOverStr($str_line, 
                                       $pos, 
                                       $len, 
                                       $startDelim, 
                                       $stopDelim);
            }
            if ($str_line[$pos] == $startDelim) {
                $str_line_aux = $this->_getSubstr($str_line, $pos);
                $pos_aux      = $this->_getClosingBracesPos($str_line_aux);
                $pos         += $pos_aux;
                if ($pos == $len-1) {
                    break;
                }
            }
        }
        if ($str_line[$pos] != $stopDelim) {
            $this->_protError('_getClosingBracesPos: must be a '
                              . '"' . $stopDelim . '" but is a '
                              . '"' . $str_line[$pos] . '"'
                              . ' |POS: ' . $pos
                              . ' |STR_LINE: ' . $str_line . '!!!!',
                              __LINE__, 
                              __FILE__);
        }

        if ($pos >= $len) {
            return false;
        }
        return $pos;
    }



    /**
     * Advances the position $pos in $str over an correct escaped string
     *
     * Examples: $str='"\\\"First Last\\\""', $pos=0
     *      --> returns true and $pos=strlen($str)-1
     *
     * @param string $str        String
     * @param int    &$pos       Current position in $str pointing to a 
     *                            double quote ("), on return pointing 
     *                            on the closing double quote
     * @param int    $len        Length of $str in bytes(!)
     * @param string $startDelim Start delimiter
     * @param string $stopDelim  Stop delimiter
     *
     * @return boolean true if we advanced over a correct string, 
     *                 false otherwise
     * @access private
     * @author Nigel Vickers
     * @author Ralf Becker
     * @since 1.1
     */
    function _advanceOverStr($str, 
                             &$pos, 
                             $len, 
                             $startDelim ='(', 
                             $stopDelim = ')') 
    {
        if ($str[$pos] !== '"') {
            // start condition failed
            return false;
        }
        
        $pos++;

        while ($str[$pos] !== '"' && $pos < $len) {
            // this is a fix to stop before the delimiter, in broken 
            // string messages containing an odd number of double quotes
            // the idea is to check for a stopDelimited followed by 
            // eiter a new startDelimiter or an other stopDelimiter
            // that allows to have something 
            // like '"Name (Nick)" <email>' containing one delimiter
                
            if ($str[$pos] === $stopDelim 
                && ($str[$pos+1] === $startDelim 
                || $str[$pos+1] === $stopDelim)) {
                // stopDelimited need to be parsed outside!
                $pos--;
                return false;
            }

            // all escaped chars are overread (eg. \\,  \", \x)
            if ($str[$pos] === '\\') {
                $pos++;
            }
            $pos++;
        }

        return $pos < $len && $str[$pos] === '"';
    }



    /**
     * Utility funcion to get from here to the end of the line
     *
     * @param string  &$str      String
     * @param boolean $including true for Including EOL
     *                           false to not include EOL
     *
     * @return string The string to the first EOL
     * @access private
     * @since  1.0
     */
    function _getToEOL(&$str, $including = true) 
    {
        $len = $this->_getLineLength($str);
        if ($including) {
            for ($i=0; $i<$len; $i++) {
                if ($str[$i] == "\n") {
                    break;
                }
            }
            $content = $this->_getSubstr($str, 0, $i + 1);
            $str     = $this->_getSubstr($str, $i + 1);
        } else {
            for ($i = 0 ; $i < $len ; $i++ ) {
                if ($str[$i] == "\n" || $str[$i] == "\r") {
                    break;
                }
            }
            $content = $this->_getSubstr($str, 0, $i);
            $str     = $this->_getSubstr($str, $i);
        }
        return $content;
    }



    /**
     * Fetches the next IMAP token or parenthesis
     *
     * @param string  &$str               The IMAP's server response
     * @param string  &$content           The next token
     * @param boolean $parenthesisIsToken true: the parenthesis IS a token, 
     *                                    false: I consider all the response 
     *                                    in parenthesis as a token
     * @param boolean $colonIsToken       true: the colin IS a token
     *                                    false: 
     *
     * @return int The content size
     * @access private
     * @since 1.0
     */
    function _getNextToken(&$str, 
                           &$content, 
                           $parenthesisIsToken = true,
                           $colonIsToken = true)
    {
        $len          = $this->_getLineLength($str);
        $pos          = 0;
        $content_size = false;
        $content      = false;
        if ($str == '' || $len < 2) {
            $content = $str;
            return $len;
        }
        switch ($str[0]) {
        case '{':
            if ($posClosingBraces = $this->_getClosingBracesPos($str, 
                                                                '{', 
                                                                '}') == false) {
                $this->_protError('_getClosingBracesPos() error!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            if (!is_numeric(($strBytes = $this->_getSubstr($str, 
                                                           1, 
                                                           $posClosingBraces - 1)))) {
                $this->_protError('must be a number but is a '
                                  . '"' . $strBytes . '" !!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            if ($str[$posClosingBraces] != '}') {
                $this->_protError('must be a "}" but is a '
                                  . '"' . $str[$posClosingBraces] . '"!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            if ($str[$posClosingBraces + 1] != "\r") {
                $this->_protError('must be a "\r" but is a '
                                  . '"' . $str[$posClosingBraces + 1] . '"!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            if ($str[$posClosingBraces + 2] != "\n") {
                $this->_protError('must be a "\n" but is a '
                                  . '"' . $str[$posClosingBraces + 2] . '"!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            $content = $this->_getSubstr($str, 
                                         $posClosingBraces + 3, 
                                         $strBytes);
            if ($this->_getLineLength($content) != $strBytes) {
                $this->_protError('content size is '
                                  . '"' . $this->_getLineLength($content) . '"'
                                  . ' but the string reports a size of '
                                  . $strBytes .'!!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            $content_size = $strBytes;
            //Advance the string
            $str = $this->_getSubstr($str, $posClosingBraces + $strBytes + 3);
            break;

        case '"':
            if ($colonIsToken) {
                for ($pos=1; $pos<$len; $pos++) {
                    if ($str[$pos] == '"') {
                        break;
                    }
                    if ($str[$pos] == "\\" && $str[$pos + 1 ] == '"') {
                        $pos++;
                    }
                    if ($str[$pos] == "\\" && $str[$pos + 1 ] == "\\") {
                        $pos++;
                    }
                }
                if ($str[$pos] != '"') {
                    $this->_protError('must be a "\"" but is a '
                                      . '"' . $str[$pos] . '"!!!!', 
                                      __LINE__, 
                                      __FILE__);
                }
                $content_size = $pos;
                $content      = $this->_getSubstr($str, 1, $pos - 1);
                //Advance the string
                $str = $this->_getSubstr($str, $pos + 1);
            } else {
                for ($pos=1; $pos<$len; $pos++) {
                    if ($str[$pos] == '"') {
                        break;
                    }
                    if ($str[$pos] == "\\" && $str[$pos + 1 ] == '"' ) {
                        $pos++;
                    }
                    if ($str[$pos] == "\\" && $str[$pos + 1 ] == "\\" ) {
                        $pos++;
                    }
                }
                if ($str[$pos] != '"') {
                    $this->_protError('must be a "\"" but is a '
                                      . '"' . $str[$pos] . '"!!!!', 
                                      __LINE__, 
                                      __FILE__);
                }
                $content_size = $pos;
                $content      = $this->_getSubstr($str, 0, $pos + 1);
                //Advance the string
                $str = $this->_getSubstr($str, $pos + 1);

            }
            // we need to strip slashes for a quoted string
            $content = stripslashes($content);
            break;

        case "\r":
            $pos = 1;
            if ($str[1] == "\n") {
                $pos++;
            }
            $content_size = $pos;
            $content      = $this->_getSubstr($str, 0, $pos);
            $str          = $this->_getSubstr($str, $pos);
            break;

        case "\n":
            $pos          = 1;
            $content_size = $pos;
            $content      = $this->_getSubstr($str, 0, $pos);
            $str          = $this->_getSubstr($str, $pos);
            break;

        case '(':
            if ($parenthesisIsToken == false) {
                $pos          = $this->_getClosingBracesPos($str);
                $content_size = $pos + 1;
                $content      = $this->_getSubstr($str, 0, $pos + 1);
                $str          = $this->_getSubstr($str, $pos + 1);
            } else {
                $pos          = 1;
                $content_size = $pos;
                $content      = $this->_getSubstr($str, 0, $pos);
                $str          = $this->_getSubstr($str, $pos);
            }
            break;

        case ')':
            $pos          = 1;
            $content_size = $pos;
            $content      = $this->_getSubstr($str, 0, $pos);
            $str          = $this->_getSubstr($str, $pos);
            break;

        case ' ':
            $pos          = 1;
            $content_size = $pos;
            $content      = $this->_getSubstr($str, 0, $pos);
            $str          = $this->_getSubstr($str, $pos);
            break;

        default:
            for ($pos = 0; $pos < $len; $pos++) {
                if ($this->_getSubstr($str, 0, 5) == 'BODY[' 
                    || $this->_getSubstr($str, 0, 5) == 'BODY.') {
                    if ($str[$pos] == ']') {
                        $pos++;
                        break;
                    }
                } elseif ($str[$pos] == ' ' 
                          || $str[$pos] == "\r" 
                          || $str[$pos] == ')' 
                          || $str[$pos] == '(' 
                          || $str[$pos] == "\n" ) {
                    break;
                }
                if ($str[$pos] == "\\" && $str[$pos + 1 ] == ' ') {
                    $pos++;
                }
                if ($str[$pos] == "\\" && $str[$pos + 1 ] == "\\") {
                    $pos++;
                }
            }
            //Advance the string
            if ($pos == 0) {
                $content_size = 1;
                $content      = $this->_getSubstr($str, 0, 1);
                $str          = $this->_getSubstr($str, 1);
            } else {
                $content_size = $pos;
                $content      = $this->_getSubstr($str, 0, $pos);
                if ($pos < $len) {
                    $str = $this->_getSubstr($str, $pos);
                } else {
                    //if this is the end of the string... exit the switch
                    break;
                }
            }
            break;
        }
        return $content_size;
    }



    /**
     * Utility funcion to display to console the protocol errors
     * printErrors() additionally has to be set to true
     *
     * @param string  $str        The error message
     * @param int     $line       The line producing the error
     * @param string  $file       File where the error was produced
     * @param boolean $printError true: print the error
     *                            false: do not print the error
     *
     * @return nothing
     * @access private
     * @since 1.0
     */
    function _protError($str , $line , $file, $printError = true)
    {
        // ToDo: all real errors should be returned as PEAR error, others 
        // hidden by default
        // NO extra output from this class!
        if ($this->_printErrors && $printError) {
            echo "$line,$file,PROTOCOL ERROR!:$str\n";
        }
    }



    /**
     * get EXT array from string
     *
     * @param string &$str       String
     * @param string $startDelim Start delimiter
     * @param string $stopDelim  Stop delimiter
     *
     * @return array EXT array
     * @access private
     * @since 1.0
     */
    function _getEXTarray(&$str, $startDelim = '(', $stopDelim = ')')
    {
        /* I let choose the $startDelim  and $stopDelim to allow parsing
           the OK response  so I also can parse a response like this
           * OK [UIDNEXT 150] Predicted next UID
        */
        $this->_getNextToken($str, $parenthesis);
        if ($parenthesis != $startDelim) {
            $this->_protError('must be a "' . $startDelim . '" but is a '
                              . '"' . $parenthesis . '" !!!!', 
                              __LINE__, 
                              __FILE__);
        }

        $parenthesis = '';
        $struct_arr  = array();
        while ($parenthesis != $stopDelim && $str != '') {
            // The command
            $this->_getNextToken($str, $token);
            $token = strtoupper($token);

            if (($ret = $this->_retrParsedResponse($str, $token)) != false) {
                //$struct_arr[$token] = $ret;
                $struct_arr = array_merge($struct_arr, $ret);
            }

            $parenthesis = $token;

        } //While

        if ($parenthesis != $stopDelim ) {
            $this->_protError('1_must be a "' . $stopDelim . '" but is a '
                              . '"' . $parenthesis . '"!!!!', 
                              __LINE__, 
                              __FILE__);
        }
        return $struct_arr;
    }



    /**
     * retrieve parsed response
     *
     * @param string &$str          String
     * @param string $token         Token
     * @param string $previousToken Previous token
     *
     * @return array Parsed response
     * @access private
     * @since 1.0
     */
    function _retrParsedResponse(&$str, $token, $previousToken = null)
    {
        //echo "\n\nTOKEN:$token\r\n";
        $token = strtoupper($token);

        switch ($token) {
        case 'RFC822.SIZE':
            return array($token => $this->_parseOneStringResponse($str,
                                                                  __LINE__, 
                                                                  __FILE__));
            break;

        // case 'RFC822.TEXT':

        // case 'RFC822.HEADER':

        case 'RFC822':
            return array($token => $this->_parseContentresponse($str, 
                                                                $token));
            break;

        case 'FLAGS':
        case 'PERMANENTFLAGS':
            return array($token => $this->_parseFLAGSresponse($str));
            break;

        case 'ENVELOPE':
            return array($token => $this->_parseENVELOPEresponse($str));
            break;

        case 'EXPUNGE':
            return false;
            break;

        case 'NOMODSEQ':
            // ToDo: implement RFC 4551
            return array($token=>'');
            break;

        case 'UID':
        case 'UIDNEXT':
        case 'UIDVALIDITY':
        case 'UNSEEN':
        case 'MESSAGES':
        case 'UIDNEXT':
        case 'UIDVALIDITY':
        case 'UNSEEN':
        case 'INTERNALDATE':
            return array($token => $this->_parseOneStringResponse($str,
                                                                  __LINE__, 
                                                                  __FILE__));
            break;

        case 'BODY':
        case 'BODYSTRUCTURE':
            return array($token => $this->_parseBodyResponse($str, $token));
            break;

        case 'RECENT':
            if ($previousToken != null) {
                $aux['RECENT'] = $previousToken;
                return $aux;
            } else {
                return array($token => $this->_parseOneStringResponse($str,
                                                                      __LINE__, 
                                                                      __FILE__));
            }
            break;

        case 'EXISTS':
            return array($token => $previousToken);
            break;

        case 'READ-WRITE':
        case 'READ-ONLY':
            return array($token => $token);
            break;

        case 'QUOTA':
            /*
            A tipical GETQUOTA DIALOG IS AS FOLLOWS

                C: A0004 GETQUOTA user.damian
                S: * QUOTA user.damian (STORAGE 1781460 4000000)
                S: A0004 OK Completed

            another example of QUOTA response from GETQUOTAROOT:
                C: A0008 GETQUOTAROOT INBOX
                S: * QUOTAROOT INBOX ""
                S: * QUOTA "" (STORAGE 0 1024000 MESSAGE 0 40000)
                S: A0008 OK GETQUOTAROOT finished.

            RFC 2087 section 5.1 says the list could be empty:

                C: A0004 GETQUOTA user.damian
                S: * QUOTA user.damian ()
                S: A0004 OK Completed

            quota_list      ::= "(" #quota_resource ")"
            quota_resource  ::= atom SP number SP number
            quota_response  ::= "QUOTA" SP astring SP quota_list
            */

            $mailbox = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            $ret_aux = array('MAILBOX' => $this->utf7Decode($mailbox));

            // courier fix
            if ($str[0] . $str[1] == "\r\n") {
                return array($token => $ret_aux);
            }
            // end courier fix

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_parseString($str, '(', __LINE__, __FILE__);

            // fetching quota resources 
            // (BNF ::= #quota_resource  but space separated instead of comma)
            $this->_getNextToken($str, $quota_resp);
            while ($quota_resp != ')') {
                if (($ext = $this->_retrParsedResponse($str, 
                                                       $quota_resp)) == false) {
                    $this->_protError('bogus response!!!!', 
                                      __LINE__, 
                                      __FILE__);
                }
                $ret_aux = array_merge($ret_aux, $ext);

                $this->_getNextToken($str, $quota_resp);
                if ($quota_resp == ' ') {
                    $this->_getNextToken($str, $quota_resp);
                }
            }

            // if empty list, apparently no STORAGE or MESSAGE quota set
            return array($token => $ret_aux);
            break;

        case 'QUOTAROOT':
            /*
            A tipical GETQUOTA DIALOG IS AS FOLLOWS

                C: A0004 GETQUOTA user.damian
                S: * QUOTA user.damian (STORAGE 1781460 4000000)
                S: A0004 OK Completed
            */
            $mailbox = $this->utf7Decode($this->_parseOneStringResponse($str,
                                                            __LINE__, 
                                                            __FILE__));

            $str_line = rtrim(substr($this->_getToEOL($str, false), 0));
            if (empty($str_line)) {
                $ret = @array('MAILBOX' => $this->utf7Decode($mailbox));
            } else {
                $quotaroot = $this->_parseOneStringResponse($str_line,
                                                            __LINE__, 
                                                            __FILE__);
                $ret       = @array('MAILBOX' => $this->utf7Decode($mailbox),
                                    $token    => $quotaroot);
            }
            return array($token => $ret);
            break;

        case 'STORAGE':
            $used = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            $qmax = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            return array($token => array('USED' => $used, 'QMAX' => $qmax));
            break;

        case 'MESSAGE':
            $mused = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            $mmax  = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            return array($token=>array("MUSED"=> $mused, "MMAX" => $mmax));
            break;

        case 'FETCH':
            $this->_parseSpace($str, __LINE__, __FILE__);
            // Get the parsed pathenthesis
            $struct_arr = $this->_getEXTarray($str);
            return $struct_arr;
            break;

        case 'NAMESPACE':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $personal, false);
            $struct_arr['NAMESPACES']['personal'] = $this->_arrayfyContent($personal);
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $others, false);
            $struct_arr['NAMESPACES']['others'] = $this->_arrayfyContent($others);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $shared, false);
            $struct_arr['NAMESPACES']['shared'] = $this->_arrayfyContent($shared);
                
            return array($token => $struct_arr);
            break;

        case 'CAPABILITY':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $str_line = rtrim(substr($this->_getToEOL($str, false), 0));

            $struct_arr['CAPABILITIES'] = explode(' ', $str_line);
            return array($token => $struct_arr);
            break;

        case 'STATUS':
            $mailbox = $this->_parseOneStringResponse($str, __LINE__, __FILE__);
            $this->_parseSpace($str, __LINE__, __FILE__);
            $ext                      = $this->_getEXTarray($str);
            $struct_arr['MAILBOX']    = $this->utf7Decode($mailbox);
            $struct_arr['ATTRIBUTES'] = $ext;
            return array($token => $struct_arr);
            break;

        case 'LIST':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $params_arr = $this->_arrayfyContent($str);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $hierarchydelim);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox_name);

            $result_array = array('NAME_ATTRIBUTES'    => $params_arr, 
                                  'HIERACHY_DELIMITER' => $hierarchydelim, 
                                  'MAILBOX_NAME'       => $this->utf7Decode($mailbox_name));
            return array($token => $result_array);
            break;

        case 'LSUB':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $params_arr = $this->_arrayfyContent($str);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $hierarchydelim);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox_name);

            $result_array = array('NAME_ATTRIBUTES'    => $params_arr, 
                                  'HIERACHY_DELIMITER' => $hierarchydelim, 
                                  'MAILBOX_NAME'       => $this->utf7Decode($mailbox_name));
            return array($token => $result_array);
            break;

        case 'SEARCH':
        case 'SORT':
            $str_line = rtrim(substr($this->_getToEOL($str, false), 1));

            $struct_arr[$token . '_LIST'] = explode(' ', $str_line);
            if (count($struct_arr[$token . '_LIST']) == 1 
                && $struct_arr[$token . '_LIST'][0] == '') {
                $struct_arr[$token . '_LIST'] = null;
            }
            return array($token => $struct_arr);
            break;

        case 'OK':
            /* TODO:
                parse the [ .... ] part of the response, use the method
                _getEXTarray(&$str,'[',$stopDelim=']')
            */
            $str_line = rtrim(substr($this->_getToEOL($str, false), 1));
            if ($str_line[0] == '[') {
                $braceLen = $this->_getClosingBracesPos($str_line, '[', ']');
                $str_aux  = '('. substr($str_line, 1, $braceLen -1). ')';
                $ext_arr  = $this->_getEXTarray($str_aux);
                //$ext_arr=array($token=>$this->_getEXTarray($str_aux));
            } else {
                $ext_arr = $str_line;
                //$ext_arr=array($token=>$str_line);
            }
            $result_array =  $ext_arr;
            return $result_array;
            break;

        case 'NO':
            /* TODO:
                parse the [ .... ] part of the response, use the method
                _getEXTarray(&$str,'[',$stopDelim=']')
            */
            $str_line       = rtrim(substr($this->_getToEOL($str, false), 1));
            $result_array[] = @array('COMMAND' => $token, 'EXT' => $str_line);
            return $result_array;
            break;

        case 'BAD':
            /* TODO:
                parse the [ .... ] part of the response, use the method
                _getEXTarray(&$str,'[',$stopDelim=']')
            */
            $str_line       = rtrim(substr($this->_getToEOL($str, false), 1));
            $result_array[] = array('COMMAND' => $token, 'EXT' => $str_line);
            return $result_array;
            break;

        case 'BYE':
            /* TODO:
                parse the [ .... ] part of the response, use the method
                _getEXTarray(&$str,'[',$stopDelim=']')
            */
            $str_line       = rtrim(substr($this->_getToEOL($str, false), 1));
            $result_array[] = array('COMMAND' => $token, 'EXT' => $str_line);
            return $result_array;
            break;

        case 'LISTRIGHTS':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox);
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $user);
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $granted);

            $ungranted = explode(' ', rtrim(substr($this->_getToEOL($str, false), 1)));

            $result_array = @array('MAILBOX'   => $this->utf7Decode($mailbox),
                                   'USER'      => $user,
                                   'GRANTED'   => $granted,
                                   'UNGRANTED' => $ungranted);
            return $result_array;
            break;

        case 'MYRIGHTS':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox);
            // Patch to handle the alternate MYRIGHTS response from 
            // Courier-IMAP
            if ($str==')') {
                $granted = $mailbox;
                $mailbox = $this->currentMailbox;
            } else {
                $this->_parseSpace($str, __LINE__, __FILE__);
                $this->_getNextToken($str, $granted);
            }
            // End Patch

            $result_array = array('MAILBOX' => $this->utf7Decode($mailbox), 
                                  'GRANTED' => $granted);
            return $result_array;
            break;

        case 'ACL':
            /*
            RFC 4314:
            acl-data        = "ACL" SP mailbox *(SP identifier SP rights)
            identifier      = astring
            rights          = astring ;; only lowercase ASCII letters and 
                                         digits are allowed.
            */
            //$str = " INBOX\r\nA0006 OK Completed\r\n";
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox);
                
            $arr = array();
            while (substr($str, 0, 2) != "\r\n") {
                $this->_parseSpace($str, __LINE__, __FILE__);
                $this->_getNextToken($str, $acl_user);
                $this->_parseSpace($str, __LINE__, __FILE__);
                $this->_getNextToken($str, $acl_rights);
                $arr[] = array('USER' => $acl_user, 'RIGHTS' => $acl_rights);
            }

            $result_array = array('MAILBOX' => $this->utf7Decode($mailbox), 
                                  'USERS'   => $arr);
            return $result_array;
            break;

        case 'ANNOTATION':
            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $mailbox);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $this->_getNextToken($str, $entry);

            $this->_parseSpace($str, __LINE__, __FILE__);
            $attrs = $this->_arrayfyContent($str);

            $result_array = array('MAILBOX'    => $mailbox, 
                                  'ENTRY'      => $entry, 
                                  'ATTRIBUTES' => $attrs);
            return $result_array;
            break;

        case '':
            $this->_protError('PROTOCOL ERROR!:str empty!!', 
                              __LINE__, 
                              __FILE__);
            break;

        case '(':
            $this->_protError('OPENING PARENTHESIS ERROR!!!', 
                              __LINE__, 
                              __FILE__);
            break;

        case ')':
            //"CLOSING PARENTHESIS BREAK!!!!!!!"
            break;

        case "\r\n":
            $this->_protError('BREAK!!!', __LINE__, __FILE__);
            break;

        case ' ':
            // this can happen and we just ignore it
            // This happens when - for example - fetch returns more than 1 
            // parammeter
            // for example you ask to get RFC822.SIZE and UID
            // $this->_protError('SPACE BREAK!!!', __LINE__, __FILE__);
            break;

        default:
            $body_token   = strtoupper(substr($token, 0, 5));
            $rfc822_token = strtoupper(substr($token, 0, 7));

            if ($body_token == 'BODY[' 
                || $body_token == 'BODY.' 
                || $rfc822_token == 'RFC822.') {
                //echo "TOKEN:$token\n";
                //$this->_getNextToken( $str , $mailbox );
                return array($token => $this->_parseContentresponse($str,
                                                                    $token));
            } else {
                $this->_protError('UNIMPLEMMENTED! I don\'t know the '
                                  . 'parameter "' . $token . '"!!!', 
                                  __LINE__, 
                                  __FILE__);
            }
            break;
        }

        return false;
    }



    /**
     * Verifies that the next character IS a space
     *
     * @param string  &$str       String
     * @param int     $line       Line number
     * @param string  $file       File name
     * @param boolean $printError Print errors
     *
     * @return string First character of $str
     * @access private
     */
    function _parseSpace(&$str, $line, $file, $printError = true)
    {
        /*
        This code repeats a lot in this class
        so i make it a function to make all the code shorter
        */
        $this->_getNextToken($str, $space);
        if ($space != ' ') {
            $this->_protError('must be a " " but is a "' . $space . '"!!!!', 
                              $line, 
                              $file,
                              $printError);
        }
        return $space;
    }



    /**
     * parse string for next character after token
     *
     * @param string &$str String
     * @param string $char Next character
     * @param int    $line Line number
     * @param string $file File name
     *
     * @return string Character after next token
     * @access private
     */
    function _parseString(&$str, $char, $line, $file)
    {
        /*
        This code repeats a lot in this class
        so i make it a function to make all the code shorter
        */
        $this->_getNextToken($str, $char_aux);
        if (strtoupper($char_aux) != strtoupper($char)) {
            $this->_protError('must be a "' . $char . '" but is a '
                              . '"' . $char_aux . '"!!!!', 
                              $line, 
                              $file);
        }
        return $char_aux;
    }



    /**
     * parse IMAP response
     *
     * @param string &$str  Response string
     * @param int    $cmdid Command ID
     *
     * @return array Response array
     * @access private
     */
    function _genericImapResponseParser(&$str, $cmdid = null)
    {
        $result_array = array();
        if ($this->_unParsedReturn) {
            $unparsed_str = $str;
        }

        $this->_getNextToken($str, $token);

        while ($token != $cmdid && $str != '') {
            if ($token == '+' ) {
                //if the token  is + ignore the line
                // TODO: verify that this is correct!!!
                $this->_getToEOL($str);
                $this->_getNextToken($str, $token);
            }

            $this->_parseString($str, ' ', __LINE__, __FILE__);

            $this->_getNextToken($str, $token);
            if ($token == '+') {
                $this->_getToEOL($str);
                $this->_getNextToken($str, $token);
            } else {
                if (is_numeric($token)) {
                    // The token is a NUMBER so I store it
                    $msg_nro = $token;
                    $this->_parseSpace($str, __LINE__, __FILE__);

                    // I get the command
                    $this->_getNextToken($str, $command);
 
                    if (($ext_arr = $this->_retrParsedResponse($str, $command, $msg_nro)) == false) {
                        // if this bogus response is a FLAGS () or EXPUNGE 
                        // response the ignore it
                        if ($command != 'FLAGS' && $command != 'EXPUNGE') {
                            $this->_protError('bogus response!!!!', 
                                              __LINE__, 
                                              __FILE__, 
                                              false);
                        }
                    }
                    $result_array[] = array('COMMAND' => $command, 
                                            'NRO'     => $msg_nro, 
                                            'EXT'     => $ext_arr);
                } else {
                    // OK the token is not a NUMBER so it MUST be a COMMAND
                    $command = $token;

                    /* Call the parser return the array
                        take care of bogus responses!
                    */

                    if (($ext_arr = $this->_retrParsedResponse($str, $command)) == false) {
                        $this->_protError('bogus response!!!! (COMMAND:'
                                          . $command. ')', 
                                          __LINE__, 
                                          __FILE__);
                    }
                    $result_array[] = array('COMMAND' => $command, 
                                            'EXT'     => $ext_arr);
                }
            }


            $this->_getNextToken($str, $token);

            $token = strtoupper($token);
            if ($token != "\r\n" && $token != '') {
                $this->_protError('PARSE ERROR!!! must be a "\r\n" here but '
                                  . 'is a "' . $token . '"!!!! (getting the '
                                  . 'next line)|STR:|' . $str. '|', 
                                  __LINE__, 
                                  __FILE__);
            }            
            $this->_getNextToken($str, $token);

            if ($token == '+') {
                //if the token  is + ignore the line
                // TODO: verify that this is correct!!!
                $this->_getToEOL($str);
                $this->_getNextToken($str, $token);
            }
        } //While
        // OK we finish the UNTAGGED Response now we must parse 
        // the FINAL TAGGED RESPONSE
        // TODO: make this a litle more elegant!
        $this->_parseSpace($str, __LINE__, __FILE__, false);

        $this->_getNextToken($str, $cmd_status);

        $str_line = rtrim(substr($this->_getToEOL($str), 1));


        $response['RESPONSE'] = array('CODE'     => $cmd_status, 
                                      'STR_CODE' => $str_line, 
                                      'CMDID'    => $cmdid);

        $ret = $response;
        if (!empty($result_array)) {
            $ret = array_merge($ret, array('PARSED' => $result_array));
        }

        if ($this->_unParsedReturn) {
            $unparsed['UNPARSED'] = $unparsed_str;
            $ret                  = array_merge($ret, $unparsed);
        }

        if (isset($status_arr)) {
            $status['STATUS'] = $status_arr;
            $ret              = array_merge($ret, $status);
        }

        return $ret;
    }



    /**
     * Send generic command
     *
     * @param string $command Command
     * @param string $params  Parameters
     *
     * @return array Parsed response
     * @access private
     */
    function _genericCommand($command, $params = '')
    {
        if (!$this->_connected) {
            return new PEAR_Error('not connected! (CMD:$command)');
        }
        $cmdid = $this->_getCmdId();
        $this->_putCMD($cmdid, $command, $params);
        $args = $this->_getRawResponse($cmdid);
        return $this->_genericImapResponseParser($args, $cmdid);
    }



    /**
     * Encode string to UTF7
     *
     * Use utf7Encode() instead. This method is only for BC.
     *
     * @param string $str String
     *
     * @return string UTF7 encoded string
     * @access public
     * @deprecated Use utf7Encode() instead
     */
    function utf_7_encode($str)
    {
        return utf7Encode($str);
    }



    /**
     * Encode string to UTF7
     *
     * @param string $str String
     *
     * @return string UTF7 encoded string
     * @access public
     */
    function utf7Encode($str)
    {
        if ($this->_useUTF_7 == false) {
            return $str;
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($str, 'UTF7-IMAP', 'ISO-8859-1');
        }
        
        $encoded_utf7 = '';
        $base64_part  = '';
        if (is_array($str)) {
            return new PEAR_Error('error');
        }

        for ($i = 0; $i < $this->_getLineLength($str); $i++) {
            //those chars should be base64 encoded
            if (((ord($str[$i]) >= 39) && (ord($str[$i]) <= 126)) 
                || ((ord($str[$i]) >= 32) && (ord($str[$i]) <= 37))) {
                if ($base64_part) {
                    $encoded_utf7 = sprintf("%s&%s-", 
                                            $encoded_utf7, 
                                            str_replace('=', '', base64_encode($base64_part)));
                    $base64_part  = '';
                }
                $encoded_utf7 = sprintf("%s%s", $encoded_utf7, $str[$i]);
            } else {
                //handle &
                if (ord($str[$i]) == 38 ) {
                    if ($base64_part) {
                        $encoded_utf7 = sprintf("%s&%s-", 
                                                $encoded_utf7, 
                                                str_replace('=', '', base64_encode($base64_part)));
                        $base64_part  = '';
                    }
                    $encoded_utf7 = sprintf("%s&-", $encoded_utf7);
                } else {
                    $base64_part = sprintf("%s%s", $base64_part, $str[$i]);
                    //$base64_part = sprintf("%s%s%s",
                    //                       $base64_part, 
                    //                       chr(0), 
                    //                       $str[$i]);
                }
            }
        }
        if ($base64_part) {
            $encoded_utf7 = sprintf("%s&%s-", 
                                    $encoded_utf7, 
                                    str_replace('=', '', base64_encode($base64_part)));
            $base64_part  = '';
        }

        return $encoded_utf7;
    }



    /**
     * Decode string from UTF7
     *
     * Use utf7Decode() instead. This method is only for BC.
     *
     * @param string $str UTF7 encoded string
     *
     * @return string Decoded string
     * @access public
     * @deprecated Use utf7Decode() instead
     */
    function utf_7_decode($str)
    {
        utf7Decode($str);
    }



    /**
     * Decode string from UTF7
     *
     * @param string $str UTF7 encoded string
     *
     * @return string Decoded string
     * @access public
     */
    function utf7Decode($str)
    {
        if ($this->_useUTF_7 == false) {
            return $str;
        }

        //return imap_utf7_decode($str);

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF7-IMAP');
        }

        $base64_part  = '';
        $decoded_utf7 = '';

        for ($i = 0; $i < $this->_getLineLength($str); $i++) {
            if ($this->_getLineLength($base64_part) > 0) {
                if ($str[$i] == '-') {
                    if ($base64_part == '&') {
                        $decoded_utf7 = sprintf("%s&", $decoded_utf7);
                    } else {
                        $next_part_decoded = base64_decode(substr($base64_part, 1));
                        $decoded_utf7      = sprintf("%s%s", 
                                                $decoded_utf7, 
                                                $next_part_decoded);
                    }
                    $base64_part = '';

                } else {
                    $base64_part = sprintf("%s%s", $base64_part, $str[$i]);
                }
            } else {
                if ($str[$i] == '&') {
                    $base64_part = '&';
                } else {
                    $decoded_utf7 = sprintf("%s%s", $decoded_utf7, $str[$i]);
                }
            }
        }
        return $decoded_utf7;
    }



    /**
     * Make  CREATE/RENAME compatible option params
     *
     * @param array $options options to format
     *
     * @return string Returns a string for formatted parameters
     * @access private
     * @since 1.1
     */
    function _getCreateParams($options)
    {
        $args = '';
        if (is_null($options) === false && is_array($options) === true) {
            foreach ($options as $opt => $data) {
                switch (strtoupper($opt)) {
                case 'PARTITION':
                    $args .= sprintf(" %s", $this->utf7Encode($data));
                    break;

                default:
                    // ignore any unknown options
                    break;

                }
            }
        }
        return $args;
    }



    /**
     * Return true if the TLS negotiation was successful
     *
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    function cmdStartTLS()
    {
        if (PEAR::isError($res = $this->_genericCommand('STARTTLS'))) {
            return $res;
        }

        if (stream_socket_enable_crypto($this->_socket->fp, 
                                        true, 
                                        STREAM_CRYPTO_METHOD_TLS_CLIENT) == false) {
            $msg = 'Failed to establish TLS connection';
            return new PEAR_Error($msg);
        }

        if ($this->_debug === true) {
            echo "STARTTLS Negotiation Successful\n";
        }

        // RFC says we need to query the server capabilities again
        if (PEAR::isError($res = $this->cmdCapability())) {
            $msg = 'Failed to connect, server said: ' . $res->getMessage();
            return new PEAR_Error($msg);
        }
        return true;
    }
    


    /**
     * get the length of string
     *
     * @param string $string String
     *
     * @return int Line length
     * @access private
     */
    function _getLineLength($string) 
    {
        if (extension_loaded('mbstring')) {
            return mb_strlen($string, 'latin1');
        } else {
            return strlen($string);
        }
    }



    /**
     * get substring from string
     *
     * @param string $string String
     * @param int    $start  Position to start from
     * @param int    $length Number of characters
     *
     * @return string Substring
     * @access private
     */
    function _getSubstr($string, $start, $length = false) 
    {
        if (extension_loaded('mbstring')) {
            if ($length !== false) {
                return mb_substr($string, $start, $length, 'latin1');
            } else {
                $strlen = mb_strlen($string, 'latin1');
                return mb_substr($string, $start, $strlen, 'latin1');
            }
        } else {
            if ($length !== false) {
                return substr($string, $start, $length);
            } else {
                return substr($string, $start);
            }
        }
    }

}//Class
?>
