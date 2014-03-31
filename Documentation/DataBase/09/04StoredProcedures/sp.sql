CREATE FUNCTION GET_JTH
(
  $Text                   TEXT,
  $Delimiter              CHAR(10),
  $I                      INT(11)
)
RETURNS TEXT
BEGIN

DECLARE _tmp      TEXT DEFAULT '';
DECLARE _test     TEXT DEFAULT '';

SELECT SUBSTRING_INDEX( $Text, $Delimiter, $I )     INTO _tmp;
SELECT SUBSTRING_INDEX( $Text, $Delimiter, $I - 1 ) INTO _test;

IF _tmp = _test THEN
    SET _tmp = "";
ELSE
    SELECT SUBSTRING_INDEX( _tmp, $Delimiter, -1 ) INTO _tmp;
END IF;

RETURN _tmp;

END
;
CREATE FUNCTION generate_salt
()
RETURNS CHAR(64)
BEGIN

DECLARE salt CHAR(64);

SET salt = RAND();

SET salt = SHA2( salt, 256 );

return salt;

END
;
CREATE FUNCTION Users_Compute_Hash
(
  salt           CHAR(64),
  value          TEXT
)
RETURNS CHAR(64)
BEGIN

DECLARE enckey TEXT;
DECLARE string TEXT;
DECLARE hash   CHAR(64);

SET enckey = SHA2( HEX( DES_ENCRYPT( "PrivateKey" ) ), 256 );
SET string = CONCAT( enckey, salt, value );
SET hash   = SHA2( string, 256 );

return hash;

END
;
CREATE FUNCTION Old_Users_Compute_Hash
(
  $salt                             INT(11),
  $value                           CHAR(99)
)
RETURNS CHAR(16)
DETERMINISTIC
BEGIN

return MD5( concat($value, $salt) );

END
;
CREATE FUNCTION Is_Local_Caller
()
RETURNS BOOL
DETERMINISTIC
BEGIN

DECLARE $USER TEXT;

SET $USER = USER();

return ('public@localhost' = $USER OR 'root@localhost' = $USER);

END
;
CREATE PROCEDURE users_uid_create( $Type VARCHAR(20) )
BEGIN

INSERT INTO users_uids (type) VALUES ( $Type );
SELECT LAST_INSERT_ID() AS USER;

END
;
CREATE PROCEDURE Users_Activation_Sent
(
  $Email              CHAR(99),
  $Password           CHAR(99)
)
BEGIN

IF Users_Vefify_Credentials( $Email, $Password ) THEN

    UPDATE users SET sent=1 WHERE email=$Email;

END IF;

END
;
CREATE PROCEDURE users_change_password
(
  $Email       CHAR(99),
  $OldPassword CHAR(99),
  $NewPassword CHAR(99)
)
BEGIN

DECLARE ret   BOOL;
DECLARE salt  TEXT;
DECLARE uhash TEXT;
DECLARE phash TEXT;

SET ret = False;

IF users_verify_credentials( $Email, $OldPassword ) THEN

    SET salt  = generate_salt();
    SET uhash = Users_Compute_Hash( salt, $Email    );
    SET phash = Users_Compute_Hash( salt, $NewPassword );

    UPDATE users
    SET user_salt=salt, user_hash=uhash, password_hash=phash
    WHERE email=$Email;

    SET ret = True;

END IF;

SELECT ret AS success;

END
;
CREATE PROCEDURE users_admin_reset_password
(
  $email        CHAR(99),
  $new_password CHAR(99)
)
BEGIN

DECLARE $ret   BOOL;
DECLARE $salt  TEXT;
DECLARE $uhash TEXT;
DECLARE $phash TEXT;

SET $ret = False;

SET $salt  = generate_salt();
SET $uhash = Users_Compute_Hash( $salt, $email        );
SET $phash = Users_Compute_Hash( $salt, $new_password );

UPDATE users
SET user_salt=$salt, user_hash=$uhash, password_hash=$phash
WHERE email=$email;

IF EXISTS( SELECT * FROM users WHERE user_salt=$salt AND user_hash=$uhash AND password_hash=$phash ) THEN
  SET $ret = True;
