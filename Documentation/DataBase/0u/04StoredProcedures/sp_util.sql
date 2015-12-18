CREATE FUNCTION GET_JTH
(
  $Text                   TEXT,
  $Delimiter              TEXT,
  $I                      INT(11)
)
RETURNS TEXT
DETERMINISTIC
BEGIN

DECLARE $ret      CHAR(99) DEFAULT '';
DECLARE $tmp      CHAR(99) DEFAULT '';
DECLARE $test     CHAR(99) DEFAULT '';

IF 0 = LENGTH( $Delimiter ) THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'EMPTY_DELIMITER';

END IF;

#
#	Compare whether substring returned is same for i and i-1.
#	If so have run out of components, set return as "".
#

SET $tmp  = SUBSTRING_INDEX( $Text, $Delimiter, $I );
SET $test = SUBSTRING_INDEX( $Text, $Delimiter, $I - 1 );

IF $tmp != $test THEN

    SET $ret = SUBSTRING_INDEX( $tmp, $Delimiter, -1 );

END IF;

RETURN $ret;

END
;
CREATE FUNCTION Read_Only
()
RETURNS BOOL
READS SQL DATA
BEGIN

DECLARE $readonly BOOLEAN DEFAULT 0;

IF @@read_only THEN

  SET $readonly = 1;

END IF;

return $readonly;

END
;
CREATE FUNCTION generate_salt
()
RETURNS CHAR(64)
DETERMINISTIC
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
DETERMINISTIC
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
CREATE FUNCTION My_Encrypt
(
  enckey         TEXT,
  value          TEXT
)
RETURNS TEXT
DETERMINISTIC
BEGIN

DECLARE hashkey TEXT;

SET hashkey = SHA2( HEX( DES_ENCRYPT( enckey ) ), 256 );

return HEX( AES_ENCRYPT( value, hashkey ) );

END
;
CREATE FUNCTION My_Decrypt
(
  enckey            TEXT,
  encvalue          TEXT
)
RETURNS TEXT
DETERMINISTIC
BEGIN

DECLARE hashkey TEXT;

SET hashkey = SHA2( HEX( DES_ENCRYPT( enckey ) ), 256 );

return AES_DECRYPT( UNHEX( encvalue ), hashkey );

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
CREATE FUNCTION Is_Local_Root_Caller
()
RETURNS BOOL
DETERMINISTIC
BEGIN

DECLARE $USER TEXT;

SET $USER = USER();

return ('root@localhost' = $USER);

END
;
CREATE PROCEDURE CheckLimitOffset
(
INOUT $limit                       INT(11),
INOUT $offset                      INT(11)
)
BEGIN

IF "" = $limit THEN
  SET $limit = 1000000;
END IF;

IF "" = $offset THEN
  SET $offset = 0;
END IF;

END
;
CREATE FUNCTION GetTimeZone
()
RETURNS CHAR(6)
DETERMINISTIC
BEGIN

DECLARE $diff TIME DEFAULT 0;

SET $diff = TIMEDIFF(NOW(), UTC_TIMESTAMP);

IF 0 <= $diff THEN
  return CONCAT( "+", $diff );
ELSE
  return $diff;
END IF;

END
;
CREATE FUNCTION AsAppleTime
(
  $datetime DATETIME
)
RETURNS TEXT
DETERMINISTIC
BEGIN

return CONCAT( DATE( $datetime ), "T", TIME( $datetime ), ".000", GetTimeZone() );

END
;
CREATE FUNCTION ConvertZoneToTime
(
  $zone CHAR(6)
)
RETURNS TIME
DETERMINISTIC
BEGIN

return CONVERT( REPLACE( $zone, "+", " " ), TIME );

END
;
CREATE FUNCTION ConvertTo
(
  $datetime DATETIME
)
RETURNS DATETIME
DETERMINISTIC
BEGIN

DECLARE $dx DATETIME DEFAULT 0;

SET $dx = DATE_SUB( $datetime, INTERVAL 1 HOUR );

return $dx;

END
;
CREATE FUNCTION ConvertToLocalTimeZone
(
  $appletime CHAR(29)
)
RETURNS DATETIME
DETERMINISTIC
BEGIN

# 2014-12-30T10:00:00.000+10:00

DECLARE $len           INT;
DECLARE $tmp      CHAR(29);
DECLARE $datetime DATETIME;
DECLARE $zone      CHAR(6);
DECLARE $test         TEXT;

