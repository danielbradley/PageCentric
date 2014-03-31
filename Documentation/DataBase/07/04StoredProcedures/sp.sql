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
CREATE FUNCTION Users_Compute_Hash
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
CREATE PROCEDURE users_change_password
(
  $Email       CHAR(99),
  $OldPassword CHAR(99),
  $NewPassword CHAR(99)
)
BEGIN

SET @ret = False;

IF users_verify_credentials( $Email, $OldPassword ) THEN

    SET @salt  = RAND() * 1000;
    SET @uhash = Users_Compute_Hash( @salt, $Email    );
    SET @phash = Users_Compute_Hash( @salt, $NewPassword );

    UPDATE users
    SET user_salt=@salt, user_hash=@uhash, password_hash=@phash
    WHERE email=$Email;

    SET @ret = True;

END IF;

SELECT @ret AS success;

END
;
CREATE PROCEDURE users_admin_reset_password
(
  $Email       CHAR(99),
  $NewPassword CHAR(99)
)
BEGIN

SET @ret = False;

SET @salt  = RAND() * 1000;
SET @uhash = Users_Compute_Hash( @salt, $Email    );
SET @phash = Users_Compute_Hash( @salt, $NewPassword );

UPDATE users
SET user_salt=@salt, user_hash=@uhash, password_hash=@phash
WHERE email=$Email;

SET @ret = True;

SELECT @ret AS success;

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

SELECT email INTO @email FROM users WHERE USER=$USER;

return users_verify_credentials( @email, $Password );

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

SET @id = 0;

IF ! Users_Exists( $Email ) THEN
	INSERT INTO users_uids (type) VALUES ( $Type );
    SET @USER  = LAST_INSERT_ID();
    SET @salt  = RAND() * 1000;
    SET @uhash = Users_Compute_Hash( @salt, $Email    );
    SET @phash = Users_Compute_Hash( @salt, $Password );

    INSERT INTO users
        (  USER,  email, created, last_login, invalid_logins, user_salt, user_hash, password_hash, sent,   user_status,  given_name,  family_name )
    VALUES
        ( @USER, $Email,   NOW(),          0,              0,     @salt,    @uhash,        @phash,    0, "UNCONFIRMED", $Given_name, $Family_name );

    SET @id = @USER;
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

SELECT USER INTO @USER FROM users WHERE email=$email;

IF EXISTS( SELECT * FROM users_activations WHERE USER=@USER ) THEN

    UPDATE users SET sent=0 WHERE email=$email;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve
(
$Sid                           CHAR(32),
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
  $sid                             CHAR(32),
  $user_hash                       CHAR(32)
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
$Sid                          CHAR(32),
$USER                          INT(11),
$email                        CHAR(99),
$given_name                   CHAR(50),
$family_name                  CHAR(50)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER OR "ADMIN" = @idtype THEN

    SELECT email             INTO @email             FROM users WHERE USER=$USER;
    SELECT email_provisional INTO @email_provisional FROM users WHERE USER=$USER;
    SELECT sent              INTO @sent              FROM users WHERE USER=$USER;

    IF $email != @email THEN
        SET @email_provisional = $email;
        SET @sent              = 0;
    END IF;

    UPDATE users
    SET email_provisional=@email_provisional, sent=@sent, given_name=$given_name, family_name=$family_name
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

SET @ret = False;

IF EXISTS( SELECT * FROM users WHERE email=$Email ) THEN

    SELECT user_salt     INTO @salt     FROM users WHERE email=$Email;
    SELECT password_hash INTO @phash1   FROM users WHERE email=$Email;

    SET @phash2 = Users_Compute_Hash( @salt, $Password );

    IF @phash1 = @phash2 THEN
        SET @ret = True;
    END IF;

END IF;

return @ret;

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
  $sid                           CHAR(32)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF "SID" = @idtype THEN
    IF NOT EXISTS( SELECT * FROM users_requested_invites WHERE email=$email ) THEN
        SELECT * FROM users_requested_invites ORDER BY time_of_request;
    END IF;
END IF;

END
;
CREATE PROCEDURE Users_Activations_Create
(
  $Email      CHAR(99)
)
BEGIN

SET @token = MD5( CONCAT( MD5(RAND()), NOW(), MD5(RAND()) ) );

SELECT USER INTO @USER FROM users WHERE email=$Email OR email_provisional=$Email;

IF "" != @USER THEN
    REPLACE INTO users_activations VALUES ( @USER, NOW(), @token );
END IF;

SELECT @token AS token;

END
;
CREATE PROCEDURE Users_Activations_Confirm_Account
(
  $Token                           CHAR(64)
)
BEGIN

SELECT USER INTO @USER FROM users_activations WHERE token=$Token;

IF @USER != 0 THEN
    SELECT email, email_provisional INTO @email, @email_provisional
	FROM users WHERE USER=@USER;
	
	IF "" != @email_provisional THEN
	    SET @email = @email_provisional;
    END IF;

    UPDATE users SET email=@email, email_provisional='', user_status='CONFIRMED' WHERE USER=@USER;
    DELETE FROM users_activations WHERE token=$Token;
END IF;

END
;
CREATE PROCEDURE Users_Activations_Confirm_Account_And_Authenticate
(
  $Token                           CHAR(64)
)
BEGIN

SELECT USER INTO @USER FROM users_activations WHERE token=$Token;

SET @sessionid = "";

IF @USER != 0 THEN
    SELECT email, email_provisional INTO @email, @email_provisional
	FROM users WHERE USER=@USER;
	
	IF "" != @email_provisional THEN
	    SET @email = @email_provisional;
    END IF;

    UPDATE users SET email=@email, email_provisional='', user_status='CONFIRMED' WHERE USER=@USER;
    DELETE FROM users_activations WHERE token=$Token;

    SET @sessionid = MD5( concat( $Token, NOW() ) );
    REPLACE INTO users_sessions VALUES ( @sessionid, @Email, NOW(), NOW(), UNIX_TIMESTAMP() + 1000 );
END IF;

SELECT @sessionid AS sessionid;

END
;
CREATE PROCEDURE Users_Send_Resets_Replace
(
  $Email      CHAR(99)
)
BEGIN

SET @token = MD5( CONCAT( MD5(RAND()), NOW(), MD5(RAND()) ) );

SELECT USER INTO @USER FROM users WHERE email=$Email;

IF "" != @USER THEN
    REPLACE INTO users_send_resets VALUES ( @USER, NOW(), @token, 0 );
END IF;

END
;
CREATE PROCEDURE Users_Send_Resets_Retrieve
()
BEGIN

SELECT * FROM users_send_resets LEFT JOIN users USING (USER) WHERE users_send_resets.sent=0;
#'0000-00-00 00:00:00';

END
;
CREATE PROCEDURE Users_Send_Resets_Sent
(
$email  CHAR(99)
)
BEGIN

SELECT USER INTO @USER FROM users_reset_passwords LEFT JOIN users USING (USER) WHERE email=$email LIMIT 1;

UPDATE users_send_resets SET sent=NOW() WHERE USER=@USER;

END
;
CREATE PROCEDURE Users_Send_Resets_Reset_Password
(
  $Token                           CHAR(64),
  $Password                        CHAR(99)
)
BEGIN

SELECT USER INTO @USER FROM users_send_resets WHERE token=$Token;

IF @USER != 0 THEN
    SELECT email INTO @email FROM users WHERE USER=@USER;

    SET @salt  = RAND() * 1000;
    SET @uhash = Users_Compute_Hash( @salt, @email );
    SET @phash = Users_Compute_Hash( @salt, $Password );

    UPDATE users
    SET user_salt=@salt, user_hash=@uhash, password_hash=@phash, invalid_logins=0
    WHERE USER=@USER;

    DELETE FROM users_send_resets WHERE token=$Token;
END IF;

END
;
CREATE FUNCTION Users_Send_Resets_Exists
(
  $Token      CHAR(64)
)
RETURNS BOOL
READS SQL DATA
BEGIN

return EXISTS( SELECT * FROM users_send_resets WHERE token=$Token );

END
;
CREATE PROCEDURE Users_Sessions_Replace
(
  $Email                           CHAR(99),
  $Password                        CHAR(99)
)
BEGIN

SELECT email          INTO @email   FROM users WHERE email=$Email;
SELECT user_salt      INTO @salt    FROM users WHERE email=$Email;
SELECT password_hash  INTO @phash1  FROM users WHERE email=$Email;
SELECT invalid_logins INTO @invalid FROM users WHERE email=$Email;

IF "" != @email THEN

    IF @invalid < 4 THEN

        SET @phash2 = Users_Compute_Hash( @salt, $Password );

        IF @phash1=@phash2 THEN

            SET @sessionid = MD5( concat( concat($Email,$Password), NOW() ) );
            REPLACE INTO users_sessions VALUES ( @sessionid, $Email, NOW(), NOW(), UNIX_TIMESTAMP() + 1000 );
            UPDATE users SET invalid_logins = 0, last_login=NOW() WHERE email=$Email;

        ELSE

            UPDATE users SET invalid_logins = @invalid + 1 WHERE email=$Email;
            SET @sessionid = "INVALID_PASSWORD";

        END IF;
    ELSE

        SET @sessionid = "INVALID_LOGINS";

    END IF;

ELSE

    SET @sessionid = "INVALID_USER";

END IF;

SELECT @sessionid AS sessionid;

END
;
CREATE PROCEDURE Users_Sessions_Terminate
(
  $Sid                             CHAR(32)
)
BEGIN

DELETE FROM users_sessions WHERE sid=$Sid;

END
;
CREATE FUNCTION Users_Sessions_Verify
(
  $Sid CHAR(32)
)
RETURNS BOOLEAN
READS SQL DATA
BEGIN

SET @time = UNIX_TIMESTAMP();

SELECT expiry INTO @expiry FROM users_sessions WHERE sid=$Sid;

IF @time < @expiry THEN
    SET @ret = True;
ELSE
    SET @ret = False;
END IF;

return @ret;

END
;
CREATE PROCEDURE Users_Sessions_Extend_Expiry
(
  $Sid CHAR(32)
)
BEGIN

SET @time = UNIX_TIMESTAMP();

SELECT expiry INTO @expiry FROM users_sessions WHERE sid=$Sid;

IF @time < @expiry THEN
    SET @expiry = @expiry + 1000;
    UPDATE users_sessions SET expiry=@expiry WHERE sid=$Sid;
ELSE
    UPDATE users_sessions SET expiry=0       WHERE sid=$Sid;
END IF;

END
;
CREATE PROCEDURE Users_Authorise_Sessionid
(
      $Sid          CHAR(32),
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
      $Sid          CHAR(32),
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
  $Sid CHAR(32)
)
BEGIN

IF Users_Sessions_Verify( $Sid ) THEN

    CALL Users_Sessions_Extend_Expiry( $Sid );

    SELECT email       INTO @email       FROM users_sessions WHERE sid   = $Sid;
    SELECT USER        INTO @USER        FROM users          WHERE email = @email;
    SELECT given_name  INTO @given       FROM users          WHERE email = @email;
    SELECT family_name INTO @family      FROM users          WHERE email = @email;
    SELECT type        INTO @idtype      FROM users_uids     WHERE USER  = @USER;
    SELECT last_login  INTO @last_login  FROM users          WHERE email = @email;
    SELECT user_status INTO @user_status FROM users          WHERE email = @email;
    SELECT user_hash   INTO @user_hash   FROM users          WHERE email = @email;

    SELECT @email AS email, @USER AS USER, @given AS given_name, @family AS family_name, @idtype AS idtype, @last_login AS last_login, @user_status AS user_status, @user_hash AS user_hash;

ELSE

    CALL Users_Sessions_Terminate( $Sid );

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Single
(
  $Sid                       CHAR(32),
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
  $Sid                       CHAR(32),
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
  $Sid                       CHAR(32),
  $USER                       INT(11),
  $email                  VARCHAR(99)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF @USER = $USER THEN

  SET @token = concat( MD5( concat( concat($email,RAND()), NOW() ) ), MD5( concat( concat($email,RAND()), NOW() ) ) );

  REPLACE INTO users_alternate_emails VALUES ( $USER, $email, @token );

END IF;

END
;
CREATE PROCEDURE Users_Alternate_Emails_Delete
(
  $Sid                       CHAR(32),
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
  $Sid                       CHAR(32),
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
  $Sid                        CHAR(32),
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
  $Sid  CHAR(32),
  $USER INT(11)
)
BEGIN

IF "" != $Sid THEN
  CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );
  INSERT INTO users_deleted VALUES ( @USER, $USER );
END IF;

SELECT email INTO @email FROM users WHERE USER=$USER;

DELETE FROM users_activations          WHERE USER=$USER;
DELETE FROM users_alternate_emails     WHERE USER=$USER;
DELETE FROM users_reset_passwords      WHERE USER=$USER;
DELETE FROM users_sessions             WHERE email=@email;
DELETE FROM users_uids                 WHERE USER=$USER;
DELETE FROM users_termination_schedule WHERE USER=$USER;
DELETE FROM users                      WHERE USER=$USER;

END
;
CREATE PROCEDURE Payments_Customers_Uncreated
()
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT * FROM view_payments_customers_uncreated;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Replace
(
  $USER                             INT(11),
  $customer_id                     CHAR(16)
)
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  REPLACE INTO payments_customers
    (  USER,  created,  customer_id )
  VALUES
    ( $USER,    NOW(), $customer_id );

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Delete
(
  $sid                             CHAR(32),
  $USER                             INT(11)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

  SELECT customer_id INTO @customer_id FROM payments WHERE USER=$USER;
  
  REPLACE INTO payments_remove_cards
    (  USER,  customer_id )
  VALUES
    ( $USER, @customer_id );

  DELETE FROM payments_customers WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Retrieve_By_User
(
  $sid                             CHAR(32),
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
  $sid                             CHAR(32),
  $USER                             INT(11),
  $plan_id                         CHAR(32),
  $cost                         DECIMAL(13,2)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN


  SET @switched = NOW();
  SET @subscription_id = '';

  SELECT subscription_id INTO @subscription_id
  FROM payments_plans
  WHERE USER=$USER AND NOT subscription_id = "" ORDER BY PLAN DESC LIMIT 1;

  REPLACE INTO payments_plans
    (  USER,  switched,  plan_id,  cost,  subscription_id )
  VALUES
    ( $USER, @switched, $plan_id, $cost, @subscription_id );

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

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

  SELECT *
  FROM      payments_purchases
  LEFT JOIN view_payments_credit_cards USING (USER)
  WHERE NOT processed=0 AND transacted=0;

ELSE

  SELECT "You must call this method from local host" AS msg;

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Transacted
(
  $PURCHASE               INT(11),
  $transaction_id        CHAR(16)
)
BEGIN

SET @USER = USER();

IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

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

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

SET @FILE = $FILE;

IF $USER = @USER OR "ADMIN" = @idtype THEN

    SET @salt  = 1;
    SET @token = MD5( concat( $base64, @salt ) ); 

    WHILE EXISTS( SELECT * FROM files WHERE token=@token ) DO
        SET @salt = @salt + 1;
        SET @token = MD5( concat( $base64, @salt ) ); 
    END WHILE;

    REPLACE INTO files
        (  FILE,  USER,  version,  kind,  original_filename,  filename,  filetype,  filesize,  fileextension,  salt,  token,  base64 )
    VALUES
        ( $FILE, $USER,    NOW(), $kind, $original_filename, $filename, $filetype, $filesize, $fileextension, @salt, @token, $base64 );

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