END IF;

SELECT $ret AS success;

END
;
CREATE FUNCTION Users_Check_Password
(
  $USER                             INT(11),
  $Password                        CHAR(99)
)
RETURNS BOOLEAN
READS SQL DATA
BEGIN

DECLARE $email CHAR(99);

SELECT email INTO $email FROM users WHERE USER=$USER;

return users_verify_credentials( $email, $Password );

END
;
CREATE PROCEDURE users_create
(
$Email                     CHAR(99),
$Password                  CHAR(99),
$Given_name                CHAR(50),
$Family_name               CHAR(50),
$Type                      CHAR(20)
)
BEGIN

DECLARE $id    INT;
DECLARE $USER  INT;
DECLARE $salt  TEXT;
DECLARE $uhash TEXT;
DECLARE $phash TEXT;

SET $id = 0;

IF ! Users_Exists( $Email ) THEN
  INSERT INTO users_uids (type) VALUES ( $Type );
  SET $USER  = LAST_INSERT_ID();
  SET $salt  = generate_salt();
  SET $uhash = Users_Compute_Hash( $salt, $Email    );
  SET $phash = Users_Compute_Hash( $salt, $Password );

  INSERT INTO users
    (  USER,  email, created, last_login, invalid_logins, user_salt, user_hash, password_hash, sent,   user_status,  given_name,  family_name )
  VALUES
    ( $USER, $Email,   NOW(),          0,              0,     $salt,    $uhash,        $phash,    0, "UNCONFIRMED", $Given_name, $Family_name );

  SET $id = $USER;
END IF;

SELECT @id AS USER;

END
;
CREATE FUNCTION Users_Exists( $Email CHAR(99) )
RETURNS BOOLEAN
DETERMINISTIC
BEGIN

return Exists( SELECT email FROM users WHERE email=$Email );

END
;
CREATE PROCEDURE Users_Resend_Activation
(
$email                     CHAR(99)
)
BEGIN

DECLARE $USER INT;

SELECT USER INTO $USER FROM users WHERE email=$email;

IF EXISTS( SELECT * FROM users_activations WHERE USER=$USER ) THEN

    UPDATE users SET sent=0 WHERE email=$email;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve
