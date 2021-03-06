CREATE TABLE users_uids (

USER                            INT(11)  NOT NULL AUTO_INCREMENT,
type                        VARCHAR(20)  NOT NULL DEFAULT '',

PRIMARY KEY (USER)
);
CREATE TABLE users (

USER                            INT(11)  NOT NULL,
email                       VARCHAR(99)  NOT NULL,
email_provisional           VARCHAR(99)  NOT NULL DEFAULT '',
created                    DATETIME      NOT NULL,
last_login                 DATETIME      NOT NULL,
invalid_logins                  INT(11)  NOT NULL,

user_salt                       INT(11)  NOT NULL,
user_hash                   VARCHAR(16)  NOT NULL,
password_hash               VARCHAR(16)  NOT NULL,
user_status                 VARCHAR(20)  NOT NULL,
sent                           BOOL      NOT NULL,

given_name                  VARCHAR(50)  NOT NULL,
family_name                 VARCHAR(50)  NOT NULL,

PRIMARY KEY (email), UNIQUE KEY (USER)
);
CREATE TABLE users_requested_invites (

REQUEST                             INT(11)  AUTO_INCREMENT,
email                           VARCHAR(99)  NOT NULL DEFAULT '',
time_of_request                DATETIME,
invite_sent                     BOOLEAN,

PRIMARY KEY (REQUEST)
);
CREATE TABLE users_activations (

USER                                INT(11)  NOT NULL,
timestamp                     TIMESTAMP      NOT NULL,
token                           VARCHAR(64)  NOT NULL,

PRIMARY KEY (USER)
);
CREATE TABLE users_reset_passwords
(
USER                                INT(11)  NOT NULL,
timestamp                     TIMESTAMP      NOT NULL,
token                           VARCHAR(64)  NOT NULL,
sent                          TIMESTAMP      NOT NULL,

PRIMARY KEY (USER)
);
CREATE TABLE users_sessions
(
sid                             VARCHAR(32)  NOT NULL,
email                           VARCHAR(99)  NOT NULL,
created                       TIMESTAMP      NOT NULL,
updated                       TIMESTAMP      NOT NULL,
expiry                              INT(64)  NOT NULL,

PRIMARY KEY (sid)
);
CREATE TABLE users_alternate_emails (

USER                            INT(11)  NOT NULL AUTO_INCREMENT,
email                       VARCHAR(99)  NOT NULL DEFAULT '',
token                       VARCHAR(64)  NOT NULL,

PRIMARY KEY (USER,email)
);
CREATE TABLE users_termination_schedule
(

USER                            INT(11)  NOT NULL,
mark                       DATETIME      NOT NULL,
time_of_termination        DATETIME      NOT NULL,

PRIMARY KEY (USER)
);
CREATE TABLE users_deleted
(
USER         INT(11),
DELETED_USER INT(11)
);
