-- $Horde: horde/scripts/sql/create.sybase.sql,v 1.1.10.18 2009/06/10 16:45:28 jan Exp $
--
-- horde tables definitions : sql script
-- 01/22/2003 - F. Helly <francois.helly@wanadoo.fr>
-- command line syntax :  isql -ihorde_sybase.sql
-- warning : use nvarchar only if you need unicode encoding for some strings

use horde
go


DROP TABLE horde_datatree
go

DROP TABLE horde_prefs
go

DROP TABLE horde_users
go

DROP TABLE horde_signups
go

DROP TABLE horde_groups
go

DROP TABLE horde_groups_members
go

DROP TABLE horde_sessionhandler
go

-- DROP TABLE horde_datatree_seq
-- go

-- DROP TABLE horde_tokens
-- go

-- DROP TABLE horde_vfs
-- go

-- DROP TABLE horde_muvfs
-- go


CREATE TABLE horde_users (
    user_uid varchar(256) NOT NULL,
    user_pass varchar(256) NOT NULL,
    user_soft_expiration_date numeric(10,0),
    user_hard_expiration_date numeric(10,0),
    PRIMARY KEY (user_uid)
)
go

CREATE TABLE horde_signups (
    user_name VARCHAR(255) NOT NULL,
    signup_date NUMERIC(10,0) NOT NULL,
    signup_host VARCHAR(255) NOT NULL,
    signup_data TEXT NOT NULL,
    PRIMARY KEY (user_name)
)
go

CREATE TABLE horde_groups (
    group_uid numeric(11,0) IDENTITY NOT NULL,
    group_name varchar(256) NOT NULL,
    group_parents varchar(256) NOT NULL,
    group_email varchar(256),
    PRIMARY KEY (group_uid)
)
go

CREATE TABLE horde_groups_members (
    group_uid numeric(11,0) NOT NULL,
    user_uid varchar(256) NOT NULL
)
go

CREATE INDEX group_uid_idx ON horde_groups_members (group_uid)
CREATE INDEX user_uid_idx ON horde_groups_members (user_uid)
go

CREATE TABLE horde_perms (
    perm_id numeric(11,0) NOT NULL,
    perm_name varchar(256) NOT NULL,
    perm_parents varchar(256) NOT NULL,
    perm_data text,
    PRIMARY KEY (perm_id)
)
go

CREATE TABLE horde_datatree (
    datatree_id numeric(11,0) IDENTITY NOT NULL,
    group_uid varchar(256) NOT NULL,
    user_uid varchar(256) NOT NULL,
    datatree_name varchar(256) NOT NULL,
    datatree_parents varchar(256) NULL,
    datatree_data text NULL,
    datatree_serialized smallint DEFAULT 0 NOT NULL,
    PRIMARY KEY (datatree_id),
    FOREIGN KEY (user_uid)
    REFERENCES horde_users(user_uid)
)
go

CREATE TABLE horde_prefs (
    pref_uid varchar(256) NOT NULL,
    pref_scope varchar(16) NOT NULL,
    pref_name varchar(32) NOT NULL,
    pref_value text NULL,
    PRIMARY KEY (pref_uid,pref_scope,pref_name)
)
go

CREATE TABLE horde_sessionhandler (
    session_id varchar(32) NOT NULL,
    session_lastmodified numeric(11,0) NOT NULL,
    session_data image NULL,
    PRIMARY KEY (session_id)
)
go

CREATE TABLE horde_syncml_map (
    syncml_syncpartner varchar(255) NOT NULL,
    syncml_db          varchar(255) NOT NULL,
    syncml_uid         varchar(255) NOT NULL,
    syncml_cuid        varchar(255),
    syncml_suid        varchar(255),
    syncml_timestamp   numeric(11,0)
);
go

CREATE TABLE horde_syncml_anchors(
    syncml_syncpartner  varchar(255) NOT NULL,
    syncml_db           varchar(255) NOT NULL,
    syncml_uid          varchar(255) NOT NULL,
    syncml_clientanchor varchar(255),
    syncml_serveranchor varchar(255)
);
go

CREATE TABLE horde_alarms (
    alarm_id        varchar(255) NOT NULL,
    alarm_uid       varchar(255),
    alarm_start     timestamp NOT NULL,
    alarm_end       timestamp,
    alarm_methods   varchar(255),
    alarm_params    text,
    alarm_title     varchar(255) NOT NULL,
    alarm_text      text,
    alarm_snooze    timestamp,
    alarm_dismissed smallint DEFAULT 0 NOT NULL,
    alarm_internal  text
)
go

