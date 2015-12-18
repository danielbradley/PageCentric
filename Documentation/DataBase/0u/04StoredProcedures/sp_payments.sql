CREATE PROCEDURE Payments_Plans_Replace
(
    $PLAN_ID                      INT(11),
    $id                          CHAR(99),
    $billingDayOfMonth            INT(11),
    $billingFrequency                TEXT,
    $currencyIsoCode                 TEXT,
    $description                     TEXT,
    $name                            TEXT,
    $numberOfBillingCycles           TEXT,
    $price                           TEXT,
    $trialDuration                 INT(3),
    $trialDurationUnit               TEXT,
    $trialPeriod                     TEXT,
    $createdAt                   DATETIME,
    $updatedAt                   DATETIME
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Caller() THEN

        SELECT PLAN_ID INTO $PLAN_ID FROM payments_plans WHERE id=$id;

        REPLACE INTO payments_plans
            (  PLAN_ID,  id,  billingDayOfMonth,  billingFrequency,  currencyIsoCode,  description,  name,  numberOfBillingCycles,  price,  trialDuration,  trialDurationUnit,  trialPeriod,  createdAt,  updatedAt )
        VALUES
            ( $PLAN_ID, $id, $billingDayOfMonth, $billingFrequency, $currencyIsoCode, $description, $name, $numberOfBillingCycles, $price, $trialDuration, $trialDurationUnit, $trialPeriod, $createdAt, $updatedAt );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Uncreated
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Caller() THEN

	  SELECT * FROM view_payments_customers_uncreated;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Replace
(
  $USER                             INT(11),
  $customer_id                     CHAR(16)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Caller() THEN

	  REPLACE INTO payments_customers
		(  USER,  created,  customer_id )
	  VALUES
		( $USER,    NOW(), $customer_id );

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Delete
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

DECLARE $customer_id CHAR(16);

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

	  SELECT customer_id INTO $customer_id FROM payments WHERE USER=$USER;
	  
	  REPLACE INTO payments_remove_cards
		(  USER,  customer_id )
	  VALUES
		( $USER, $customer_id );

	  DELETE FROM payments_customers WHERE USER=$USER;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Customers_Retrieve_By_User
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

	  SELECT * FROM payments_customers WHERE USER=$USER;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Replace
(
  $sid                             CHAR(64),
  $USER                             INT(11),
  $final_four                      CHAR(4),
  $month                           TEXT,
  $year                            TEXT,
  $nonce                           TEXT
)
BEGIN

DECLARE $provided DATETIME DEFAULT NOW();

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER THEN

        CALL Payments_Credit_Cards_Delete( $sid, $USER );

        REPLACE INTO payments_credit_cards
            (  USER,  provided,  final_four,  month,  year,  nonce,  token,  processed )
        VALUES
            ( $USER, $provided, $final_four, $month, $year, $nonce,     '',          0 );

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Delete
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

DECLARE $customer_id TEXT DEFAULT '';

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER THEN

        SELECT customer_id INTO $customer_id FROM view_payments_credit_cards WHERE USER=$USER;
	  
        REPLACE INTO payments_remove_cards
            (  USER,  customer_id )
        VALUES
            ( $USER, $customer_id );

        DELETE FROM payments_credit_cards WHERE USER=$USER;

    ELSE

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INVALID_AUTHORISATION';

    END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Retrieve_By_User
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

    IF $USER = @USER THEN

        SELECT * FROM payments_credit_cards WHERE USER=$USER;

    ELSE

        SELECT token FROM payments_credit_cards WHERE USER=$USER;

    END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Retrieve_Unsynced
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    IF Is_Local_Caller() THEN

        SELECT * FROM view_payments_credit_cards_unsynced;

	ELSE

        SELECT "You must call this method from local host" AS msg;

    END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Credit_Cards_Synced
(
  $USER                           INT(11),
  $token                         CHAR(16)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

    UPDATE payments_credit_cards
    SET number='', cvv='', month='', year='', token=$token, processed=NOW()
    WHERE USER=$USER;

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Insert
(
  $sid                             CHAR(64),
  $USER                             INT(11),
  $description                     CHAR(99),
  $cost                         DECIMAL(13,2)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF "" != @USER THEN

	  INSERT INTO payments_purchases
		(  USER,  purchased,  description,  cost )
	  VALUES
		( $USER,      NOW(), $description, $cost );

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Retrieve_Unprocessed
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Caller() THEN

	  SELECT *
	  FROM      payments_purchases
	  LEFT JOIN view_payments_credit_cards USING (USER)
	  WHERE NOT processed=0 AND transacted=0;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Purchases_Transacted
(
  $PURCHASE               INT(11),
  $transaction_id        CHAR(16)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	IF Is_Local_Caller() THEN

	  UPDATE payments_purchases
	  SET transaction_id=$transaction_id, transacted=NOW()
	  WHERE PURCHASE=$PURCHASE;

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Plans_Retrieve_Unsubscribed
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET @USER = USER();

	IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

	  SELECT view_payments_credit_cards.*, S2.*
	  FROM view_payments_credit_cards
	  LEFT JOIN
	  (
		SELECT * FROM
		(
		  SELECT * FROM payments_plans ORDER BY PLAN DESC
		) AS S1
		GROUP BY USER
	  ) AS S2
	  USING (USER)
	  WHERE NOT processed=0 AND subscribed=0;

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Replace
(
  $USER                             INT(11),
  $transaction_id                  CHAR(16),
  $description                     CHAR(99)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET @USER = USER();

	IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

	  REPLACE INTO payments_transactions
		(  USER,  transaction_id,  description )
	  VALUES
		( $USER, $transaction_id, $description );

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_Unfinished
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET @USER = USER();

	IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

	  SELECT * FROM payments_transactions WHERE date IS NULL;

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_Submitted
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET @USER = USER();

	IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

	  SELECT * FROM payments_transactions WHERE status='submitted_for_settlement';

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Update_Details
(
  $TRANSACTION                      INT(11),
  $date                        DATETIME,
  $type                            CHAR(50),
  $status                          CHAR(50),
  $payment_method_token            CHAR(16),
  $amount                       DECIMAL(13,2)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SET @USER = USER();

	IF 'public@localhost' = @USER OR 'root@localhost' = @USER THEN

	  UPDATE payments_transactions
	  SET
						date=$date,
						type=$type,
					  status=$status,
		payment_method_token=$payment_method_token,
					  amount=$amount
	  WHERE
		TRANSACTION=$TRANSACTION;

	ELSE

	  SELECT "You must call this method from local host" AS msg;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Transactions_Retrieve_By_User
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

	SELECT * FROM payments_transactions WHERE USER=$USER;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Remove_Cards_Retrieve
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT * FROM payments_remove_cards;

END IF;

END
;
CREATE PROCEDURE Payments_Remove_Cards_Removed
(
$USER                           INT(11),
$customer_id                   CHAR(16)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	DELETE FROM payments_remove_cards WHERE USER=$USER AND customer_id=$customer_id;

END IF;

END
;
CREATE PROCEDURE Payments_Details_Replace
(
  $sid                          CHAR(64),
  $USER                          INT(11),
  $given_name                   CHAR(99),
  $family_name                  CHAR(99),
  $address                      CHAR(99),
  $address2                     CHAR(99),
  $suburb                       CHAR(99),
  $state                        CHAR(99),
  $country                      CHAR(99),
  $postcode                     CHAR(5)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

	  REPLACE INTO payments_details
		(  USER,  given_name,  family_name,  address,  address2,  suburb,  state,  country,  postcode )
	  VALUES
		( $USER, $given_name, $family_name, $address, $address2, $suburb, $state, $country, $postcode );

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Details_Retrieve
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN

	  SELECT * FROM payments_details WHERE USER=$USER;

	END IF;

END IF;

END
;
CREATE PROCEDURE Payments_Invoices_Replace
(
  $INVOICE                       INT(11),
  $USER                          INT(11),
  $currency                     CHAR(16),
  $amount                    DECIMAL(13,2),
  $gst                       DECIMAL(13,2),
  $total                     DECIMAL(13,2),
  $paid                      DECIMAL(13,2),
  $transacted                DATETIME
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	REPLACE INTO payments_invoices
	(  INVOICE,  USER,  raised,  currency,  amount,  gst,  total,  paid,  transacted )
	VALUES
	( $INVOICE, $USER,   NOW(), $currency, $amount, $gst, $total, $paid, $transacted );

END IF;

END
;
CREATE PROCEDURE Payments_Invoices_Retrieve
()
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	SELECT * FROM payments_invoices WHERE $total > $paid;

END IF;

END
;
CREATE PROCEDURE Payments_Invoices_Retrieve_By_User
(
  $sid                             CHAR(64),
  $USER                             INT(11)
)
BEGIN

IF @@read_only THEN

    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'READ_ONLY';

ELSE

	CALL Users_Authorise_Sessionid( $Sid, @email, @USER, @idtype );

	IF $USER = @USER THEN
	  SELECT * FROM payments_invoices WHERE USER=$USER;
	END IF;

END IF;

END
;
