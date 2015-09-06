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
$Type                      CHAR(20)
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
		(  USER,  email, created, last_login, invalid_logins, user_salt, user_hash, password_hash, sent,   user_status,  given_name,  family_name )
	  VALUES
		( $USER, $Email,   NOW(),          0,              0,     $salt,    $uhash,        $phash,    0, "UNCONFIRMED", $Given_name, $Family_name );

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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Root_Caller() THEN
	  CALL users_create( 'admin', $password, 'Admin', 'Account', 'ADMIN' );
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