CREATE TABLE horde_cache (
    cache_id          varchar(32) NOT NULL,
    cache_timestamp   numeric(11,0) NOT NULL,
    cache_expiration  numeric(11,0) NOT NULL,
    cache_data        image,
    PRIMARY KEY  (cache_id)
)
go

CREATE TABLE horde_locks (
    lock_id                  varchar(36) NOT NULL,
    lock_owner               varchar(32) NOT NULL,
    lock_scope               varchar(32) NOT NULL,
    lock_principal           varchar(255) NOT NULL,
    lock_origin_timestamp    numeric(11, 0) NOT NULL,
    lock_update_timestamp    numeric(11, 0) NOT NULL,
    lock_expiry_timestamp    numeric(11, 0) NOT NULL,
    lock_type                numeric(11, 0) NOT NULL,

    PRIMARY KEY (lock_id)
)
go

-- CREATE TABLE horde_datatree_seq (
--   id numeric(10,0) IDENTITY NOT NULL,
--   PRIMARY KEY (id)
-- )
-- go

-- CREATE TABLE horde_tokens (
--   token_address varchar(100) NOT NULL,
--   token_id varchar(32) NOT NULL,
--   token_timestamp numeric(20,0) NOT NULL,
--   PRIMARY KEY (token_address,token_id)
-- )
-- go

-- CREATE TABLE horde_vfs (
--   vfs_id numeric(20,0) NOT NULL,
--   vfs_type numeric(8,0) NOT NULL,
--   vfs_path varchar(256) NOT NULL,
--   vfs_name nvarchar(256) NOT NULL,
--   vfs_modified numeric(20,0) NOT NULL,
--   vfs_owner varchar(256) NOT NULL,
--   vfs_data image NULL,
--   PRIMARY KEY (vfs_id)
-- )
-- go

-- CREATE TABLE horde_muvfs (
--   vfs_id  numeric(20,0) NOT NULL,
--   vfs_type      numeric(8,0) NOT NULL,
--   vfs_path      varchar(256) NOT NULL,
--   vfs_name      varchar(256) NOT NULL,
--   vfs_modified  numeric(8,0) NOT NULL,
--   vfs_owner     varchar(256) NOT NULL,
--   vfs_perms     smallint NOT NULL,
--   vfs_data      image NULL,
--   PRIMARY KEY (vfs_id)
--   )
-- go


CREATE INDEX pref_uid_idx ON horde_prefs (pref_uid)
go

CREATE INDEX pref_scope_idx ON horde_prefs (pref_scope)
go

CREATE INDEX datatree_datatree_name_idx ON horde_datatree (datatree_name)
go

CREATE INDEX datatree_group_idx ON horde_datatree (group_uid)
go

CREATE INDEX datatree_user_idx ON horde_datatree (user_uid)
go

CREATE INDEX datatree_serialized_idx ON horde_datatree (datatree_serialized)
go

CREATE INDEX datatree_parents_idx ON horde_datatree (datatree_parents)
go

CREATE INDEX syncml_syncpartner_idx ON horde_syncml_map (syncml_syncpartner);
go

CREATE INDEX syncml_db_idx ON horde_syncml_map (syncml_db);
go

CREATE INDEX syncml_uid_idx ON horde_syncml_map (syncml_uid);
go

CREATE INDEX syncml_cuid_idx ON horde_syncml_map (syncml_cuid);
go

CREATE INDEX syncml_suid_idx ON horde_syncml_map (syncml_suid);
go

CREATE INDEX syncml_anchors_syncpartner_idx ON horde_syncml_anchors (syncml_syncpartner);
go

CREATE INDEX syncml_anchors_db_idx ON horde_syncml_anchors (syncml_db);
go

CREATE INDEX syncml_anchors_uid_idx ON horde_syncml_anchors (syncml_uid);
go

CREATE INDEX alarm_id_idx ON horde_alarms (alarm_id)
go

CREATE INDEX alarm_user_idx ON horde_alarms (alarm_uid)
go

CREATE INDEX alarm_start_idx ON horde_alarms (alarm_start)
go

CREATE INDEX alarm_end_idx ON horde_alarms (alarm_end)
go

CREATE INDEX alarm_snooze_idx ON horde_alarms (alarm_snooze)
go

