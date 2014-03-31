CREATE FUNCTION GET_JTH
(
  $Text                   TEXT,
  $Delimiter              CHAR(10),
  $I                      INT(11)
)
RETURNS TEXT
DETERMINISTIC
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
CREATE PROCEDURE Users_Reset_Passwords_Replace
(
  $Email      CHAR(99)
)
BEGIN

SET @token = MD5( CONCAT( MD5(RAND()), NOW(), MD5(RAND()) ) );

SELECT USER INTO @USER FROM users WHERE email=$Email;

IF "" != @USER THEN
    REPLACE INTO users_reset_passwords VALUES ( @USER, NOW(), @token, 0 );
END IF;

END
;
CREATE PROCEDURE Users_Reset_Passwords_Retrieve
()
BEGIN

SELECT * FROM users_reset_passwords LEFT JOIN users USING (USER) WHERE users_reset_passwords.sent=0;
#'0000-00-00 00:00:00';

END
;
CREATE PROCEDURE Users_Reset_Passwords_Sent
(
$email  CHAR(99)
)
BEGIN

SELECT USER INTO @USER FROM users_reset_passwords LEFT JOIN users USING (USER) WHERE email=$email LIMIT 1;

UPDATE users_reset_passwords SET sent=NOW() WHERE USER=@USER;

END
;
CREATE PROCEDURE Users_Reset_Passwords_Reset_Password
(
  $Token                           CHAR(64),
  $Password                        CHAR(99)
)
BEGIN

SELECT USER INTO @USER FROM users_reset_passwords WHERE token=$Token;

IF @USER != 0 THEN
    SELECT email INTO @email FROM users WHERE USER=@USER;

    SET @salt  = RAND() * 1000;
    SET @uhash = Users_Compute_Hash( @salt, @email );
    SET @phash = Users_Compute_Hash( @salt, $Password );

    UPDATE users
    SET user_salt=@salt, user_hash=@uhash, password_hash=@phash, invalid_logins=0
    WHERE USER=@USER;

    DELETE FROM users_reset_passwords WHERE token=$Token;
END IF;

END
;
CREATE FUNCTION Users_Reset_Passwords_Exists
(
  $Token      CHAR(64)
)
RETURNS BOOL
READS SQL DATA
BEGIN

return EXISTS( SELECT * FROM users_reset_passwords WHERE token=$Token );

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

    SELECT @email AS email, @USER AS USER, @given AS given_name, @family AS family_name, @idtype AS idtype, @last_login AS last_login, @user_status AS user_status;

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
CREATE PROCEDURE Invites_Replace
(
  $Sid                        CHAR(32),
  $USER                        INT(11),
  $invitee_name               CHAR(99),
  $invitee_email              CHAR(99)
)
BEGIN

CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

IF $USER = @USER THEN

REPLACE INTO invites
    (  USER,  invitee_email,  invitee_name,  invitation_sent )
VALUES
    ( $USER, $invitee_email, $invitee_name,                0 );

END IF;

END
;
CREATE PROCEDURE Invites_Retrieve_Unsent
()
BEGIN

SELECT * FROM invites LEFT JOIN view_users USING (USER) WHERE invitation_sent=0;

END
;
CREATE PROCEDURE Invites_Sent
(
  $USER                         INT(11),
  $invitee_email               CHAR(99)
)
BEGIN

UPDATE invites SET invitation_sent=NOW() WHERE USER=$USER AND invitee_email=$invitee_email;

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
