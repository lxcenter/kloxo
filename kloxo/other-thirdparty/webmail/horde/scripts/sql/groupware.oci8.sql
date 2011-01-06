set doc off;
set sqlblanklines on;

/**
 * Oracle Table Creation Scripts.
 * 
 * $Horde: horde/scripts/sql/create.oci8.sql,v 1.4.8.24 2009-10-19 10:54:32 jan Exp $
 * 
 * @author Miguel Ward <mward@aluar.com.ar>
 * 
 * This sql creates the Horde SQL tables in an Oracle 8.x database. Should
 * work with Oracle 9.x (and Oracle7 using varchar2).
 * 
 * Notes:
 * 
 *  * Obviously you must have Oracle installed on this machine AND you must
 *    have compiled PHP with Oracle (you included --with-oci8-instant
 *    --with-oci8 or in the build arguments for PHP, or uncommented the oci8
 *    extension in php.ini).
 * 
 *  * If you don't use the Instant Client, make sure that the user that starts
 *    up Apache (usually nobody or www-data) has the following environment
 *    variables defined:
 * 
 *    export ORACLE_HOME=/home/oracle/OraHome1
 *    export ORA_NLS=/home/oracle/OraHome1/ocommon/nls/admin/data
 *    export ORA_NLS33=/home/oracle/OraHome1/ocommon/nls/admin/data
 *    export LD_LIBRARY_PATH=$ORACLE_HOME/lib:$LD_LIBRARY_PATH
 * 
 *    YOU MUST CUSTOMIZE THESE VALUES TO BE APPROPRIATE TO YOUR INSTALLATION
 * 
 *    You can include these variables in the user's local .profile or in
 *    /etc/profile, etc.
 * 
 *  * No grants are necessary since we connect as the owner of the tables. If
 *    you wish you can adapt the creation of tables to include tablespace and
 *    storage information. Since we include none it will use the default
 *    tablespace values for the user creating these tables. Same with the
 *    indexes (in theory these should use a different tablespace).
 * 
 *  * There is no need to shut down and start up the database!
 */

rem conn horde/&horde_password@database

/**
 * This is the Horde users table, needed only if you are using SQL
 * authentication.
 */

CREATE TABLE horde_users (
    user_uid                    VARCHAR2(255) NOT NULL,
    user_pass                   VARCHAR2(255) NOT NULL,
    user_soft_expiration_date   NUMBER(16),
    user_hard_expiration_date   NUMBER(16),

    PRIMARY KEY (user_uid)
);

CREATE TABLE horde_signups (
    user_name VARCHAR2(255) NOT NULL,
    signup_date NUMBER(16) NOT NULL,
    signup_host VARCHAR2(255) NOT NULL,
    signup_data CLOB NOT NULL,
    PRIMARY KEY (user_name)
);

CREATE TABLE horde_groups (
    group_uid NUMBER(16) NOT NULL,
    group_name VARCHAR2(255) NOT NULL UNIQUE,
    group_parents VARCHAR2(255) NOT NULL,
    group_email VARCHAR2(255),
    PRIMARY KEY (group_uid)
);

CREATE TABLE horde_groups_members (
    group_uid NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL
);

CREATE INDEX group_uid_idx ON horde_groups_members (group_uid);
CREATE INDEX user_uid_idx ON horde_groups_members (user_uid);

CREATE TABLE horde_perms (
    perm_id NUMBER(16) NOT NULL,
    perm_name VARCHAR2(255) NOT NULL UNIQUE,
    perm_parents VARCHAR2(255) NOT NULL,
    perm_data CLOB,
    PRIMARY KEY (perm_id)
);

/**
 * This is the Horde preferences table, holding all of the user-specific
 * options for every Horde user.
 * 
 * pref_uid   is the username.
 * pref_scope is the application the pref belongs to.
 * pref_name  is the name of the variable to save.
 * pref_value is the value saved (can be very long).
 * 
 * We use a CLOB column so that longer column values are supported.
 * 
 * If still using Oracle 7 this should work but you have to use
 * VARCHAR2(2000) which is the limit imposed by said version.
 */

