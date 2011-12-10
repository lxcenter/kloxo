-- $Horde: turba/scripts/upgrades/sharesng.sql,v 1.1.2.2 2011/02/24 10:19:50 jan Exp $

CREATE TABLE turba_sharesng (
    share_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(255),
    share_flags INT DEFAULT 0 NOT NULL,
    perm_creator_2 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_creator_4 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_creator_8 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_creator_16 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_default_2 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_default_4 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_default_8 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_default_16 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_guest_2 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_guest_4 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_guest_8 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_guest_16 TINYINT(1) DEFAULT 0 NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    attribute_params VARCHAR(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX index_turba_sharesng_on_share_name ON turba_sharesng (share_name);
CREATE INDEX index_turba_sharesng_on_share_owner ON turba_sharesng (share_owner);
CREATE INDEX index_turba_sharesng_on_perm_creator_2 ON turba_sharesng (perm_creator_2);
CREATE INDEX index_turba_sharesng_on_perm_creator_4 ON turba_sharesng (perm_creator_4);
CREATE INDEX index_turba_sharesng_on_perm_creator_8 ON turba_sharesng (perm_creator_8);
CREATE INDEX index_turba_sharesng_on_perm_creator_16 ON turba_sharesng (perm_creator_16);
CREATE INDEX index_turba_sharesng_on_perm_default_2 ON turba_sharesng (perm_default_2);
CREATE INDEX index_turba_sharesng_on_perm_default_4 ON turba_sharesng (perm_default_4);
CREATE INDEX index_turba_sharesng_on_perm_default_8 ON turba_sharesng (perm_default_8);
CREATE INDEX index_turba_sharesng_on_perm_default_16 ON turba_sharesng (perm_default_16);
CREATE INDEX index_turba_sharesng_on_perm_guest_2 ON turba_sharesng (perm_guest_2);
CREATE INDEX index_turba_sharesng_on_perm_guest_4 ON turba_sharesng (perm_guest_4);
CREATE INDEX index_turba_sharesng_on_perm_guest_8 ON turba_sharesng (perm_guest_8);
CREATE INDEX index_turba_sharesng_on_perm_guest_16 ON turba_sharesng (perm_guest_16);

CREATE TABLE turba_sharesng_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm_2 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_4 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_8 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_16 TINYINT(1) DEFAULT 0 NOT NULL
);

CREATE INDEX index_turba_sharesng_groups_on_share_id ON turba_sharesng_groups (share_id);
CREATE INDEX index_turba_sharesng_groups_on_group_uid ON turba_sharesng_groups (group_uid);
CREATE INDEX index_turba_sharesng_groups_on_perm_2 ON turba_sharesng_groups (perm_2);
CREATE INDEX index_turba_sharesng_groups_on_perm_4 ON turba_sharesng_groups (perm_4);
CREATE INDEX index_turba_sharesng_groups_on_perm_8 ON turba_sharesng_groups (perm_8);
CREATE INDEX index_turba_sharesng_groups_on_perm_16 ON turba_sharesng_groups (perm_16);

CREATE TABLE turba_sharesng_users (
    share_id INT NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    perm_2 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_4 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_8 TINYINT(1) DEFAULT 0 NOT NULL,
    perm_16 TINYINT(1) DEFAULT 0 NOT NULL
);

CREATE INDEX index_turba_sharesng_users_on_share_id ON turba_sharesng_users (share_id);
CREATE INDEX index_turba_sharesng_users_on_user_uid ON turba_sharesng_users (user_uid);
CREATE INDEX index_turba_sharesng_users_on_perm_2 ON turba_sharesng_users (perm_2);
CREATE INDEX index_turba_sharesng_users_on_perm_4 ON turba_sharesng_users (perm_4);
CREATE INDEX index_turba_sharesng_users_on_perm_8 ON turba_sharesng_users (perm_8);
CREATE INDEX index_turba_sharesng_users_on_perm_16 ON turba_sharesng_users (perm_16);
