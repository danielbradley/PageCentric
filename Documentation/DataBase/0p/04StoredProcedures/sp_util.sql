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
