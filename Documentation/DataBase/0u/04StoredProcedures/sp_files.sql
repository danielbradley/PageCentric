CREATE PROCEDURE Files_Replace
(
  $Sid                             CHAR(64),
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

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER OR "ADMIN" = @idtype OR Is_Local_Caller() THEN

      SET $token = generate_salt();

      WHILE EXISTS( SELECT * FROM files WHERE token=$token ) DO
        SET $token = generate_salt();
      END WHILE;

      REPLACE INTO files
        (  FILE,  USER,  version,  kind,  original_filename,  filename,  filetype,  filesize,  fileextension,  salt,  token,  base64 )
      VALUES
        ( $FILE, $USER,    NOW(), $kind, $original_filename, $filename, $filetype, $filesize, $fileextension,     0, $token, $base64 );

      SELECT LAST_INSERT_ID() INTO $FILE;

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

    SELECT $FILE AS FILE;

END IF;

END
;
CREATE FUNCTION Files_Exists_By_Kind
(
  $Sid                             CHAR(64),
  $USER                             INT(11),
  $kind                            CHAR(30)
)
RETURNS BOOL
READS SQL DATA
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER OR "ADMIN" = @idtype THEN

        return EXISTS( SELECT * FROM files WHERE USER=$USER AND kind=$kind );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Files_Retrieve_Info_By_Kind
(
  $Sid                             CHAR(64),
  $USER                             INT(11),
  $kind                            CHAR(30)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER OR "ADMIN" = @idtype THEN

      SELECT * FROM view_files WHERE USER=$USER AND kind=$kind ORDER BY version DESC;

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Files_Retrieve
(
  $Sid                             CHAR(64),
  $FILE                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    SELECT * FROM files WHERE USER=@USER AND FILE=$FILE ORDER BY version DESC LIMIT 1;

END IF;

END
;
CREATE PROCEDURE Files_Retrieve_By_Token
(
  $Sid                             CHAR(64),
  $token                           CHAR(64)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SELECT * FROM files WHERE token=$token ORDER BY version DESC LIMIT 1;

END IF;

END
;