CREATE TABLE horde_prefs (
    pref_uid    VARCHAR2(255) NOT NULL,
    pref_scope  VARCHAR2(16) NOT NULL,
    pref_name   VARCHAR2(32) NOT NULL,
--  See above notes on CLOBs.
    pref_value  CLOB,

    PRIMARY KEY (pref_uid, pref_scope, pref_name)
);

CREATE INDEX pref_uid_idx ON horde_prefs (pref_uid);
CREATE INDEX pref_scope_idx ON horde_prefs (pref_scope);


/**
 * The DataTree tables are used for holding hierarchical data such as Groups,
 * Permissions, and data for some Horde applications.
 */

CREATE TABLE horde_datatree (
    datatree_id          NUMBER(16) NOT NULL,
    group_uid            VARCHAR2(255) NOT NULL,
    user_uid             VARCHAR2(255),
    datatree_name        VARCHAR2(255) NOT NULL,
    datatree_parents     VARCHAR2(255),
    datatree_order       NUMBER(16),
    datatree_data        CLOB,
    datatree_serialized  NUMBER(1) DEFAULT 0 NOT NULL,

    PRIMARY KEY (datatree_id)
);

CREATE INDEX datatree_datatree_name_idx ON horde_datatree (datatree_name);
CREATE INDEX datatree_group_idx ON horde_datatree (group_uid);
CREATE INDEX datatree_user_idx ON horde_datatree (user_uid);
CREATE INDEX datatree_order_idx ON horde_datatree (datatree_order);
CREATE INDEX datatree_serialized_idx ON horde_datatree (datatree_serialized);
CREATE INDEX datatree_parents_idx ON horde_datatree (datatree_parents);

CREATE TABLE horde_datatree_attributes (
    datatree_id      NUMBER(16) NOT NULL,
    attribute_name   VARCHAR2(255) NOT NULL,
    attribute_key    VARCHAR2(255),
    attribute_value  VARCHAR2(4000)
);

CREATE INDEX datatree_attribute_idx ON horde_datatree_attributes (datatree_id);
CREATE INDEX datatree_attribute_name_idx ON horde_datatree_attributes (attribute_name);
CREATE INDEX datatree_attribute_key_idx ON horde_datatree_attributes (attribute_key);
CREATE INDEX datatree_attribute_value_idx ON horde_datatree_attributes (attribute_value);


CREATE TABLE horde_tokens (
    token_address    VARCHAR2(100) NOT NULL,
    token_id         VARCHAR2(32) NOT NULL,
    token_timestamp  NUMBER(16) NOT NULL,

    PRIMARY KEY (token_address, token_id)
);


CREATE TABLE horde_vfs (
    vfs_id        NUMBER(16) NOT NULL,
    vfs_type      NUMBER(8) NOT NULL,
    vfs_path      VARCHAR2(255),
    vfs_name      VARCHAR2(255) NOT NULL,
    vfs_modified  NUMBER(16) NOT NULL,
    vfs_owner     VARCHAR2(255),
    vfs_data      BLOB,

    PRIMARY KEY   (vfs_id)
);

CREATE INDEX vfs_path_idx ON horde_vfs (vfs_path);
CREATE INDEX vfs_name_idx ON horde_vfs (vfs_name);


CREATE TABLE horde_histories (
    history_id       NUMBER(16) NOT NULL,
    object_uid       VARCHAR2(255) NOT NULL,
    history_action   VARCHAR2(32) NOT NULL,
    history_ts       NUMBER(16) NOT NULL,
    history_desc     CLOB,
    history_who      VARCHAR2(255),
    history_extra    CLOB,

    PRIMARY KEY (history_id)
);

CREATE INDEX history_action_idx ON horde_histories (history_action);
CREATE INDEX history_ts_idx ON horde_histories (history_ts);
CREATE INDEX history_uid_idx ON horde_histories (object_uid);


CREATE TABLE horde_sessionhandler (
    session_id             VARCHAR2(32) NOT NULL,
    session_lastmodified   NUMBER(16) NOT NULL,
    session_data           BLOB,

    PRIMARY KEY (session_id)
);