(
$Sid                           CHAR(64),
$USER                           INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

    SELECT * FROM users WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_By_User_Hash
(
  $sid                             CHAR(64),
  $user_hash                       CHAR(64)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF "" != @idtype THEN

    SELECT * FROM users WHERE user_hash=$user_hash;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Unsent
()
BEGIN

SELECT * FROM view_users WHERE sent=0;

END
;
CREATE PROCEDURE Users_Update
(
$Sid                          CHAR(64),
$USER                          INT(11),
$new_email                    CHAR(99),
$new_given_name               CHAR(50),
$new_family_name              CHAR(50)
)
BEGIN

DECLARE $email             CHAR(99);
DECLARE $email_provisional CHAR(99);
DECLARE $sent              DATETIME;

SET $sent = 0;

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER OR "ADMIN" = @idtype THEN

    SELECT email             INTO $email             FROM users WHERE USER=$USER;
    SELECT email_provisional INTO $email_provisional FROM users WHERE USER=$USER;

    IF $new_email != @email THEN
        SET $email_provisional = $new_email;
    END IF;

    UPDATE users
    SET email_provisional=$email_provisional, sent=$sent, given_name=$new_given_name, family_name=$new_family_name
    WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Users_Update_Sent
(
  $Email              CHAR(99)
)
BEGIN

UPDATE users SET sent=1 WHERE email=$Email OR email_provisional=$Email;

END
;
CREATE FUNCTION users_verify_credentials
(
  $Email              CHAR(99),
  $Password           CHAR(99)
)
RETURNS BOOL
READS SQL DATA
BEGIN

DECLARE $ret    BOOL;
DECLARE $salt   TEXT;
DECLARE $phash1 TEXT;
DECLARE $phash2 TEXT;

SET $ret = False;

IF EXISTS( SELECT * FROM users WHERE email=$Email ) THEN

    SELECT user_salt     INTO $salt   FROM users WHERE email=$Email;
    SELECT password_hash INTO $phash1 FROM users WHERE email=$Email;

    SET $phash2 = Users_Compute_Hash( $salt, $Password );

    IF $phash1 = $phash2 THEN
        SET $ret = True;
    END IF;

END IF;

return $ret;

END
;
CREATE PROCEDURE Users_Requested_Invites_Replace
(
  $email                         CHAR(99)
)
BEGIN

IF NOT EXISTS( SELECT * FROM users_requested_invites WHERE email=$email ) THEN
    REPLACE INTO users_requested_invites
           (  REQUEST,  email, time_of_request, invite_sent )
    VALUES (        0, $email,           NOW(),           0 );
END IF;

END
;
CREATE PROCEDURE Users_Requested_Invites_Retrieve
(
  $sid                           CHAR(64)
)
BEGIN

#CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

#IF "SID" = @idtype THEN
#    IF NOT EXISTS( SELECT * FROM users_requested_invites WHERE email=@email ) THEN
#        SELECT * FROM users_requested_invites ORDER BY time_of_request;
#    END IF;
#END IF;

END
;
CREATE PROCEDURE Users_Activations_Create
(
  $email                           CHAR(99)
)
BEGIN

DECLARE $USER  INT;
DECLARE $token TEXT;

SET $token = generate_salt();

SELECT USER INTO $USER FROM users WHERE email=$email OR email_provisional=$email;

IF "" != $USER THEN
  REPLACE INTO users_activations VALUES ( $USER, NOW(), $token );
END IF;

SELECT $token AS token;

END
;
CREATE PROCEDURE Users_Activations_Confirm_Account
(
  $token                           CHAR(64)
)
BEGIN

DECLARE $USER              INT;
DECLARE $email             TEXT;
DECLARE $email_provisional TEXT;

SELECT USER INTO $USER FROM users_activations WHERE token=$token;

IF 0 != $USER THEN
  SELECT email, email_provisional INTO $email, $email_provisional
  FROM users WHERE USER=$USER;
	
  IF "" != $email_provisional THEN
    SET $email = $email_provisional;
  END IF;

  UPDATE users SET email=$email, email_provisional='', user_status='CONFIRMED' WHERE USER=$USER;
  DELETE FROM users_activations WHERE token=$token;
END IF;

END
;
CREATE PROCEDURE Users_Activations_Confirm_Account_And_Authenticate
(
  $token                           CHAR(64)
)
BEGIN

DECLARE $USER              INT;
DECLARE $email             TEXT;
DECLARE $email_provisional TEXT;
DECLARE $sessionid         TEXT;

SELECT USER INTO $USER FROM users_activations WHERE token=$token;

SET $sessionid = "";

IF 0 != $USER THEN
    SELECT email, email_provisional INTO $email, $email_provisional
    FROM users WHERE USER=$USER;
	
    IF "" != $email_provisional THEN
        SET $email = $email_provisional;
    END IF;

    UPDATE users SET email=$email, email_provisional='', user_status='CONFIRMED' WHERE USER=$USER;
    DELETE FROM users_activations WHERE token=$token;

    SET $sessionid = MD5( concat( $token, NOW() ) );
    REPLACE INTO users_sessions VALUES ( $sessionid, $email, NOW(), NOW(), UNIX_TIMESTAMP() + 1000 );
END IF;

SELECT $sessionid AS sessionid;

END
;
CREATE PROCEDURE Users_Send_Resets_Replace
(
  $email      CHAR(99)
)
BEGIN

DECLARE $USER  INT;
DECLARE $token TEXT;

SET $token = generate_seed();

SELECT USER INTO $USER FROM users WHERE email=$email;

IF "" != $USER THEN
  REPLACE INTO users_send_resets VALUES ( $USER, NOW(), $token, 0 );
END IF;

END
;
CREATE PROCEDURE Users_Send_Resets_Retrieve
()
BEGIN

SELECT * FROM users_send_resets LEFT JOIN users USING (USER) WHERE users_send_resets.sent=0;

END
;
CREATE PROCEDURE Users_Send_Resets_Sent
(
  $email  CHAR(99)
)
BEGIN

DECLARE $USER INT;

SELECT USER INTO $USER FROM users_send_resets LEFT JOIN users USING (USER) WHERE email=$email LIMIT 1;

UPDATE users_send_resets SET sent=NOW() WHERE USER=$USER;

END
;
CREATE PROCEDURE Users_Send_Resets_Reset_Password
(
  $token                           CHAR(64),
  $password                        CHAR(99)
)
BEGIN

DECLARE $USER  INT;
DECLARE $email TEXT;
DECLARE $salt  TEXT;
DECLARE $uhash TEXT;
DECLARE $phash TEXT;

SELECT USER INTO $USER FROM users_send_resets WHERE token=$token;

IF 0 != $USER THEN
    SELECT email INTO $email FROM users WHERE USER=$USER;

    SET $salt  = generate_salt();
    SET $uhash = Users_Compute_Hash( $salt, @email );
    SET $phash = Users_Compute_Hash( $salt, $Password );

    UPDATE users
    SET user_salt=$salt, user_hash=$uhash, password_hash=$phash, invalid_logins=0
    WHERE USER=$USER;

    DELETE FROM users_send_resets WHERE token=$token;
END IF;

END
;
CREATE FUNCTION Users_Send_Resets_Exists
(
  $token      CHAR(64)
)
RETURNS BOOL
READS SQL DATA
BEGIN

return EXISTS( SELECT * FROM users_send_resets WHERE token=$token );

END
;
CREATE PROCEDURE Users_Sessions_Replace
(
  $email                           CHAR(99),
  $password                        CHAR(99)
)
BEGIN

DECLARE $salt      TEXT;
DECLARE $phash1    TEXT;
DECLARE $phash2    TEXT;
DECLARE $invalid   TEXT;
DECLARE $sessionid TEXT;

DELETE FROM users_sessions WHERE expiry < NOW();

SELECT user_salt      INTO $salt    FROM users WHERE email=$email;
SELECT password_hash  INTO $phash1  FROM users WHERE email=$email;
SELECT invalid_logins INTO $invalid FROM users WHERE email=$email;

IF "" != $email THEN

  IF $invalid > 4 THEN

     SELECT SLEEP( $invalid );

     SET $sessionid = "INVALID_LOGINS";

  ELSE

    SET $phash2 = Users_Compute_Hash( $salt, $password );

    IF $phash1=$phash2 THEN

      SET $sessionid = generate_salt();

      WHILE EXISTS( SELECT * FROM users_sessions WHERE sid=$sessionid ) DO
        SET $sessionid = generate_salt();
	  END WHILE;
	  
      REPLACE INTO users_sessions VALUES ( $sessionid, $email, NOW(), NOW(), UNIX_TIMESTAMP() + 1000 );
      UPDATE users SET invalid_logins = 0, last_login=NOW() WHERE email=$email;

    ELSE

      UPDATE users SET invalid_logins = $invalid + 1 WHERE email=$Email;
      SET $sessionid = "INVALID_PASSWORD";

    END IF;
  END IF;
ELSE

  SET $sessionid = "INVALID_USER";

END IF;

SELECT $sessionid AS sessionid;

END
;
CREATE PROCEDURE Users_Sessions_Terminate
(
  $Sid                             CHAR(64)
)
BEGIN

DELETE FROM users_sessions WHERE sid=$Sid;

END
;
CREATE FUNCTION Users_Sessions_Verify
(
  $Sid CHAR(64)
)
RETURNS BOOLEAN
READS SQL DATA
BEGIN

DECLARE $expiry INT;
DECLARE $now    INT;
DECLARE $ret    BOOL;

SET $now    = UNIX_TIMESTAMP();
SET $ret    = False;

SELECT expiry INTO $expiry FROM users_sessions WHERE sid=$Sid;

IF $now < $expiry THEN
    SET $ret = True;
END IF;

return $ret;

END
;
CREATE PROCEDURE Users_Sessions_Extend_Expiry
(
  $Sid CHAR(64)
)
BEGIN

DECLARE $expiry INT;
DECLARE $now    INT;
DECLARE $ret    BOOL;

SET $now = UNIX_TIMESTAMP();

SELECT expiry INTO $expiry FROM users_sessions WHERE sid=$Sid;

IF $now < $expiry THEN
    SET $expiry = $expiry + 1000;
    UPDATE users_sessions SET expiry=$expiry WHERE sid=$Sid;
ELSE
    UPDATE users_sessions SET expiry=0       WHERE sid=$Sid;
END IF;

END
;
CREATE PROCEDURE Users_Authorise_Sessionid
(
      $Sid          CHAR(64),
  OUT $Email        CHAR(99),
  OUT $USER          INT(11),
  OUT $IDType    VARCHAR(20)
)
BEGIN

IF Users_Sessions_Verify( $Sid ) THEN

    CALL Users_Sessions_Extend_Expiry( $Sid );

    SELECT email    INTO $Email    FROM users_sessions WHERE sid      = $Sid;
    SELECT USER     INTO $USER     FROM users          WHERE email    = $Email;
    SELECT type     INTO $IDType   FROM users_uids     WHERE USER     = $USER;

ELSE

    CALL Users_Sessions_Terminate( $Sid );

END IF;

END
;
CREATE PROCEDURE Users_Authorize_Sessionid
(
      $Sid          CHAR(64),
  OUT $Email        CHAR(99),
  OUT $USER          INT(11),
  OUT $IDType    VARCHAR(20)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, $Email, $USER, $IDType );

END
;
CREATE PROCEDURE Users_Authenticate
(
  $Sid CHAR(64)
)
BEGIN

IF Users_Sessions_Verify( $Sid ) THEN

  CALL Users_Sessions_Extend_Expiry( $Sid );

  SELECT email, USER, given_name, family_name, type AS idtype, last_login, user_status, user_hash
  FROM users_sessions
  LEFT JOIN view_users USING (email) WHERE sid=$Sid;

ELSE

  CALL Users_Sessions_Terminate( $Sid );

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Single
(
  $Sid                       CHAR(64),
  $USER                       INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN

  SELECT * FROM users WHERE USER=$USER LIMIT 1;

END IF;

END
;
CREATE PROCEDURE Users_Update_Name
(
  $Sid                       CHAR(64),
  $USER                       INT(11),
  $given_name                CHAR(50),
  $family_name               CHAR(50)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN

  UPDATE users SET given_name=$given_name, family_name=$family_name WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Signups
(
$days INT(11)
)
BEGIN

SELECT email, sent, user_status, given_name, family_name, user_hash FROM users WHERE created > DATE_SUB( NOW(), INTERVAL $days DAY );

END
;
CREATE PROCEDURE Users_Alternate_Emails_Create
(
  $Sid                       CHAR(64),
  $USER                       INT(11),
  $email                  VARCHAR(99)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN

  REPLACE INTO users_alternate_emails VALUES ( $USER, $email, generate_salt() );

END IF;

END
;
CREATE PROCEDURE Users_Alternate_Emails_Delete
(
  $Sid                       CHAR(64),
  $USER                       INT(11),
  $Email                  VARCHAR(99)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN
    DELETE FROM users_alternate_emails WHERE USER=$USER AND email=$Email;
END IF;

END
;
CREATE PROCEDURE Users_Alternate_Emails_Retrieve_By_USER
(
  $Sid                       CHAR(64),
  $USER                       INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN
    SELECT * FROM users_alternate_emails WHERE USER=$USER ORDER BY email;
END IF;

END
;
CREATE PROCEDURE Users_Termination_Schedule_Replace
(
  $Sid                        CHAR(64),
  $USER                        INT(11),
  $password                   CHAR(99)
)
BEGIN

SET @success = 0;

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN
    IF ( users_verify_credentials( @email, $password ) ) THEN
        REPLACE INTO users_termination_schedule
            ( USER,  mark,  time_of_termination )
        VALUES
            ( $USER, NOW(), date_add( NOW(), INTERVAL 1 DAY ) );
        SET @success = 1;

    END IF;
END IF;

SELECT @success AS success;

END
;
CREATE PROCEDURE Users_Termination_Schedule_Retrieve
()
BEGIN

SELECT USER, time_of_termination, email
FROM users_termination_schedule LEFT JOIN users USING (USER)
WHERE NOW() > time_of_termination;

END
;
CREATE PROCEDURE Users_Delete
(
  $Sid  CHAR(64),
  $USER INT(11)
)
BEGIN

DECLARE $email TEXT;

IF "" != $Sid THEN
  CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );
  INSERT INTO users_deleted VALUES ( @USER, $USER );
END IF;

SELECT email INTO $email FROM users WHERE USER=$USER;

DELETE FROM users_activations          WHERE USER=$USER;
DELETE FROM users_alternate_emails     WHERE USER=$USER;
DELETE FROM users_send_resets          WHERE USER=$USER;
DELETE FROM users_sessions             WHERE email=$email;
DELETE FROM users_uids                 WHERE USER=$USER;
DELETE FROM users_termination_schedule WHERE USER=$USER;
DELETE FROM users                      WHERE USER=$USER;

END
;
CREATE PROCEDURE Payments_Customers_Uncreated
()
BEGIN

IF Is_Local_Caller() THEN

  SELECT * FROM view_payments_customers_uncreated;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Replace
(
  $USER                             INT(11),
  $customer_id                     CHAR(16)
)
BEGIN

DECLARE $USER INT;

IF Is_Local_Caller() THEN

  REPLACE INTO payments_customers
    (  USER,  created,  customer_id )
  VALUES
    ( $USER,    NOW(), $customer_id );

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Delete
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

DECLARE $customer_id CHAR(16);

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT customer_id INTO $customer_id FROM payments WHERE USER=$USER;
  
  REPLACE INTO payments_remove_cards
    (  USER,  customer_id )
  VALUES
    ( $USER, $customer_id );

  DELETE FROM payments_customers WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Retrieve_By_User
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT * FROM payments_customers WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Plans_Replace
(
  $sid                             CHAR(64),
  $USER                             INT(11),
  $plan_id                         CHAR(32),
  $cost                         DECIMAL(13,2)
)
BEGIN

DECLARE $switched DATETIME;
DECLARE $subscription_id CHAR(16);

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SET $switched        = NOW();
  SET $subscription_id = '';

  SELECT subscription_id INTO $subscription_id
  FROM payments_plans
  WHERE USER=$USER AND NOT subscription_id = "" ORDER BY PLAN DESC LIMIT 1;

  REPLACE INTO payments_plans
    (  USER,  switched,  plan_id,  cost,  subscription_id )
  VALUES
    ( $USER, $switched, $plan_id, $cost, $subscription_id );

END IF;

END
;
CREATE PROCEDURE Payments_Plans_Retrieve
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT * FROM payments_plans WHERE USER=$USER
  ORDER BY switched DESC
  LIMIT 1;

END IF;

END
;
CREATE PROCEDURE Payments_Plans_Retrieve_Today
()
BEGIN

SELECT * FROM
  (SELECT * FROM payments_plans ORDER BY switched DESC) AS S1
LEFT JOIN
  (SELECT * FROM payments_invoices WHERE raised=DATE(NOW())) AS S2 USING (USER)
WHERE
  DAY(switched) = DAY(NOW())
GROUP BY USER;

END
;
CREATE PROCEDURE Payments_Plans_Update_Subscription_Id
(
   $PLAN                             INT(11),
   $subscription_id                 CHAR(16)
)
BEGIN

UPDATE payments_plans
SET subscription_id=$subscription_id, subscribed=NOW()
WHERE PLAN=$PLAN;

END
;
CREATE PROCEDURE Payments_Purchases_Insert
(
  $sid                             CHAR(32),
  $USER                             INT(11),
  $description                     CHAR(99),
  $cost                         DECIMAL(13,2)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF "" != @USER THEN

  INSERT INTO payments_purchases
    (  USER,  purchased,  description,  cost )
  VALUES
    ( $USER,      NOW(), $description, $cost );

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Retrieve_Unprocessed
()
BEGIN

IF Is_Local_Caller() THEN

  SELECT *
  FROM      payments_purchases
  LEFT JOIN view_payments_credit_cards USING (USER)
  WHERE NOT processed=0 AND transacted=0;

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Transacted
(
  $PURCHASE               INT(11),
  $transaction_id        CHAR(16)
)
BEGIN

IF Is_Local_Caller() THEN

  UPDATE payments_purchases
  SET transaction_id=$transaction_id, transacted=NOW()
  WHERE PURCHASE=$PURCHASE;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Replace
(
  $sid                             CHAR(32),
  $USER                             INT(11),
  $final_four                      CHAR(4),
  $number                          TEXT,
  $cvv                             TEXT,
  $month                           TEXT,
  $year                            TEXT
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT token INTO @token FROM payments_credit_cards WHERE USER=$USER;

  IF "" != @token THEN
  
    CALL Payments_Credit_Cards_Delete( $sid, $USER );
  
  END IF;

  SET @provided = NOW();

  SELECT provided INTO @provided FROM payments_credit_cards WHERE USER=$USER;

  REPLACE INTO payments_credit_cards
    (  USER,  provided,  final_four,  number,  cvv,  month,  year,  token,  processed )
  VALUES
    ( $USER, @provided, $final_four, $number, $cvv, $month, $year,     '',          0 );

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Delete
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT customer_id INTO @customer_id FROM view_payments_credit_cards WHERE USER=$USER;
  
  REPLACE INTO payments_remove_cards
    (  USER,  customer_id )
  VALUES
    ( $USER, @customer_id );

  DELETE FROM payments_credit_cards
  WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Retrieve_By_User
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT * FROM payments_credit_cards WHERE USER=$USER;

ELSE

  SELECT token FROM payments_credit_cards WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Retrieve_Unsynced
()
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT * FROM view_payments_credit_cards_unsynced;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Synced
(
  $USER                           INT(11),
  $token                         CHAR(16)
)
BEGIN

UPDATE payments_credit_cards
SET number='', cvv='', month='', year='', token=$token, processed=NOW()
WHERE USER=$USER;

END
;
CREATE PROCEDURE Payments_Plans_Retrieve_Unsubscribed
()
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT view_payments_credit_cards.*, S2.*
  FROM view_payments_credit_cards
  LEFT JOIN
  (
    SELECT * FROM
	(
	  SELECT * FROM payments_plans ORDER BY PLAN DESC
	) AS S1
	GROUP BY USER
  ) AS S2
  USING (USER)
  WHERE NOT processed=0 AND subscribed=0;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Replace
(
  $USER                             INT(11),
  $transaction_id                  CHAR(16),
  $description                     CHAR(99)
)
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  REPLACE INTO payments_transactions
    (  USER,  transaction_id,  description )
  VALUES
	( $USER, $transaction_id, $description );

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_Unfinished
()
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT * FROM payments_transactions WHERE date IS NULL;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_Submitted
()
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT * FROM payments_transactions WHERE status='submitted_for_settlement';

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Update_Details
(
  $TRANSACTION                      INT(11),
  $date                        DATETIME,
  $type                            CHAR(50),
  $status                          CHAR(50),
  $payment_method_token            CHAR(16),
  $amount                       DECIMAL(13,2)
)
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  UPDATE payments_transactions
  SET
                    date=$date,
	                type=$type,
	              status=$status,
	payment_method_token=$payment_method_token,
	              amount=$amount
  WHERE
    TRANSACTION=$TRANSACTION;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_By_User
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

SELECT * FROM payments_transactions WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Remove_Cards_Retrieve
()
BEGIN

SELECT * FROM payments_remove_cards;

END
;
CREATE PROCEDURE Payments_Remove_Cards_Removed
(
$USER                           INT(11),
$customer_id                   CHAR(16)
)
BEGIN

DELETE FROM payments_remove_cards WHERE USER=$USER AND customer_id=$customer_id;

END
;
CREATE PROCEDURE Payments_Details_Replace
(
  $sid                          CHAR(32),
  $USER                          INT(11),
  $given_name                   CHAR(99),
  $family_name                  CHAR(99),
  $address                      CHAR(99),
  $address2                     CHAR(99),
  $suburb                       CHAR(99),
  $state                        CHAR(99),
  $country                      CHAR(99),
  $postcode                     CHAR(5)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  REPLACE INTO payments_details
    (  USER,  given_name,  family_name,  address,  address2,  suburb,  state,  country,  postcode )
  VALUES
    ( $USER, $given_name, $family_name, $address, $address2, $suburb, $state, $country, $postcode );

END IF;

END
;
CREATE PROCEDURE Payments_Details_Retrieve
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT * FROM payments_details WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Invoices_Replace
(
  $INVOICE                       INT(11),
  $USER                          INT(11),
  $currency                     CHAR(16),
  $amount                    DECIMAL(13,2),
  $gst                       DECIMAL(13,2),
  $total                     DECIMAL(13,2),
  $paid                      DECIMAL(13,2),
  $transacted                DATETIME
)
BEGIN

REPLACE INTO payments_invoices
(  INVOICE,  USER,  raised,  currency,  amount,  gst,  total,  paid,  transacted )
VALUES
( $INVOICE, $USER,   NOW(), $currency, $amount, $gst, $total, $paid, $transacted );

END
;
CREATE PROCEDURE Payments_Invoices_Retrieve
()
BEGIN

SELECT * FROM payments_invoices WHERE $total > $paid;

END
;
CREATE PROCEDURE Payments_Invoices_Retrieve_By_User
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN
  SELECT * FROM payments_invoices WHERE USER=$USER;
END IF;

END
;
CREATE PROCEDURE Files_Replace
(
  $Sid                             CHAR(32),
  $FILE                             INT(11),
  $USER                             INT(11),
  $kind                            CHAR(30),
  $original_filename               CHAR(255),
  $filename                        CHAR(255),
  $filetype                        CHAR(99),
  $filesize                        CHAR(45),
  $fileextension                   CHAR(10),
  $base64                      LONGBLOB
)
BEGIN

DECLARE $FILE  INT;
DECLARE $salt  TEXT;
DECLARE $token TEXT;

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER OR "ADMIN" = @idtype THEN

  SET $token = generate_salt();

  WHILE EXISTS( SELECT * FROM files WHERE token=$token ) DO
    SET $token = generate_salt();
  END WHILE;

  REPLACE INTO files
    (  FILE,  USER,  version,  kind,  original_filename,  filename,  filetype,  filesize,  fileextension,  salt,  token,  base64 )
  VALUES
    ( $FILE, $USER,    NOW(), $kind, $original_filename, $filename, $filetype, $filesize, $fileextension,     0, @token, $base64 );

  SELECT LAST_INSERT_ID() INTO @FILE;

END IF;

SELECT @FILE AS FILE;

END
;
CREATE FUNCTION Files_Exists_By_Kind
(
  $Sid                             CHAR(32),
  $USER                             INT(11),
  $kind                            CHAR(30)
)
RETURNS BOOL
READS SQL DATA
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER OR "ADMIN" = @idtype THEN

  return EXISTS( SELECT * FROM files WHERE USER=$USER AND kind=$kind );

END IF;

END
;
CREATE PROCEDURE Files_Retrieve_Info_By_Kind
(
  $Sid                             CHAR(32),
  $USER                             INT(11),
  $kind                            CHAR(30)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER OR "ADMIN" = @idtype THEN

  SELECT * FROM view_files WHERE USER=$USER AND kind=$kind ORDER BY version DESC;

END IF;

END
;
CREATE PROCEDURE Files_Retrieve
(
  $Sid                             CHAR(32),
  $FILE                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

SELECT * FROM files WHERE USER=@USER AND FILE=$FILE ORDER BY version DESC LIMIT 1;

END
;
CREATE PROCEDURE Files_Retrieve_By_Token
(
  $sid                             CHAR(32),
  $token                           CHAR(64)
)
BEGIN

SELECT * FROM files WHERE token=$token ORDER BY version DESC LIMIT 1;

END
;
