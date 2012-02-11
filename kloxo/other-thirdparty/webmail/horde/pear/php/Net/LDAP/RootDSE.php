<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
* RootDSE.php
*
* PHP version 4, 5
*
* @category  Net
* @package   Net_LDAP
* @author    Tarjej Huse <tarjei@bergfald.no>
* @author    Jan Wagner <wagner@netsols.de>
* @author    Del <del@babel.com.au>
* @author    Benedikt Hallinger <beni@php.net>
* @copyright 2003-2007 Tarjej Huse, Jan Wagner, Del Elson, Benedikt Hallinger
* @license   http://www.gnu.org/copyleft/lesser.html LGPL
* @version   CVS: $Id: RootDSE.php,v 1.12 2008/10/26 15:31:06 clockwerx Exp $
* @link      http://pear.php.net/package/Net_LDAP/
*/
require_once 'PEAR.php';

/**
* Getting the rootDSE entry of a LDAP server
*
* @category Net
* @package  Net_LDAP
* @author   Jan Wagner <wagner@netsols.de>
* @license  http://www.gnu.org/copyleft/lesser.html LGPL
* @link     http://pear.php.net/package/Net_LDAP/
*/
class Net_LDAP_RootDSE extends PEAR
{
    /**
    * @access private
    * @var object Net_LDAP_Entry
    **/
    var $_entry;

    /**
    * Class constructor
    *
    * @param Net_LDAP_Entry &$entry Net_LDAP_Entry object
    */
    function Net_LDAP_RootDSE(&$entry)
    {
        $this->_entry = $entry;
    }

    /**
    * Gets the requested attribute value
    *
    * Same usuage as {@link Net_LDAP_Entry::getValue()}
    *
    * @param string $attr    Attribute name
    * @param array  $options Array of options
    *
    * @access public
    * @return mixed Net_LDAP_Error object or attribute values
    * @see Net_LDAP_Entry::get_value()
    */
    function getValue($attr = '', $options = '')
    {
        return $this->_entry->get_value($attr, $options);
    }

    /**
    * Alias function of getValue() for perl-ldap interface
    *
    * @see getValue()
    */
    function get_value()
    {
        $args = func_get_args();
        return call_user_func_array(array( &$this, 'getValue' ), $args);
    }

    /**
    * Determines if the extension is supported
    *
    * @param array $oids Array of oids to check
    *
    * @access public
    * @return boolean
    */
    function supportedExtension($oids)
    {
        return $this->_checkAttr($oids, 'supportedExtension');
    }

    /**
    * Alias function of supportedExtension() for perl-ldap interface
    *
    * @see supportedExtension()
    */
    function supported_extension()
    {
        $args = func_get_args();
        return call_user_func_array(array( &$this, 'supportedExtension'), $args);
    }

    /**
    * Determines if the version is supported
    *
    * @param array $versions Versions to check
    *
    * @access public
    * @return boolean
    */
    function supportedVersion($versions)
    {
        return $this->_checkAttr($versions, 'supportedLDAPVersion');
    }

    /**
    * Alias function of supportedVersion() for perl-ldap interface
    *
    * @see supportedVersion()
    */
    function supported_version()
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this, 'supportedVersion'), $args);
    }

    /**
    * Determines if the control is supported
    *
    * @param array $oids Control oids to check
    *
    * @access public
    * @return boolean
    */
    function supportedControl($oids)
    {
        return $this->_checkAttr($oids, 'supportedControl');
    }

    /**
    * Alias function of supportedControl() for perl-ldap interface
    *
    * @see supportedControl()
    */
    function supported_control()
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this, 'supportedControl' ), $args);
    }

    /**
    * Determines if the sasl mechanism is supported
    *
    * @param array $mechlist SASL mechanisms to check
    *
    * @access public
    * @return boolean
    */
    function supportedSASLMechanism($mechlist)
    {
        return $this->_checkAttr($mechlist, 'supportedSASLMechanisms');
    }

    /**
    * Alias function of supportedSASLMechanism() for perl-ldap interface
    *
    * @see supportedSASLMechanism()
    */
    function supported_sasl_mechanism() 
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this, 'supportedSASLMechanism'), $args);
    }

    /**
    * Checks for existance of value in attribute
    *
    * @param array  $values values to check
    * @param string $attr   attribute name
    *
    * @access private
    * @return boolean
    */
    function _checkAttr($values, $attr)
    {
        if (!is_array($values)) $values = array($values);

        foreach ($values as $value) {
            if (!@in_array($value, $this->get_value($attr, 'all'))) {
                return false;
            }
        }
        return true;
    }
}

?>