CREATE INDEX alarm_dismissed_idx ON horde_alarms (alarm_dismissed)
go

CREATE INDEX session_lastmodified_idx ON horde_sessionhandler (session_lastmodified)
go

-- CREATE INDEX vfs_path_idx ON horde_vfs (vfs_path)
-- go

-- CREATE INDEX vfs_name_idx ON horde_vfs (vfs_name)
-- go

-- CREATE INDEX vfs_path_idx ON horde_muvfs (vfs_path)
-- go

-- CREATE INDEX vfs_name_idx ON horde_muvfs (vfs_name)
-- go


grant select, insert, delete, update on editor to horde
go
grant select, insert, delete, update on host to horde
go
grant select, insert, delete, update on dbase to horde
go
grant select, insert, delete, update on site to horde
go
grant select, insert, delete, update on connection to horde
go
grant select, insert, delete, update on horde_datatree to horde
go
grant select, insert, delete, update on horde_prefs to horde
go
grant select, insert, delete, update on horde_sessionhandler to horde
go
grant select, insert, delete, update on horde_syncml_map to horde
go
grant select, insert, delete, update on horde_alarms to horde
go
grant select, insert, delete, update on horde_cache to horde
go

-- grant select, insert, delete, update on horde_datatree_seq to horde
-- go
-- grant select, insert, delete, update on horde_tokens to horde
-- go
-- grant select, insert, delete, update on horde_vfs to horde
-- go
-- grant select, insert, delete, update on horde_muvfs to horde
-- go



-- add you admin_user_uid and admin_user_pass

-- insert into horde_users values ('your_admin_user_uid', 'your_admin_user_pass_md5_encrypted')
-- go

-- $Horde: imp/scripts/sql/imp.sql,v 1.1.2.1 2007/12/20 14:00:36 jan Exp $

CREATE TABLE imp_sentmail (
    sentmail_id        BIGINT NOT NULL,
    sentmail_who       VARCHAR(255) NOT NULL,
    sentmail_ts        BIGINT NOT NULL,
    sentmail_messageid VARCHAR(255) NOT NULL,
    sentmail_action    VARCHAR(32) NOT NULL,
    sentmail_recipient VARCHAR(255) NOT NULL,
    sentmail_success   INT NOT NULL,
--
    PRIMARY KEY (sentmail_id)
);

CREATE INDEX sentmail_ts_idx ON imp_sentmail (sentmail_ts);
CREATE INDEX sentmail_who_idx ON imp_sentmail (sentmail_who);
CREATE INDEX sentmail_success_idx ON imp_sentmail (sentmail_success);

-- $Horde: turba/scripts/sql/turba.sql,v 1.1.2.9 2009/10/20 21:44:34 jan Exp $

CREATE TABLE turba_objects (
    object_id VARCHAR(32) NOT NULL,
    owner_id VARCHAR(255) NOT NULL,
    object_type VARCHAR(255) DEFAULT 'Object' NOT NULL,
    object_uid VARCHAR(255),
    object_members BLOB,
    object_firstname VARCHAR(255),
    object_lastname VARCHAR(255),
    object_middlenames VARCHAR(255),
    object_nameprefix VARCHAR(32),
    object_namesuffix VARCHAR(32),
    object_alias VARCHAR(32),
    object_photo BLOB,
    object_phototype VARCHAR(10),
    object_bday VARCHAR(10),
    object_homestreet VARCHAR(255),
    object_homepob VARCHAR(10),
    object_homecity VARCHAR(255),
    object_homeprovince VARCHAR(255),
    object_homepostalcode VARCHAR(10),
    object_homecountry VARCHAR(255),
    object_workstreet VARCHAR(255),
    object_workpob VARCHAR(10),
    object_workcity VARCHAR(255),
    object_workprovince VARCHAR(255),
    object_workpostalcode VARCHAR(10),
    object_workcountry VARCHAR(255),
    object_tz VARCHAR(32),
    object_geo VARCHAR(255),
    object_email VARCHAR(255),
    object_homephone VARCHAR(25),
    object_workphone VARCHAR(25),
    object_cellphone VARCHAR(25),
    object_fax VARCHAR(25),
    object_pager VARCHAR(25),
    object_title VARCHAR(255),
    object_role VARCHAR(255),
    object_logo BLOB,
    object_logotype VARCHAR(10),
    object_company VARCHAR(255),
    object_category VARCHAR(80),
    object_notes TEXT,
    object_url VARCHAR(255),
    object_freebusyurl VARCHAR(255),
    object_pgppublickey TEXT,
    object_smimepublickey TEXT,
    PRIMARY KEY(object_id)
);

