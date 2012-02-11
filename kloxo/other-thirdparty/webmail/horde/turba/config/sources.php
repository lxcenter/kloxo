<?php
/**
 * $Horde: turba/config/sources.php.dist,v 1.97.6.41 2009/08/05 21:06:10 jan Exp $
 *
 * This file is where you specify the sources of contacts available to users
 * at your installation. It contains a large number of EXAMPLES. Please
 * remove or comment out those examples that YOU DON'T NEED. There are a
 * number of properties that you can set for each server, including:
 *
 * title:       This is the common (user-visible) name that you want displayed
 *              in the contact source drop-down box.
 *
 * type:        The types 'ldap', 'sql', 'imsp', 'group', 'favourites' and
 *              'prefs' are currently supported. Preferences-based address
 *              books are not intended for production installs unless you
 *              really know what you're doing - they are not searchable, and
 *              they won't scale well if a user has a large number of entries.
 *
 * params:      These are the connection parameters specific to the contact
 *              source. See below for examples of how to set these.
 *
 * Special params settings:
 *
 *   charset:       The character set that the backend stores data in. Many
 *                  LDAP servers use utf-8. Database servers typically use
 *                  iso-8859-1.
 *
 *   tls:           Only applies to LDAP servers. If true, then try to use a
 *                  TLS connection to the server.
 *
 *   scope:         Only applies to LDAP servers. Can be set to 'one' to
 *                  search one level of the LDAP directory, or 'sub' to search
 *                  all levels. 'one' will work for most setups and should be
 *                  much faster. However we default to 'sub' for backwards
 *                  compatibility.
 *
 *   checkrequired: Only applies to LDAP servers. If present, this value causes
 *                  the driver to consult the LDAP schema for any attributes
 *                  that are required by the given objectclass(es). Required
 *                  attributes will be provided automatically if the
 *                  'checkrequired_string' parameter is present.
 *                  *NOTE* You must have the Net_LDAP PEAR library installed
 *                  for this to work.
 *
 *   checksyntax:   Only applies to LDAP servers. If present, this value causes
 *                  the driver to inspect the LDAP schema for particular
 *                  attributes by the type defined in the corresponding schema
 *                  *NOTE* You must have the Net_LDAP PEAR library installed
 *                  for this to work.
 *
 *   deref:         Only applies to LDAP servers. If set, should be one of:
 *                    LDAP_DEREF_NEVER
 *                    LDAP_DEREF_SEARCHING
 *                    LDAP_DEREF_FINDING
 *                    LDAP_DEREF_ALWAYS
 *                  This tells the LDAP server when to dereference
 *                  aliases. See http://www.php.net/ldap for more
 *                  information.
 *
 *   dn:            Only applies to LDAP servers. Defines the list of LDAP
 *                  attributes that build a valid DN.
 *
 *   root:          Only applies to LDAP servers. Defines the base DN where to
 *                  start the search, i.e. dc=example,dc=com.
 *
 *   bind_dn:       Only applies to LDAP servers which do not allow anonymous
 *                  connections. Active Directory servers do not allow it by
 *                  default, so before using one as a Turba source, you must
 *                  create a "rightless" user, which is only allowed to connect
 *                  to the server, and set the bind_dn parameter like
 *                  'rightless@example.com' (not cn=rightless,dc=example,dc=com)
 *
 *   bind_password: Only applies to LDAP servers which do not allow anonymous
 *                  connection. You should set this to the cleartext password
 *                  for the user specified in 'bind_dn'.
 *
 *   referrals:     Only applies to LDAP servers. If set, should be 0 or 1.
 *                  See the LDAP documentation about the corresponding
 *                  parameter REFERRALS. Windows 2003 Server requires that you
 *                  set this parameter to 0.
 *
 *   sizelimit:     Only applies to LDAP servers. If set, limit the search to
 *                  the specified number of entries. Value 0 or no value means
 *                  no limit. Keep in mind that servers can impose their own
 *                  search limits.
 *
 *   objectclass:   Only applies to LDAP servers. Defines a list of
 *                  objectclasses that contacts must belong to, and that new
 *                  objects will be created with.
 *
 *   filter:        Filter helps to filter your result based on certain
 *                  condition in SQL and LDAP backends. A filter can be
 *                  specified to avoid some unwanted data. For example, if the
 *                  source is an external sql database, to select records with
 *                  the delete flag = 0: 'filter' => 'deleted=0'.
 *                  Don't enclose filter in brackets - this will done
 *                  automatically. Also keep in mind that a full filter line
 *                  will be built from 'filter' and 'objectclass' parameters.
 *
 *   version:       Only applies to LDAP servers. If set, specify LDAP server
 *                  version, can be 2 or 3. Active Directory servers
 *                  require version 3.
 *
 * map:         This is a list of mappings from the Turba attribute names (on
 *              the left) to the attribute names by which they are known in
 *              this contact source (on the right). Turba also supports
 *              composite fields. A composite field is defined by mapping the
 *              field name to an array containing a list of component fields
 *              and a format string (similar to a printf() format string;
 *              however, note that positioned parameters like %1$s will NOT
 *              work). 'attribute' defines where the composed value is saved,
 *              and can be left out. 'parse' defines a list of format strings
 *              and field names that should be used for splitting up composite
 *              fields, in the order of precedence, and can be left out. Here
 *              is an example:
 *              ...
 *              'name' => array('fields' => array('firstname', 'lastname'),
 *                              'format' => '%s %s',
 *                              'attribute' => 'object_name'),
 *              'firstname' => 'object_firstname',
 *              'lastname' => 'object_lastname',
 *              ...
 *
 *              Standard Turba attributes are:
 *                __key     : A backend-specific ID for the entry (any value
 *                            as long as it is unique inside that source;
 *                            required)
 *                __uid     : Globally unique ID of the entry (used for
 *                            synchronizing and must be able to be set to any
 *                            value)
 *                __owner   : User name of the contact's owner
 *                __type    : Either 'Object' or 'Group'
 *                __members : Serialized PHP array with list of Group members.
 *              More Turba attributes are defined in config/attributes.php.
 *
 * tabs:        All fields can be grouped into tabs with this optional entry.
 *              This list is multidimensional hash, the keys are the tab
 *              titles.
 *              Here is an example:
 *              'tabs' => array(
 *                  'Names' => array('firstname', 'lastname', 'alias'),
 *                  'Addresses' => array('homeAddress', 'workAddress')
 *              );
 *
 * search:      A list of Turba attribute names that can be searched for this
 *              source.
 *
 * strict:      A list of native field/attribute names that must
 *              always be matched exactly in a search.
 *
 * approximate: Only applies to LDAP servers. If set, should be an
 *              array of native field/attribute names to search
 *              "approximately" (for example, "Sánchez", "Sanchez",
 *              and "Sanchéz" will all match a search string of
 *              "sanchez").
 *
 * export:      If set to true, this source will appear on the Export menu,
 *              allowing users to export the contacts to a CSV (etc.) file.
 *
 * browse:      If set to true, this source will be browseable via the Browse
 *              menu item, and empty searches against the source will return
 *              all contacts.
 *
 * use_shares:  If this is present and true, Horde_Share functionality will
 *              be enabled for this source - allowing users to share their
 *              personal address books as well as to create new ones. Since
 *              Turba only supports having one backend configured for
 *              creating new shares, use the 'shares' configuration option to
 *              specify which backend will be used for creating new shares.
 *              All permission checking will be done against Horde_Share, but
 *              note that any 'extended' permissions (such as max_contacts)
 *              will still be enforced. Also note that the backend driver
 *              must have support for using this. Currently SQL and IMSP.
 *
 * list_name_field:  If this is present and non-empty, it will be taken as the
 *                   field to store contact list names in.
 *                   This is required when using a composite field as the 'name'
 *                   field.
 *
 * alternative_name: If this is present and non-empty, it will be taken as the
 *                   field to use an alternative in case that the name field is
 *                   empty.
 *
 * Here are some example configurations:
 */

