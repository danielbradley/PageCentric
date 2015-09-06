CREATE FUNCTION DistanceBetween
(
  $lat1  FLOAT,
  $lon1  FLOAT,
  $lat2  FLOAT,
  $lon2  FLOAT
)
RETURNS FLOAT
DETERMINISTIC
BEGIN

DECLARE $radius_earth_km INT DEFAULT 6371;

DECLARE $distance FLOAT;
DECLARE $sin1     FLOAT;
DECLARE $sin2     FLOAT;
DECLARE $cos1     FLOAT;
DECLARE $cos2     FLOAT;
DECLARE $power1   FLOAT;
DECLARE $power2   FLOAT;

SET $sin1 = SIN( ($lat1 - $lat2) * pi()/180/2);
SET $sin2 = SIN( ($lon1 - $lon2) * pi()/180/2);

SET $cos1 = COS( $lat1 * pi()/180);
SET $cos2 = COS( $lat2 * pi()/180);

SET $power1 = POWER( $sin1, 2 );
SET $power2 = POWER( $sin2, 2 );

SET $distance = $radius_earth_km * 2 * ASIN( SQRT( $power1 + $cos1 * $cos2 * $power2 ) );

return $distance;

END
;