CREATE INDEX turba_owner_idx ON turba_objects (owner_id);
CREATE INDEX turba_email_idx ON turba_objects (object_email);
CREATE INDEX turba_firstname_idx ON turba_objects (object_firstname);
CREATE INDEX turba_lastname_idx ON turba_objects (object_lastname);

CREATE TABLE turba_shares (
    share_id INT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255) NOT NULL,
    share_flags SMALLINT DEFAULT 0 NOT NULL,
    perm_creator SMALLINT DEFAULT 0 NOT NULL,
    perm_default SMALLINT DEFAULT 0 NOT NULL,
    perm_guest SMALLINT DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    attribute_params TEXT,
    PRIMARY KEY (share_id)
);

CREATE INDEX turba_shares_share_name_idx ON turba_shares (share_name);
CREATE INDEX turba_shares_share_owner_idx ON turba_shares (share_owner);
CREATE INDEX turba_shares_perm_creator_idx ON turba_shares (perm_creator);
CREATE INDEX turba_shares_perm_default_idx ON turba_shares (perm_default);
CREATE INDEX turba_shares_perm_guest_idx ON turba_shares (perm_guest);

CREATE TABLE turba_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX turba_shares_groups_share_id_idx ON turba_shares_groups (share_id);
CREATE INDEX turba_shares_groups_group_uid_idx ON turba_shares_groups (group_uid);
CREATE INDEX turba_shares_groups_perm_idx ON turba_shares_groups (perm);

CREATE TABLE turba_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX turba_shares_users_share_id_idx ON turba_shares_users (share_id);
CREATE INDEX turba_shares_users_user_uid_idx ON turba_shares_users (user_uid);
CREATE INDEX turba_shares_users_perm_idx ON turba_shares_users (perm);

-- $Horde: ingo/scripts/sql/ingo.sql,v 1.6.2.8 2009/10/20 21:44:32 jan Exp $

CREATE TABLE ingo_rules (
    rule_id INT NOT NULL,
    rule_owner VARCHAR(255) NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_action INT NOT NULL,
    rule_value VARCHAR(255),
    rule_flags INT,
    rule_conditions TEXT,
    rule_combine INT,
    rule_stop INT,
    rule_active INT DEFAULT 1 NOT NULL,
    rule_order INT DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (rule_id)
);

CREATE INDEX rule_owner_idx ON ingo_rules (rule_owner);


CREATE TABLE ingo_lists (
    list_owner VARCHAR(255) NOT NULL,
    list_blacklist INT DEFAULT 0,
    list_address VARCHAR(255) NOT NULL
);

CREATE INDEX list_idx ON ingo_lists (list_owner, list_blacklist);


CREATE TABLE ingo_forwards (
    forward_owner VARCHAR(255) NOT NULL,
    forward_addresses TEXT,
    forward_keep INT DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (forward_owner)
);


CREATE TABLE ingo_vacations (
    vacation_owner VARCHAR(255) NOT NULL,
    vacation_addresses TEXT,
    vacation_subject VARCHAR(255),
    vacation_reason TEXT,
    vacation_days INT DEFAULT 7,
    vacation_start INT,
    vacation_end INT,
    vacation_excludes TEXT,
    vacation_ignorelists INT DEFAULT 1,
--
    PRIMARY KEY (vacation_owner)
);


CREATE TABLE ingo_spam (
    spam_owner VARCHAR(255) NOT NULL,
    spam_level INT DEFAULT 5,
    spam_folder VARCHAR(255),
--
    PRIMARY KEY (spam_owner)
);


CREATE TABLE ingo_shares (
    share_id INT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255) NOT NULL,
    share_flags SMALLINT DEFAULT 0 NOT NULL,
    perm_creator SMALLINT DEFAULT 0 NOT NULL,
    perm_default SMALLINT DEFAULT 0 NOT NULL,
    perm_guest SMALLINT DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX ingo_shares_share_name_idx ON ingo_shares (share_name);
