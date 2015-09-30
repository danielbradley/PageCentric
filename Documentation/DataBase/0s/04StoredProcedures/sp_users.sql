CREATE PROCEDURE users_uid_create( $Type VARCHAR(20) )
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	INSERT INTO users_uids (type) VALUES ( $Type );
	SELECT LAST_INSERT_ID() AS USER;

END IF;

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

DECLARE $USER   INT;
DECLARE $salt   TEXT;
DECLARE $uhash  TEXT;
DECLARE $phash  TEXT;
DECLARE $status TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

        IF Users_Exists( $Email ) THEN

            SELECT "OK" AS status, $USER AS USER;

        ELSE

            SELECT "ERROR" AS status, "UNEXPECTED_ERROR" AS message;

        END IF;

	ELSE

        SELECT "ERROR" AS status, "USER_EXISTS" AS message;

	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve
(
$Sid                           CHAR(64),
$USER                           INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

		SELECT * FROM users WHERE USER=$USER;

	END IF;

END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER OR "ADMIN" = @idtype THEN

		SELECT email             INTO $email             FROM users WHERE USER=$USER;
		SELECT email_provisional INTO $email_provisional FROM users WHERE USER=$USER;

		IF $new_email != @email THEN
			SET $email_provisional = $new_email;
		END IF;

		UPDATE users
		SET email_provisional=$email_provisional, given_name=$new_given_name, family_name=$new_family_name
		WHERE USER=$USER;

	END IF;

END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF EXISTS( SELECT * FROM users WHERE email=$Email ) THEN

		SELECT user_salt     INTO $salt   FROM users WHERE email=$Email;
		SELECT password_hash INTO $phash1 FROM users WHERE email=$Email;

		SET $phash2 = Users_Compute_Hash( $salt, $Password );

		IF $phash1 = $phash2 THEN
			SET $ret = True;
		END IF;

	END IF;

END IF;

return $ret;

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

DECLARE $valid BOOLEAN DEFAULT FALSE;
DECLARE $email TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT email INTO $email FROM users WHERE USER=$USER;

	SET $valid = users_verify_credentials( $email, $Password );

END IF;

return $valid;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

END IF;

END
;
CREATE PROCEDURE users_authorise_sessionid
(
      $Sid          CHAR(64),
  OUT $Email        CHAR(99),
  OUT $USER          INT(11),
  OUT $IDType    VARCHAR(20)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Users_Sessions_Verify( $Sid ) THEN

		CALL Users_Sessions_Extend_Expiry( $Sid );

		SELECT email    INTO $Email    FROM users_sessions WHERE sid      = $Sid;
		SELECT USER     INTO $USER     FROM users          WHERE email    = $Email;
		SELECT type     INTO $IDType   FROM users_uids     WHERE USER     = $USER;

	ELSE

		CALL Users_Sessions_Terminate( $Sid );

	END IF;

END IF;

END
;
CREATE PROCEDURE users_authorize_sessionid
(
      $Sid          CHAR(64),
  OUT $Email        CHAR(99),
  OUT $USER          INT(11),
  OUT $IDType    VARCHAR(20)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, $Email, $USER, $IDType );

END IF;

END
;
CREATE PROCEDURE Users_Authenticate
(
  $Sid CHAR(64)
)
BEGIN

DECLARE $read_only BOOL;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Users_Sessions_Verify( $Sid ) THEN

	  CALL Users_Sessions_Extend_Expiry( $Sid );

	  SELECT email, USER, given_name, family_name, type AS idtype, last_login, user_status, user_hash, $read_only AS read_only
	  FROM users_sessions
	  LEFT JOIN view_users USING (email) WHERE sid=$Sid;

	ELSE

	  CALL Users_Sessions_Terminate( $Sid );

	END IF;

END IF;

END
;
CREATE PROCEDURE users_create_quietly
(
$Email                     CHAR(99),
$Password                  CHAR(99),
$Given_name                CHAR(50),
$Family_name               CHAR(50),
$Type                      CHAR(20),
$Sent                      BOOL
)
BEGIN

DECLARE $USER   INT;
DECLARE $salt   TEXT;
DECLARE $uhash  TEXT;
DECLARE $phash  TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    IF ! Users_Exists( $Email ) THEN

      INSERT INTO users_uids (type) VALUES ( $Type );
      SET $USER  = LAST_INSERT_ID();
      SET $salt  = generate_salt();
      SET $uhash = Users_Compute_Hash( $salt, $Email    );
      SET $phash = Users_Compute_Hash( $salt, $Password );

      INSERT INTO users
        (  USER,  email, created, last_login, invalid_logins, user_salt, user_hash, password_hash,  sent,   user_status,  given_name,  family_name )
      VALUES
        ( $USER, $Email,   NOW(),          0,              0,     $salt,    $uhash,        $phash, $Sent, "UNCONFIRMED", $Given_name, $Family_name );

    END IF;

END IF;

END
;
CREATE PROCEDURE Users_Activation_Sent
(
  $Email              CHAR(99),
  $Password           CHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Users_Vefify_Credentials( $Email, $Password ) THEN

		UPDATE users SET sent=1 WHERE email=$Email;

	ELSE
	
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

	END IF;

END IF;

END
;
CREATE PROCEDURE users_change_password_with_USER
(
  $USER         INT(11),
  $OldPassword CHAR(99),
  $NewPassword CHAR(99)
)
BEGIN

DECLARE $email TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT email INTO $email FROM users WHERE USER=$USER;

	IF "" != $email THEN

	  CALL users_change_password( $email, $OldPassword, $NewPassword );

	END IF;

END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Root_Caller() THEN

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

	END IF;

END IF;

END
;
CREATE PROCEDURE users_reset_password
(
  $Sid          CHAR(64),
  $USER          INT(11),
  $new_password CHAR(99)
)
BEGIN

DECLARE $email TEXT;
DECLARE $salt  TEXT;
DECLARE $uhash TEXT;
DECLARE $phash TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF "ADMIN" = @idtype THEN

        SELECT email INTO $email FROM users WHERE USER=$USER;

        SET $salt  = generate_salt();
        SET $uhash = Users_Compute_Hash( $salt, $email        );
        SET $phash = Users_Compute_Hash( $salt, $new_password );

        UPDATE users
        SET user_salt=$salt, user_hash=$uhash, password_hash=$phash
        WHERE USER=$USER;

    END IF;

END IF;

END
;
CREATE FUNCTION Users_Exists( $Email CHAR(99) )
RETURNS BOOLEAN
READS SQL DATA
BEGIN

DECLARE $exists BOOLEAN DEFAULT FALSE;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	return Exists( SELECT email FROM users WHERE email=$Email );

END IF;

return $exists;

END
;
CREATE PROCEDURE users_create_admin
(
  $password                      CHAR(99)
)
BEGIN

DECLARE $sent_email INT DEFAULT 1;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    IF Is_Local_Root_Caller() THEN

        CALL users_create_quietly( 'admin', $password, 'Admin', 'Account', 'ADMIN', $sent_email );

    END IF;

END IF;

END
;
CREATE PROCEDURE Users_Resend_Activation
(
$email                     CHAR(99)
)
BEGIN

DECLARE $USER INT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT USER INTO $USER FROM users WHERE email=$email;

	IF EXISTS( SELECT * FROM users_activations WHERE USER=$USER ) THEN

		UPDATE users SET sent=0 WHERE email=$email;

	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_All
(
$Sid                           CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF "ADMIN" = @idtype THEN

        SELECT * FROM view_users ORDER BY USER DESC;

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_By_User_Hash
(
  $sid                             CHAR(64),
  $user_hash                       CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF "" != @idtype THEN

		SELECT * FROM users WHERE user_hash=$user_hash;

	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Unsent
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT * FROM view_users WHERE sent=0;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Unsent_With_Names
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT * FROM view_users WHERE sent=0 AND NOT given_name='' AND NOT family_name='';

END IF;

END
;
CREATE PROCEDURE Users_Update_Sent
(
  $Email              CHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	UPDATE users SET sent=1 WHERE email=$Email OR email_provisional=$Email;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Single
(
  $Sid                       CHAR(64),
  $USER                       INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF @USER = $USER THEN

	  SELECT * FROM users WHERE USER=$USER LIMIT 1;

	END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF @USER = $USER THEN

	  UPDATE users SET given_name=$given_name, family_name=$family_name WHERE USER=$USER;

	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Retrieve_Signups
(
$days INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT email, sent, user_status, given_name, family_name, user_hash FROM users WHERE created > DATE_SUB( NOW(), INTERVAL $days DAY );

END IF;

END
;
CREATE PROCEDURE Users_Requested_Invites_Replace
(
  $email                         CHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF NOT EXISTS( SELECT * FROM users_requested_invites WHERE email=$email ) THEN
		REPLACE INTO users_requested_invites
			   (  REQUEST,  email, time_of_request, invite_sent )
		VALUES (        0, $email,           NOW(),           0 );
	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Requested_Invites_Retrieve
(
  $sid                           CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF "SID" = @idtype THEN
        IF NOT EXISTS( SELECT * FROM users_requested_invites WHERE email=@email ) THEN
            SELECT * FROM users_requested_invites ORDER BY time_of_request;
        END IF;
    END IF;

END IF;

END
;
CREATE PROCEDURE Users_Activations_Create
(
  $email                           CHAR(99)
)
BEGIN

DECLARE $USER  INT;
DECLARE $token TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET $token = generate_salt();

	SELECT USER INTO $USER FROM users WHERE email=$email OR email_provisional=$email;

	IF "" != $USER THEN
	  REPLACE INTO users_activations VALUES ( $USER, NOW(), $token );
	END IF;

	SELECT $token AS token;

END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

END IF;

END
;
CREATE PROCEDURE Users_Send_Resets_Replace
(
  $email      CHAR(99)
)
BEGIN

DECLARE $USER  INT;
DECLARE $token TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET $token = generate_salt();

	SELECT USER INTO $USER FROM users WHERE email=$email;

	IF "" != $USER THEN
	  REPLACE INTO users_send_resets VALUES ( $USER, NOW(), $token, 0 );
	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Send_Resets_Retrieve
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT * FROM users_send_resets LEFT JOIN users USING (USER) WHERE users_send_resets.sent=0;

END IF;

END
;
CREATE PROCEDURE Users_Send_Resets_Sent
(
  $email  CHAR(99)
)
BEGIN

DECLARE $USER INT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT USER INTO $USER FROM users_send_resets LEFT JOIN users USING (USER) WHERE email=$email LIMIT 1;

	UPDATE users_send_resets SET sent=NOW() WHERE USER=$USER;

END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT USER INTO $USER FROM users_send_resets WHERE token=$token;

	IF 0 != $USER THEN
		SELECT email INTO $email FROM users WHERE USER=$USER;

		SET $salt  = generate_salt();
		SET $uhash = Users_Compute_Hash( $salt, $email );
		SET $phash = Users_Compute_Hash( $salt, $Password );

		UPDATE users
		SET user_salt=$salt, user_hash=$uhash, password_hash=$phash, invalid_logins=0
		WHERE USER=$USER;

		DELETE FROM users_send_resets WHERE token=$token;

		SELECT "OK" AS status, $email AS username;

	ELSE

		SELECT "ERROR" AS status, "INVALID_TOKEN" AS message;

	END IF;

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

DECLARE $exists BOOLEAN DEFAULT FALSE;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET $exists = EXISTS( SELECT * FROM users_send_resets WHERE token=$token );

END IF;

return $exists;

END
;
CREATE PROCEDURE Users_Alternate_Emails_Create
(
  $Sid                       CHAR(64),
  $USER                       INT(11),
  $email                  VARCHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF @USER = $USER THEN

	  REPLACE INTO users_alternate_emails VALUES ( $USER, $email, generate_salt() );

	END IF;

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF @USER = $USER THEN
		DELETE FROM users_alternate_emails WHERE USER=$USER AND email=$Email;
	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Alternate_Emails_Retrieve_By_USER
(
  $Sid                       CHAR(64),
  $USER                       INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF @USER = $USER THEN
		SELECT * FROM users_alternate_emails WHERE USER=$USER ORDER BY email;
	END IF;

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


IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

END IF;

END
;
CREATE PROCEDURE Users_Termination_Schedule_Retrieve
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT USER, time_of_termination, email
	FROM users_termination_schedule LEFT JOIN users USING (USER)
	WHERE NOW() > time_of_termination;

END IF;

END
;
CREATE PROCEDURE Users_Delete
(
  $Sid  CHAR(64),
  $USER INT(11)
)
BEGIN

DECLARE $email TEXT;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

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

END IF;

END
;
