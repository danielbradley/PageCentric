CREATE PROCEDURE Preregistrations_Replace
(
  $name                    CHAR(99),
  $email                   CHAR(99),
  $info                        TEXT
)
BEGIN

DECLARE $token CHAR(64);

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SET $token = generate_salt();

    REPLACE INTO preregistrations
        (  name,  email,  info,  token,  created )
    VALUES
        ( $name, $email, $info, $token,    NOW() );

    SELECT "OK" AS status, $token AS token;

END IF;

END
;