CREATE INDEX ingo_shares_share_owner_idx ON ingo_shares (share_owner);
CREATE INDEX ingo_shares_perm_creator_idx ON ingo_shares (perm_creator);
CREATE INDEX ingo_shares_perm_default_idx ON ingo_shares (perm_default);
CREATE INDEX ingo_shares_perm_guest_idx ON ingo_shares (perm_guest);

CREATE TABLE ingo_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX ingo_shares_groups_share_id_idx ON ingo_shares_groups (share_id);
CREATE INDEX ingo_shares_groups_group_uid_idx ON ingo_shares_groups (group_uid);
CREATE INDEX ingo_shares_groups_perm_idx ON ingo_shares_groups (perm);

CREATE TABLE ingo_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX ingo_shares_users_share_id_idx ON ingo_shares_users (share_id);
CREATE INDEX ingo_shares_users_user_uid_idx ON ingo_shares_users (user_uid);
CREATE INDEX ingo_shares_users_perm_idx ON ingo_shares_users (perm);

-- $Horde: kronolith/scripts/sql/kronolith.sql,v 1.3.2.10 2009/10/22 16:48:56 jan Exp $

CREATE TABLE kronolith_events (
    event_id VARCHAR(32) NOT NULL,
    event_uid VARCHAR(255) NOT NULL,
    calendar_id VARCHAR(255) NOT NULL,
    event_creator_id VARCHAR(255) NOT NULL,
    event_description TEXT,
    event_location TEXT,
    event_status INT DEFAULT 0,
    event_attendees TEXT,
    event_keywords TEXT,
    event_exceptions TEXT,
    event_title VARCHAR(255),
    event_category VARCHAR(80),
    event_recurtype INT DEFAULT 0,
    event_recurinterval INT,
    event_recurdays INT,
    event_recurenddate DATETIME,
    event_recurcount INT,
    event_start DATETIME,
    event_end DATETIME,
    event_alarm INT DEFAULT 0,
    event_modified INT NOT NULL,
    event_private INT DEFAULT 0 NOT NULL,

    PRIMARY KEY (event_id)
);

CREATE INDEX kronolith_calendar_idx ON kronolith_events (calendar_id);
CREATE INDEX kronolith_uid_idx ON kronolith_events (event_uid);


CREATE TABLE kronolith_storage (
    vfb_owner      VARCHAR(255),
    vfb_email      VARCHAR(255) NOT NULL,
    vfb_serialized TEXT NOT NULL
);

CREATE INDEX kronolith_vfb_owner_idx ON kronolith_storage (vfb_owner);
CREATE INDEX kronolith_vfb_email_idx ON kronolith_storage (vfb_email);


CREATE TABLE kronolith_shares (
    share_id INT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255) NOT NULL,
    share_flags SMALLINT DEFAULT 0 NOT NULL,
    perm_creator SMALLINT DEFAULT 0 NOT NULL,
    perm_default SMALLINT DEFAULT 0 NOT NULL,
    perm_guest SMALLINT DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX kronolith_shares_share_name_idx ON kronolith_shares (share_name);
CREATE INDEX kronolith_shares_share_owner_idx ON kronolith_shares (share_owner);
CREATE INDEX kronolith_shares_perm_creator_idx ON kronolith_shares (perm_creator);
CREATE INDEX kronolith_shares_perm_default_idx ON kronolith_shares (perm_default);
CREATE INDEX kronolith_shares_perm_guest_idx ON kronolith_shares (perm_guest);

CREATE TABLE kronolith_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX kronolith_shares_groups_share_id_idx ON kronolith_shares_groups (share_id);
CREATE INDEX kronolith_shares_groups_group_uid_idx ON kronolith_shares_groups (group_uid);
CREATE INDEX kronolith_shares_groups_perm_idx ON kronolith_shares_groups (perm);

CREATE TABLE kronolith_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX kronolith_shares_users_share_id_idx ON kronolith_shares_users (share_id);
CREATE INDEX kronolith_shares_users_user_uid_idx ON kronolith_shares_users (user_uid);
CREATE INDEX kronolith_shares_users_perm_idx ON kronolith_shares_users (perm);

-- $Horde: nag/scripts/sql/nag.sql,v 1.4.8.10 2009/10/22 14:24:20 jan Exp $

