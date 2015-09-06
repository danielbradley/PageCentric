CREATE PROCEDURE Articles_Replace
(
    $ARTICLE                                              INT(11),
    $modified                                            DATETIME,
    $id                                                  CHAR(99),
    $source                                              CHAR(30),
    $category                                            CHAR(99),
    $subject                                             CHAR(99),
    $date                                                CHAR(30),
    $session                                              INT(11),
    $title                                               CHAR(99),
    $video_type                                          CHAR(20),
    $video_code                                          CHAR(99),
    $h1                                                  CHAR(99),
    $address                                             CHAR(99),
    $section                                                 TEXT,
    $section_references                                      TEXT
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    IF Is_Local_Caller() THEN

        IF 0 = $ARTICLE THEN

           SELECT ARTICLE INTO $ARTICLE FROM articles WHERE subject=$subject AND title=$title;

        END IF;

        IF 0 = $modified THEN

            SET $modified = NOW();

        END IF;

        REPLACE INTO articles
            (  ARTICLE,  modified,  id,  source,  category,  subject,  date,  session,  title,  video_type,  video_code,  h1,  address,  section,  section_references )
        VALUES
            ( $ARTICLE, $modified, $id, $source, $category, $subject, $date, $session, $title, $video_type, $video_code, $h1, $address, $section, $section_references );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Articles_Retrieve
(
    $ARTICLE                                              INT(11),
    $filter                                              CHAR(99),
    $order                                               CHAR(99),
    $limit                                                INT(11),
    $offset                                               INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SELECT * FROM view_articles;

END IF;

END
;
CREATE PROCEDURE Articles_Retrieve_Subset
(
    $ARTICLE                                              INT(11),
    $source                                              CHAR(30),
    $category                                            CHAR(99),
    $subject                                             CHAR(99),
    $session                                              INT(11),
    $filter                                              CHAR(99),
    $order                                               CHAR(99),
    $limit                                                INT(11),
    $offset                                               INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SET @query = "SELECT * FROM view_articles WHERE TRUE";

    CALL CheckLimitOffset( $limit, $offset );

    IF "" != $ARTICLE THEN
        SET @query = concat( @query, " AND ARTICLE = '", $ARTICLE, "'" );
    ELSE
        #SET @query = concat( @query, " AND NOT hashed_id = ''" );

        IF "" != $source THEN
            SET @query = concat( @query, " AND source = '", $source, "'" );
        END IF;

        IF "" != $category THEN
            SET @query = concat( @query, " AND category = '", $category, "'" );
        END IF;

        IF "" != $subject THEN
            SET @query = concat( @query, " AND subject = '", $subject, "'" );
        END IF;

        IF "" != $session THEN
            SET @query = concat( @query, " AND session = '", $session, "'" );
        END IF;

        IF "" != $order THEN
            SET @query = concat( @query, " ORDER BY ", $order );
        END IF;

        IF "" != $limit THEN
            SET @query = concat( @query, " LIMIT ", $limit );
        END IF;

        IF "" != $offset THEN
            SET @query = concat( @query, " OFFSET ", $offset );
        END IF;
    END IF;

    PREPARE Statement FROM @query;
    EXECUTE Statement;
    DROP PREPARE Statement;

END IF;

END
;
CREATE PROCEDURE Articles_Retrieve_Subjects
(
    $source                                              CHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SELECT source, category, subject FROM view_articles WHERE source=$source GROUP BY source, category, subject ORDER BY source, category, subject;

END IF;

END
;
CREATE PROCEDURE Articles_Info_Replace
(
    $hashed_id                  CHAR(32),
    $updated                    CHAR(29),
    $duration                   INT(5.2),
    $filename                  CHAR(255),
    $filetype                   CHAR(99),
    $filesize                   CHAR(45),
    $fileextension              CHAR(10),
    $base64                   MEDIUMTEXT
)
BEGIN

DECLARE $modified           DATETIME;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SET $modified = ConvertToLocalTimeZone( $updated );

    IF Is_Local_Caller() THEN

        REPLACE INTO articles_info
            (  hashed_id,  modified,  duration,  filename,  filetype,  filesize,  fileextension,  base64 )
        VALUES
            ( $hashed_id, $modified, $duration, $filename, $filetype, $filesize, $fileextension, $base64 );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Articles_Info_Retrieve
(
    $hashed_id                                           CHAR(32),
    $filter                                              CHAR(99),
    $order                                               CHAR(99),
    $limit                                                INT(11),
    $offset                                               INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL CheckLimitOffset( $limit, $offset );

    IF "" != $hashed_id THEN

        SELECT * FROM articles_info WHERE hashed_id=$hashed_id;

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'MISSING_HASH_ID';

    END IF;

END IF;

END
;
CREATE FUNCTION Articles_Info_Contains
(
    $hashed_id                  CHAR(32),
    $updated                    CHAR(29)
)
RETURNS BOOL
DETERMINISTIC
BEGIN

DECLARE $contains BOOL DEFAULT FALSE;
DECLARE $modified           DATETIME;

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    SET $modified = ConvertToLocalTimeZone( $updated );

    IF Is_Local_Caller() THEN

        SET $contains = EXISTS( SELECT * FROM articles_info WHERE hashed_id=$hashed_id AND modified < $modified );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

RETURN $contains;

END
;
