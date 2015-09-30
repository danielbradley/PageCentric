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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    DELETE FROM users_sessions WHERE expiry < UNIX_TIMESTAMP();

    SELECT user_salt      INTO $salt    FROM users WHERE email=$email;
    SELECT password_hash  INTO $phash1  FROM users WHERE email=$email;
    SELECT invalid_logins INTO $invalid FROM users WHERE email=$email;

    IF "" != $email AND "" != $password THEN

        SET $phash2 = Users_Compute_Hash( $salt, $password );

        IF $phash1=$phash2 THEN

            SET $sessionid = generate_salt();

            WHILE EXISTS( SELECT * FROM users_sessions WHERE sid=$sessionid ) DO

                SET $sessionid = generate_salt();

            END WHILE;

            REPLACE INTO users_sessions VALUES ( $sessionid, $email, NOW(), NOW(), UNIX_TIMESTAMP() + 1000 );
            UPDATE users SET invalid_logins = 0, last_login=NOW(), visits = visits + 1 WHERE email=$email;

            SELECT "OK" AS status, $sessionid AS sessionid, USER, type AS idtype FROM view_users WHERE email=$email;

        ELSE

            UPDATE users SET invalid_logins = $invalid + 1 WHERE email=$Email;

            IF $invalid > 4 AND "" != $password THEN

                SET @bougus = SLEEP( $invalid );
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_LOGINS';

            ELSE

                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_PASSWORD';

            END IF;

        END IF;

	ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_USER';

	END IF;

END IF;

END
;
CREATE PROCEDURE Users_Sessions_Retrieve
(
  $Sid                             CHAR(64),
  $USER                            CHAR(64),
  $user_hash                       CHAR(64),
  $order                           CHAR(99),
  $limit                            INT(11),
  $offset                           INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

END IF;

END
;
CREATE PROCEDURE Users_Sessions_Retrieve_Current
(
  $Sid                             CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SELECT * FROM view_users_sessions WHERE sessionid=$Sid;

END IF;

END
;
CREATE PROCEDURE Users_Sessions_Terminate
(
  $Sid                             CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	DELETE FROM users_sessions WHERE sid=$Sid;

END IF;

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
DECLARE $ret    BOOL DEFAULT FALSE;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET $now    = UNIX_TIMESTAMP();
	SET $ret    = False;

	SELECT expiry INTO $expiry FROM users_sessions WHERE sid=$Sid;

	IF $now < $expiry THEN
		SET $ret = True;
	END IF;

END IF;

return $ret;

END
;
CREATE PROCEDURE users_sessions_extend_expiry
(
  $Sid CHAR(64)
)
BEGIN

DECLARE $expiry   INT;
DECLARE $now      INT;
DECLARE $ret      BOOL;
DECLARE read_only BOOL;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SET $now = UNIX_TIMESTAMP();

    SELECT expiry INTO $expiry FROM users_sessions WHERE sid=$Sid;

    IF $now < $expiry THEN
        SET $expiry = $expiry + 1000;
        UPDATE users_sessions SET expiry=$expiry WHERE sid=$Sid;
    ELSE
        UPDATE users_sessions SET expiry=0       WHERE sid=$Sid;
    END IF;

END IF;

END
;