CREATE TABLE nag_tasks (
    task_id              VARCHAR(32) NOT NULL,
    task_owner           VARCHAR(255) NOT NULL,
    task_creator         VARCHAR(255) NOT NULL,
    task_parent          VARCHAR(255),
    task_assignee        VARCHAR(255),
    task_name            VARCHAR(255) NOT NULL,
    task_uid             VARCHAR(255) NOT NULL,
    task_desc            TEXT,
    task_start           INT,
    task_due             INT,
    task_priority        INT DEFAULT 0 NOT NULL,
    task_estimate        FLOAT,
    task_category        VARCHAR(80),
    task_completed       SMALLINT DEFAULT 0 NOT NULL,
    task_completed_date  INT,
    task_alarm           INT DEFAULT 0 NOT NULL,
    task_private         SMALLINT DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (task_id)
);

CREATE INDEX nag_tasklist_idx ON nag_tasks (task_owner);
CREATE INDEX nag_uid_idx ON nag_tasks (task_uid);
CREATE INDEX nag_start_idx ON nag_tasks (task_start);

CREATE TABLE nag_shares (
    share_id INT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255) NOT NULL,
    share_flags SMALLINT DEFAULT 0 NOT NULL,
    perm_creator SMALLINT DEFAULT 0 NOT NULL,
    perm_default SMALLINT DEFAULT 0 NOT NULL,
    perm_guest SMALLINT DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX nag_shares_share_name_idx ON nag_shares (share_name);
CREATE INDEX nag_shares_share_owner_idx ON nag_shares (share_owner);
CREATE INDEX nag_shares_perm_creator_idx ON nag_shares (perm_creator);
CREATE INDEX nag_shares_perm_default_idx ON nag_shares (perm_default);
CREATE INDEX nag_shares_perm_guest_idx ON nag_shares (perm_guest);

CREATE TABLE nag_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX nag_shares_groups_share_id_idx ON nag_shares_groups (share_id);
CREATE INDEX nag_shares_groups_group_uid_idx ON nag_shares_groups (group_uid);
CREATE INDEX nag_shares_groups_perm_idx ON nag_shares_groups (perm);

CREATE TABLE nag_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX nag_shares_users_share_id_idx ON nag_shares_users (share_id);
CREATE INDEX nag_shares_users_user_uid_idx ON nag_shares_users (user_uid);
CREATE INDEX nag_shares_users_perm_idx ON nag_shares_users (perm);

-- $Horde: mnemo/scripts/sql/mnemo.sql,v 1.5.2.8 2009/10/20 21:44:35 jan Exp $

CREATE TABLE mnemo_memos (
    memo_owner      VARCHAR(255) NOT NULL,
    memo_id         VARCHAR(32) NOT NULL,
    memo_uid        VARCHAR(255) NOT NULL,
    memo_desc       VARCHAR(64) NOT NULL,
    memo_body       TEXT,
    memo_category   VARCHAR(80),
    memo_private    SMALLINT DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (memo_owner, memo_id)
);

CREATE INDEX mnemo_notepad_idx ON mnemo_memos (memo_owner);
CREATE INDEX mnemo_uid_idx ON mnemo_memos (memo_uid);

CREATE TABLE mnemo_shares (
    share_id INT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255) NOT NULL,
    share_flags SMALLINT DEFAULT 0 NOT NULL,
    perm_creator SMALLINT DEFAULT 0 NOT NULL,
    perm_default SMALLINT DEFAULT 0 NOT NULL,
    perm_guest SMALLINT DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX mnemo_shares_share_name_idx ON mnemo_shares (share_name);
CREATE INDEX mnemo_shares_share_owner_idx ON mnemo_shares (share_owner);
CREATE INDEX mnemo_shares_perm_creator_idx ON mnemo_shares (perm_creator);
CREATE INDEX mnemo_shares_perm_default_idx ON mnemo_shares (perm_default);
CREATE INDEX mnemo_shares_perm_guest_idx ON mnemo_shares (perm_guest);

CREATE TABLE mnemo_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX mnemo_shares_groups_share_id_idx ON mnemo_shares_groups (share_id);
CREATE INDEX mnemo_shares_groups_group_uid_idx ON mnemo_shares_groups (group_uid);
CREATE INDEX mnemo_shares_groups_perm_idx ON mnemo_shares_groups (perm);

CREATE TABLE mnemo_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX mnemo_shares_users_share_id_idx ON mnemo_shares_users (share_id);
CREATE INDEX mnemo_shares_users_user_uid_idx ON mnemo_shares_users (user_uid);
CREATE INDEX mnemo_shares_users_perm_idx ON mnemo_shares_users (perm);

