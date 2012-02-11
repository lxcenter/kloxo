-- $Horde: horde/scripts/sql/horde_signups.mysql.sql,v 1.1.2.3 2010/05/27 11:32:55 jan Exp $
CREATE TABLE horde_signups (
    user_name VARCHAR(255) NOT NULL,
    signup_date VARCHAR(255) NOT NULL,
    signup_host VARCHAR(255) NOT NULL,
    signup_data TEXT NOT NULL,
    PRIMARY KEY user_name (user_name)
);