/**
 * A local address book in an SQL database. This implements a private
 * per-user address book. Sharing of this source with other users may be
 * accomplished by enabling Horde_Share for this source by setting
 * 'use_shares' => true.
 *
 * Be sure to create a turba_objects table in your Horde database from the
 * schema in turba/scripts/db/turba.sql if you use this source.
 */
$cfgSources['localsql'] = array(
    'title' => _("My Address Book"),
    'type' => 'sql',
    // The default connection details are pulled from the Horde-wide SQL
    // connection configuration.
    'params' => array_merge($GLOBALS['conf']['sql'], array('table' => 'turba_objects')),
    // Using two tables as datasource.
    // 'params' => array_merge($GLOBALS['conf']['sql'],
    //                         array('table' => 'leaddetails LEFT JOIN leadaddress ON leaddetails.leadid = leadaddress.leadaddressid',
    //                               'filter' => 'leaddetails.converted = 0')),
    'map' => array(
        '__key' => 'object_id',
        '__owner' => 'owner_id',
        '__type' => 'object_type',
        '__members' => 'object_members',
        '__uid' => 'object_uid',
        'firstname' => 'object_firstname',
        'lastname' => 'object_lastname',
        'middlenames' => 'object_middlenames',
        'namePrefix' => 'object_nameprefix',
        'nameSuffix' => 'object_namesuffix',
        'name' => array('fields' => array('namePrefix', 'firstname',
                                          'middlenames', 'lastname',
                                          'nameSuffix'),
                        'format' => '%s %s %s %s %s',
                        'parse' => array(
                            array('fields' => array('firstname', 'middlenames',
                                                    'lastname'),
                                  'format' => '%s %s %s'),
                            array('fields' => array('firstname', 'lastname'),
                                  'format' => '%s %s'))),
        // This is a shorter version of a "name" composite field which only
        // consists of the first name and last name.
        // 'name' => array('fields' => array('firstname', 'lastname'),
        //                 'format' => '%s %s'),
        'alias' => 'object_alias',
        'birthday' => 'object_bday',
        'photo' => 'object_photo',
        'phototype' => 'object_phototype',
        'homeStreet' => 'object_homestreet',
        'homePOBox' => 'object_homepob',
        'homeCity' => 'object_homecity',
        'homeProvince' => 'object_homeprovince',
        'homePostalCode' => 'object_homepostalcode',
        'homeCountry' => 'object_homecountry',
        // This is an example composite field for addresses, so you can display
        // the various map links. If you use this, be sure to add 'homeAddress'
        // to the 'tabs' parameter below.
        // 'homeAddress' => array('fields' => array('homeStreet', 'homeCity',
        //                                          'homeProvince',
        //                                          'homePostalCode'),
        //                        'format' => "%s \n %s, %s  %s"),
        'workStreet' => 'object_workstreet',
        'workPOBox' => 'object_workpob',
        'workCity' => 'object_workcity',
        'workProvince' => 'object_workprovince',
        'workPostalCode' => 'object_workpostalcode',
        'workCountry' => 'object_workcountry',
        'timezone' => 'object_tz',
        'email' => 'object_email',
        'homePhone' => 'object_homephone',
        'workPhone' => 'object_workphone',
        'cellPhone' => 'object_cellphone',
        'fax' => 'object_fax',
        'pager' => 'object_pager',
        'title' => 'object_title',
        'role' => 'object_role',
        'company' => 'object_company',
        'logo' => 'object_logo',
        'logotype' => 'object_logotype',
        'category' => 'object_category',
        'notes' => 'object_notes',
        'website' => 'object_url',
        'freebusyUrl' => 'object_freebusyurl',
        'pgpPublicKey' => 'object_pgppublickey',
        'smimePublicKey' => 'object_smimepublickey',
    ),
    'tabs' => array(
        _("Personal") => array('firstname', 'lastname', 'middlenames',
                               'namePrefix', 'nameSuffix', 'name', 'alias',
                               'birthday', 'photo'),
        _("Location") => array('homeStreet', 'homePOBox', 'homeCity',
                               'homeProvince', 'homePostalCode', 'homeCountry',
                               'workStreet', 'workPOBox', 'workCity',
                               'workProvince', 'workPostalCode', 'workCountry',
                               'timezone'),
        _("Communications") => array('email', 'homePhone', 'workPhone',
                                     'cellPhone', 'fax', 'pager'),
        _("Organization") => array('title', 'role', 'company', 'logo'),
        _("Other") => array('category', 'notes', 'website', 'freebusyUrl',
                            'pgpPublicKey', 'smimePublicKey'),
    ),
    'search' => array(
        'name',
        'email'
    ),
    'strict' => array(
        'object_id',
        'owner_id',
        'object_type',
    ),
    'export' => true,
    'browse' => true,
    'use_shares' => true,
    'list_name_field' => 'lastname',
    'alternative_name' => 'company',
);

/**
 * An address book based on message recipients. This will always be private and
 * read-only. The address book content is provided by the
 * contacts/favouriteRecipients API method which should be implemented by a
 * mail client that collects the most regular message recipients, like IMP
 * 4.2.
 */
$cfgSources['favourites'] = array(
    'title' => _("Favourite Recipients"),
    'type' => 'favourites',
    'params' => array(
        'limit' => 10
    ),
    'map' => array(
        '__key' => 'email',
        'name' => 'email',
        'email' => 'email'
    ),
    'search' => array(
        'name',
        'email'
    ),
    'strict' => array(
        'id',
    ),
    'export' => true,
    'browse' => true,
);