CREATE INDEX session_lastmodified_idx ON horde_sessionhandler (session_lastmodified);


CREATE TABLE horde_syncml_map (
    syncml_syncpartner VARCHAR2(255) NOT NULL,
    syncml_db          VARCHAR2(255) NOT NULL,
    syncml_uid         VARCHAR2(255) NOT NULL,
    syncml_cuid        VARCHAR2(255),
    syncml_suid        VARCHAR2(255),
    syncml_timestamp   NUMBER(16)
);

CREATE INDEX syncml_syncpartner_idx ON horde_syncml_map (syncml_syncpartner);
CREATE INDEX syncml_db_idx ON horde_syncml_map (syncml_db);
CREATE INDEX syncml_uid_idx ON horde_syncml_map (syncml_uid);
CREATE INDEX syncml_cuid_idx ON horde_syncml_map (syncml_cuid);
CREATE INDEX syncml_suid_idx ON horde_syncml_map (syncml_suid);

CREATE TABLE horde_syncml_anchors(
    syncml_syncpartner  VARCHAR2(255) NOT NULL,
    syncml_db           VARCHAR2(255) NOT NULL,
    syncml_uid          VARCHAR2(255) NOT NULL,
    syncml_clientanchor VARCHAR2(255),
    syncml_serveranchor VARCHAR2(255)
);

CREATE INDEX syncml_anchors_syncpartner_idx ON horde_syncml_anchors (syncml_syncpartner);
CREATE INDEX syncml_anchors_db_idx ON horde_syncml_anchors (syncml_db);
CREATE INDEX syncml_anchors_uid_idx ON horde_syncml_anchors (syncml_uid);


CREATE TABLE horde_alarms (
    alarm_id        VARCHAR2(255) NOT NULL,
    alarm_uid       VARCHAR2(255),
    alarm_start     DATE NOT NULL,
    alarm_end       DATE,
    alarm_methods   VARCHAR2(255),
    alarm_params    CLOB,
    alarm_title     VARCHAR2(255) NOT NULL,
    alarm_text      CLOB,
    alarm_snooze    DATE,
    alarm_dismissed NUMBER(1) DEFAULT 0 NOT NULL,
    alarm_internal  CLOB
);

CREATE INDEX alarm_id_idx ON horde_alarms (alarm_id);
CREATE INDEX alarm_user_idx ON horde_alarms (alarm_uid);
CREATE INDEX alarm_start_idx ON horde_alarms (alarm_start);
CREATE INDEX alarm_end_idx ON horde_alarms (alarm_end);
CREATE INDEX alarm_snooze_idx ON horde_alarms (alarm_snooze);
CREATE INDEX alarm_dismissed_idx ON horde_alarms (alarm_dismissed);

CREATE TABLE horde_cache (
    cache_id          VARCHAR2(32) NOT NULL,
    cache_timestamp   NUMBER(16) NOT NULL,
    cache_expiration  NUMBER(16) NOT NULL,
    cache_data        BLOB,
--
    PRIMARY KEY  (cache_id)
);

CREATE TABLE horde_locks (
    lock_id                  VARCHAR2(36) NOT NULL,
    lock_owner               VARCHAR2(32) NOT NULL,
    lock_scope               VARCHAR2(32) NOT NULL,
    lock_principal           VARCHAR2(255) NOT NULL,
    lock_origin_timestamp    NUMBER(16) NOT NULL,
    lock_update_timestamp    NUMBER(16) NOT NULL,
    lock_expiry_timestamp    NUMBER(16) NOT NULL,
    lock_type                NUMBER(8) NOT NULL,

    PRIMARY KEY (lock_id)
);

exit

CREATE TABLE imp_sentmail (
    sentmail_id        NUMBER(16) NOT NULL,
    sentmail_who       VARCHAR2(255) NOT NULL,
    sentmail_ts        NUMBER(16) NOT NULL,
    sentmail_messageid VARCHAR2(255) NOT NULL,
    sentmail_action    VARCHAR2(32) NOT NULL,
    sentmail_recipient VARCHAR2(255) NOT NULL,
    sentmail_success   NUMBER(1) NOT NULL,
--
    PRIMARY KEY (sentmail_id)
);

