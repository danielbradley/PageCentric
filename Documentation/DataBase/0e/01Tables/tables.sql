CREATE TABLE preregistrations
(
  name                     CHAR(99),
  email                    CHAR(99),
  info                         TEXT,
  token                    CHAR(64),
  created                  DATETIME DEFAULT 0,
  confirmation_sent        DATETIME DEFAULT 0,
  confirmed                DATETIME DEFAULT 0,

  PRIMARY KEY (email)
);
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

user_salt                   VARCHAR(64)  NOT NULL,
user_hash                   VARCHAR(64)  NOT NULL,
password_hash               VARCHAR(64)  NOT NULL,
user_status                 VARCHAR(20)  NOT NULL,
sent                           BOOL      NOT NULL,

given_name                  VARCHAR(50)  NOT NULL,
family_name                 VARCHAR(50)  NOT NULL,

PRIMARY KEY (email), UNIQUE KEY (USER)
);
CREATE TABLE users_requested_invites
(
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
CREATE TABLE users_send_resets
(
USER                                INT(11)  NOT NULL,
timestamp                     TIMESTAMP      NOT NULL,
token                           VARCHAR(64)  NOT NULL,
sent                          TIMESTAMP      NOT NULL,

PRIMARY KEY (USER)
);
CREATE TABLE users_sessions
(
sid                             VARCHAR(64)  NOT NULL,
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
CREATE TABLE payments_customers (

USER                            INT(11),
created                    DATETIME,
customer_id                    CHAR(16) NOT NULL DEFAULT '',

PRIMARY KEY (USER)
);
CREATE TABLE payments_plans
(
PLAN                            INT(11) AUTO_INCREMENT,
USER                            INT(11),
switched                   DATETIME,
subscription_id                CHAR(16) NOT NULL DEFAULT '',
plan_id                        CHAR(32),
cost                        DECIMAL(13,2),
subscribed                 DATETIME     NOT NULL DEFAULT 0,

PRIMARY KEY (PLAN), UNIQUE KEY (USER,switched)
);
CREATE TABLE payments_purchases (

PURCHASE                        INT(11) AUTO_INCREMENT,
USER                            INT(11),
purchased                  DATETIME,
description                    CHAR(99),
cost                        DECIMAL(13,2),
transaction_id                 CHAR(16),
transacted                 DATETIME     NOT NULL DEFAULT 0,

PRIMARY KEY (PURCHASE)
);
CREATE TABLE payments_credit_cards (

USER                            INT(11),
provided                   DATETIME,
final_four                     CHAR(4),
number                         TEXT,
cvv                            TEXT,
month                          TEXT,
year                           TEXT,
token                          CHAR(16),
processed                  DATETIME NOT NULL DEFAULT 0,

PRIMARY KEY (USER)
);
CREATE TABLE payments_transactions (

TRANSACTION                     INT(11) AUTO_INCREMENT,
USER                            INT(11),
transaction_id                 CHAR(16),
description                    CHAR(99),

date                       DATETIME,
type                           CHAR(50),
status                         CHAR(50),
payment_method_token           CHAR(16),
amount                      DECIMAL(13,2),

PRIMARY KEY (TRANSACTION), UNIQUE KEY (USER,transaction_id)
);
CREATE TABLE payments_remove_cards (

USER                            INT(11),
customer_id                    CHAR(16),

PRIMARY KEY (USER)
);
CREATE TABLE payments_details (

USER                            INT(11),
given_name                     CHAR(99),
family_name                    CHAR(99),
address                        CHAR(99),
address2                       CHAR(99),
suburb                         CHAR(99),
state                          CHAR(99),
country                        CHAR(99),
postcode                       CHAR(5),

PRIMARY KEY (USER)
);
CREATE TABLE payments_invoices (

INVOICE                         INT(11) AUTO_INCREMENT,
USER                            INT(11),
raised                         DATE,
currency                       CHAR(16),
amount                      DECIMAL(13,2),
gst                         DECIMAL(13,2),
total                       DECIMAL(13,2),
paid                        DECIMAL(13,2),
transacted                 DATETIME,

PRIMARY KEY (INVOICE), UNIQUE KEY (USER,raised)
);
CREATE TABLE files
(
    FILE                            INT(11)  NOT NULL AUTO_INCREMENT,
    USER                            INT(11)  NOT NULL,
    version                    DATETIME      NOT NULL,
    kind                           CHAR(30)  NOT NULL,

    original_filename              CHAR(255) NOT NULL,
    filename                       CHAR(255) NOT NULL,
    filetype                       CHAR(99)  NOT NULL,
    filesize                       CHAR(45)  NOT NULL,
    fileextension                  CHAR(10)  NOT NULL,
    salt                            INT(4)   NOT NULL,
    token                          CHAR(64)  NOT NULL,
    base64                     LONGBLOB      NOT NULL,

    PRIMARY KEY (FILE)
);
CREATE TABLE statistics_visits
(
ip_address                          CHAR(99),
visit                                   DATE,

PRIMARY KEY (ip_address,visit)
);
CREATE TABLE statistics_impressions
(
ip_address                          CHAR(99),
session                             CHAR(64),
access                              DATETIME,

PRIMARY KEY (ip_address,session,access)
);