SET $zone = "";
SET $test = "";

IF "" != $appletime THEN

  SET $len = LENGTH( $appletime );
  
  IF 10 = $len THEN

    SET $datetime = CONVERT( $appletime, DATETIME );
    SET $test     = CONCAT_WS( "|", $datetime );

  ELSEIF 19 = $len OR 23 = $len THEN
  
    SET $tmp      = REPLACE( $appletime, "T", " " );
    SET $tmp      = SUBSTRING( $tmp, 1, 19 );
    SET $datetime = CONVERT( $tmp, DATETIME );
    SET $test     = CONCAT_WS( "|", $datetime );

  ELSEIF 19 = $len OR 23 = $len THEN
  
    SET $tmp      = REPLACE( $appletime, "T", " " );
    SET $tmp      = SUBSTRING( $tmp, 1, 19 );
    SET $datetime = CONVERT( $tmp, DATETIME );
    SET $test     = CONCAT_WS( "|", $datetime );

  ELSEIF 24 = $len OR 28 = $len THEN

    SET $tmp      = REPLACE( $appletime, "T", " " );
    SET $tmp      = SUBSTRING( $tmp, 1, 19 );
    SET $datetime = CONVERT( $tmp, DATETIME );
    SET $zone     = SUBSTRING( $appletime, -5 );
    SET $zone     = REPLACE( $zone, ' ', '+' );
    SET $zone     = INSERT( $zone, 4, 0, ':' );
    SET $datetime = CONVERT_TZ( $datetime, $zone, GetTimeZone() );
    SET $test     = CONCAT_WS( "|", $tmp, $datetime, $zone, GetTimeZone() );

    #IF NULL = $datetime THEN
    #  SET $datetime = 0;
    #END IF;


  ELSEIF 25 = $len OR 29 = $len THEN

    SET $tmp      = REPLACE( $appletime, "T", " " );
    SET $tmp      = SUBSTRING( $tmp, 1, 19 );
    SET $datetime = CONVERT( $tmp, DATETIME );
    SET $zone     = SUBSTRING( $appletime, -6 );
    SET $zone     = REPLACE( $zone, ' ', '+' );
    SET $datetime = CONVERT_TZ( $datetime, $zone, GetTimeZone() );
    SET $test      = CONCAT_WS( "|", $tmp, $datetime, $zone, GetTimeZone() );

    #IF NULL = $datetime THEN
    #  SET $datetime = 0;
    #END IF;

  ELSE

    SET $test = "Wrong length";
    SET $datetime = 1;

  END IF;

ELSE

  SET $datetime = 2;

END IF;

return $datetime;

END
;
CREATE FUNCTION ConvertWeekToDate
(
  $year YEAR,
  $week INT(2)
)
RETURNS DATE
DETERMINISTIC
BEGIN

DECLARE $date_string   TEXT;
DECLARE $start_of_week DATE;

SET $date_string = CONCAT( $year, $week, " MONDAY" );

return STR_TO_DATE( $date_string, '%X%V %W' );

END
;
CREATE FUNCTION Is_Read_Only
()
RETURNS BOOLEAN
READS SQL DATA
BEGIN

DECLARE $read_only BOOLEAN DEFAULT 0;

SELECT @@global.read_only INTO $read_only;

return $read_only;

END
;
CREATE FUNCTION Get_Time
(
   $datetime TEXT
)
RETURNS TIME
DETERMINISTIC
BEGIN

DECLARE $time TIME DEFAULT 0;
DECLARE $one  CHAR(10);
DECLARE $two  CHAR(10);
DECLARE $use  CHAR(10);

SET $one = GET_JTH( $datetime, " ", 1 );
SET $two = GET_JTH( $datetime, " ", 2 );

IF "" != $two THEN

	SET $use = $two;

ELSE

	SET $use = $one;

END IF;

IF 4 = LENGTH( $use ) THEN

	SET $time = CONCAT( SUBSTR( $use, 1, 2 ), ":", SUBSTR( $use, 3, 2 ), ":00" );

ELSEIF 5 = LENGTH( $use ) THEN

	SET $time = CONCAT( $use, ":00" );

ELSEIF 8 = LENGTH( $use ) THEN

	SET $time = $use;

ELSE

    SET $time = "12:59:59";

END IF;

return $time;

END
;