CREATE INDEX sentmail_ts_idx ON imp_sentmail (sentmail_ts);
CREATE INDEX sentmail_who_idx ON imp_sentmail (sentmail_who);
CREATE INDEX sentmail_success_idx ON imp_sentmail (sentmail_success);

-- $Horde: turba/scripts/sql/turba.oci8.sql,v 1.1.2.11 2009-10-20 21:44:34 jan Exp $

CREATE TABLE turba_objects (
    object_id VARCHAR2(32) NOT NULL,
    owner_id VARCHAR2(255) NOT NULL,
    object_type VARCHAR2(255) DEFAULT 'Object' NOT NULL,
    object_uid VARCHAR2(255),
    object_members CLOB,
    object_firstname VARCHAR2(255),
    object_lastname VARCHAR2(255),
    object_middlenames VARCHAR2(255),
    object_nameprefix VARCHAR2(32),
    object_namesuffix VARCHAR2(32),
    object_alias VARCHAR2(32),
    object_photo BLOB,
    object_phototype VARCHAR2(10),
    object_bday VARCHAR2(10),
    object_homestreet VARCHAR2(255),
    object_homepob VARCHAR2(10),
    object_homecity VARCHAR2(255),
    object_homeprovince VARCHAR2(255),
    object_homepostalcode VARCHAR2(10),
    object_homecountry VARCHAR2(255),
    object_workstreet VARCHAR2(255),
    object_workpob VARCHAR2(10),
    object_workcity VARCHAR2(255),
    object_workprovince VARCHAR2(255),
    object_workpostalcode VARCHAR2(10),
    object_workcountry VARCHAR2(255),
    object_tz VARCHAR2(32),
    object_geo VARCHAR2(255),
    object_email VARCHAR2(255),
    object_homephone VARCHAR2(25),
    object_workphone VARCHAR2(25),
    object_cellphone VARCHAR2(25),
    object_fax VARCHAR2(25),
    object_pager VARCHAR2(25),
    object_title VARCHAR2(255),
    object_role VARCHAR2(255),
    object_logo BLOB,
    object_logotype VARCHAR2(10),
    object_company VARCHAR2(255),
    object_category VARCHAR2(80),
    object_notes CLOB,
    object_url VARCHAR2(255),
    object_freebusyurl VARCHAR2(255),
    object_pgppublickey CLOB,
    object_smimepublickey CLOB,
    PRIMARY KEY(object_id)
);

CREATE INDEX turba_owner_idx ON turba_objects (owner_id);
CREATE INDEX turba_email_idx ON turba_objects (object_email);
CREATE INDEX turba_firstname_idx ON turba_objects (object_firstname);
CREATE INDEX turba_lastname_idx ON turba_objects (object_lastname);

CREATE TABLE turba_shares (
    share_id NUMBER(16) NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(255) NOT NULL,
    share_flags NUMBER(8) DEFAULT 0 NOT NULL,
    perm_creator NUMBER(8) DEFAULT 0 NOT NULL,
    perm_default NUMBER(8) DEFAULT 0 NOT NULL,
    perm_guest NUMBER(8) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    attribute_params VARCHAR2(4000),
    PRIMARY KEY (share_id)
);

CREATE INDEX turba_shares_name_idx ON turba_shares (share_name);
CREATE INDEX turba_shares_owner_idx ON turba_shares (share_owner);
CREATE INDEX turba_shares_creator_idx ON turba_shares (perm_creator);
CREATE INDEX turba_shares_default_idx ON turba_shares (perm_default);
CREATE INDEX turba_shares_guest_idx ON turba_shares (perm_guest);

CREATE TABLE turba_shares_groups (
    share_id NUMBER(16) NOT NULL,
    group_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX turba_groups_share_id_idx ON turba_shares_groups (share_id);
CREATE INDEX turba_groups_group_uid_idx ON turba_shares_groups (group_uid);
CREATE INDEX turba_groups_perm_idx ON turba_shares_groups (perm);

CREATE TABLE turba_shares_users (
    share_id NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX turba_users_share_id_idx ON turba_shares_users (share_id);
CREATE INDEX turba_users_user_uid_idx ON turba_shares_users (user_uid);
CREATE INDEX turba_users_perm_idx ON turba_shares_users (perm);

-- $Horde: ingo/scripts/sql/ingo.oci8.sql,v 1.3.2.10 2009-10-20 21:44:32 jan Exp $

CREATE TABLE ingo_rules (
    rule_id NUMBER(16) NOT NULL,
    rule_owner VARCHAR2(255) NOT NULL,
    rule_name VARCHAR2(255) NOT NULL,
    rule_action NUMBER(16) NOT NULL,
    rule_value VARCHAR2(255),
    rule_flags NUMBER(16),
    rule_conditions CLOB,
    rule_combine NUMBER(16),
    rule_stop NUMBER(1),
    rule_active NUMBER(1) DEFAULT 1 NOT NULL,
    rule_order NUMBER(16) DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (rule_id)
);

CREATE INDEX rule_owner_idx ON ingo_rules (rule_owner);


CREATE TABLE ingo_lists (
    list_owner VARCHAR2(255) NOT NULL,
    list_blacklist NUMBER(1) DEFAULT 0,
    list_address VARCHAR2(255) NOT NULL
);

CREATE INDEX list_idx ON ingo_lists (list_owner, list_blacklist);


CREATE TABLE ingo_forwards (
    forward_owner VARCHAR2(255) NOT NULL,
    forward_addresses CLOB,
    forward_keep NUMBER(16) DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (forward_owner)
);


CREATE TABLE ingo_vacations (
    vacation_owner VARCHAR2(255) NOT NULL,
    vacation_addresses CLOB,
    vacation_subject VARCHAR2(255),
    vacation_reason CLOB,
    vacation_days NUMBER(16) DEFAULT 7,
    vacation_start NUMBER(16),
    vacation_end NUMBER(16),
    vacation_excludes CLOB,
    vacation_ignorelists NUMBER(1) DEFAULT 1,
--
    PRIMARY KEY (vacation_owner)
);


CREATE TABLE ingo_spam (
    spam_owner VARCHAR2(255) NOT NULL,
    spam_level NUMBER(16) DEFAULT 5,
    spam_folder VARCHAR2(255),
--
    PRIMARY KEY (spam_owner)
);


CREATE TABLE ingo_shares (
    share_id NUMBER(16) NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(255) NOT NULL,
    share_flags NUMBER(8) DEFAULT 0 NOT NULL,
    perm_creator NUMBER(8) DEFAULT 0 NOT NULL,
    perm_default NUMBER(8) DEFAULT 0 NOT NULL,
    perm_guest NUMBER(8) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX ingo_shares_name_idx ON ingo_shares (share_name);
CREATE INDEX ingo_shares_owner_idx ON ingo_shares (share_owner);
CREATE INDEX ingo_shares_creator_idx ON ingo_shares (perm_creator);
CREATE INDEX ingo_shares_default_idx ON ingo_shares (perm_default);
CREATE INDEX ingo_shares_guest_idx ON ingo_shares (perm_guest);

CREATE TABLE ingo_shares_groups (
    share_id NUMBER(16) NOT NULL,
    group_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX ingo_groups_share_id_idx ON ingo_shares_groups (share_id);
CREATE INDEX ingo_groups_group_uid_idx ON ingo_shares_groups (group_uid);
CREATE INDEX ingo_groups_perm_idx ON ingo_shares_groups (perm);

CREATE TABLE ingo_shares_users (
    share_id NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX ingo_users_share_id_idx ON ingo_shares_users (share_id);
CREATE INDEX ingo_users_user_uid_idx ON ingo_shares_users (user_uid);
CREATE INDEX ingo_users_perm_idx ON ingo_shares_users (perm);

-- $Horde: kronolith/scripts/sql/kronolith.oci8.sql,v 1.4.2.15 2009-10-22 16:48:56 jan Exp $

CREATE TABLE kronolith_events (
    event_id VARCHAR2(32) NOT NULL,
    event_uid VARCHAR2(255) NOT NULL,
    calendar_id VARCHAR2(255) NOT NULL,
    event_creator_id VARCHAR2(255) NOT NULL,
    event_description VARCHAR2(4000),
    event_location VARCHAR2(4000),
    event_status NUMBER(8) DEFAULT 0,
    event_attendees VARCHAR2(4000),
    event_keywords VARCHAR2(4000),
    event_exceptions VARCHAR2(4000),
    event_title VARCHAR2(255),
    event_category VARCHAR2(80),
    event_recurtype NUMBER(8) DEFAULT 0,
    event_recurinterval NUMBER(16),
    event_recurdays NUMBER(16),
    event_recurenddate DATE,
    event_recurcount NUMBER(8),
    event_start DATE,
    event_end DATE,
    event_alarm NUMBER(16) DEFAULT 0,
    event_modified NUMBER(16) NOT NULL,
    event_private NUMBER(1) DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (event_id)
);

CREATE INDEX kronolith_calendar_idx ON kronolith_events (calendar_id);
CREATE INDEX kronolith_uid_idx ON kronolith_events (event_uid);


CREATE TABLE kronolith_storage (
    vfb_owner      VARCHAR2(255),
    vfb_email      VARCHAR2(255) NOT NULL,
    vfb_serialized VARCHAR2(4000) NOT NULL
);

CREATE INDEX kronolith_vfb_owner_idx ON kronolith_storage (vfb_owner);
CREATE INDEX kronolith_vfb_email_idx ON kronolith_storage (vfb_email);


CREATE TABLE kronolith_shares (
    share_id NUMBER(16) NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(255) NOT NULL,
    share_flags NUMBER(8) DEFAULT 0 NOT NULL,
    perm_creator NUMBER(8) DEFAULT 0 NOT NULL,
    perm_default NUMBER(8) DEFAULT 0 NOT NULL,
    perm_guest NUMBER(8) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX kronolith_share_name_idx ON kronolith_shares (share_name);
CREATE INDEX kronolith_share_owner_idx ON kronolith_shares (share_owner);
CREATE INDEX kronolith_perm_creator_idx ON kronolith_shares (perm_creator);
CREATE INDEX kronolith_perm_default_idx ON kronolith_shares (perm_default);
CREATE INDEX kronolith_perm_guest_idx ON kronolith_shares (perm_guest);

CREATE TABLE kronolith_shares_groups (
    share_id NUMBER(16) NOT NULL,
    group_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX kronolith_groups_share_id_idx ON kronolith_shares_groups (share_id);
CREATE INDEX kronolith_groups_group_uid_idx ON kronolith_shares_groups (group_uid);
CREATE INDEX kronolith_groups_perm_idx ON kronolith_shares_groups (perm);

CREATE TABLE kronolith_shares_users (
    share_id NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX kronolith_users_share_id_idx ON kronolith_shares_users (share_id);
CREATE INDEX kronolith_users_user_uid_idx ON kronolith_shares_users (user_uid);
CREATE INDEX kronolith_users_perm_idx ON kronolith_shares_users (perm);

-- $Horde: nag/scripts/sql/nag.oci8.sql,v 1.1.2.12 2009-10-22 14:24:20 jan Exp $

CREATE TABLE nag_tasks (
    task_id              VARCHAR2(32) NOT NULL,
    task_owner           VARCHAR2(255) NOT NULL,
    task_creator         VARCHAR2(255) NOT NULL,
    task_parent          VARCHAR2(255),
    task_assignee        VARCHAR2(255),
    task_name            VARCHAR2(255) NOT NULL,
    task_uid             VARCHAR2(255) NOT NULL,
    task_desc            CLOB,
    task_start           NUMBER(16),
    task_due             NUMBER(16),
    task_priority        NUMBER(8) DEFAULT 0 NOT NULL,
    task_estimate        FLOAT,
    task_category        VARCHAR2(80),
    task_completed       NUMBER(1) DEFAULT 0 NOT NULL,
    task_completed_date  NUMBER(16),
    task_alarm           NUMBER(16) DEFAULT 0 NOT NULL,
    task_private         NUMBER(1) DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (task_id)
);

CREATE INDEX nag_tasklist_idx ON nag_tasks (task_owner);
CREATE INDEX nag_uid_idx ON nag_tasks (task_uid);
CREATE INDEX nag_start_idx ON nag_tasks (task_start);

CREATE TABLE nag_shares (
    share_id NUMBER(16) NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(25) NOT NULL,
    share_flags NUMBER(8) DEFAULT 0 NOT NULL,
    perm_creator NUMBER(8) DEFAULT 0 NOT NULL,
    perm_default NUMBER(8) DEFAULT 0 NOT NULL,
    perm_guest NUMBER(8) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX nag_shares_name_idx ON nag_shares (share_name);
CREATE INDEX nag_shares_owner_idx ON nag_shares (share_owner);
CREATE INDEX nag_shares_creator_idx ON nag_shares (perm_creator);
CREATE INDEX nag_shares_default_idx ON nag_shares (perm_default);
CREATE INDEX nag_shares_guest_idx ON nag_shares (perm_guest);

CREATE TABLE nag_shares_groups (
    share_id NUMBER(16) NOT NULL,
    group_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX nag_groups_share_id_idx ON nag_shares_groups (share_id);
CREATE INDEX nag_groups_group_uid_idx ON nag_shares_groups (group_uid);
CREATE INDEX nag_groups_perm_idx ON nag_shares_groups (perm);

CREATE TABLE nag_shares_users (
    share_id NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX nag_users_share_id_idx ON nag_shares_users (share_id);
CREATE INDEX nag_users_user_uid_idx ON nag_shares_users (user_uid);
CREATE INDEX nag_users_perm_idx ON nag_shares_users (perm);

-- $Horde: mnemo/scripts/sql/mnemo.oci8.sql,v 1.1.2.12 2009-10-20 21:44:35 jan Exp $

CREATE TABLE mnemo_memos (
    memo_owner      VARCHAR2(255) NOT NULL,
    memo_id         VARCHAR2(32) NOT NULL,
    memo_uid        VARCHAR2(255) NOT NULL,
    memo_desc       VARCHAR2(64) NOT NULL,
    memo_body       VARCHAR2(4000),
    memo_category   VARCHAR2(80),
    memo_private    NUMBER(1) DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (memo_owner, memo_id)
);

CREATE INDEX mnemo_notepad_idx ON mnemo_memos (memo_owner);
CREATE INDEX mnemo_uid_idx ON mnemo_memos (memo_uid);

CREATE TABLE mnemo_shares (
    share_id NUMBER(16) NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(255) NOT NULL,
    share_flags NUMBER(8) DEFAULT 0 NOT NULL,
    perm_creator NUMBER(8) DEFAULT 0 NOT NULL,
    perm_default NUMBER(8) DEFAULT 0 NOT NULL,
    perm_guest NUMBER(8) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX mnemo_shares_name_idx ON mnemo_shares (share_name);
CREATE INDEX mnemo_shares_owner_idx ON mnemo_shares (share_owner);
CREATE INDEX mnemo_shares_creator_idx ON mnemo_shares (perm_creator);
CREATE INDEX mnemo_shares_default_idx ON mnemo_shares (perm_default);
CREATE INDEX mnemo_shares_guest_idx ON mnemo_shares (perm_guest);

CREATE TABLE mnemo_shares_groups (
    share_id NUMBER(16) NOT NULL,
    group_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX mnemo_groups_share_id_idx ON mnemo_shares_groups (share_id);
CREATE INDEX mnemo_groups_group_uid_idx ON mnemo_shares_groups (group_uid);
CREATE INDEX mnemo_groups_perm_idx ON mnemo_shares_groups (perm);

CREATE TABLE mnemo_shares_users (
    share_id NUMBER(16) NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm NUMBER(8) NOT NULL
);

CREATE INDEX mnemo_users_share_id_idx ON mnemo_shares_users (share_id);
CREATE INDEX mnemo_users_user_uid_idx ON mnemo_shares_users (user_uid);
CREATE INDEX mnemo_users_perm_idx ON mnemo_shares_users (perm);

