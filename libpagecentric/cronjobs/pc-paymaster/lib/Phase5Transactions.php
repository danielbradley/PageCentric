<?php

class Phase5Transactions
{
	function perform( $out, $debug )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Transactions_Retrieve_Unfinished", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->completeBraintreeTransaction( $tuple, $out, $debug );
			}
		}

		$tuples = force_array( DBi_callProcedure( DB, "Payments_Transactions_Retrieve_Submitted", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->completeBraintreeTransaction( $tuple, $out, $debug );
			}
		}
	}

	function completeBraintreeTransaction( $tuple, $out, $debug )
	{
		$TRANSACTION     = array_get( $tuple, "TRANSACTION"    );
		$USER            = array_get( $tuple, "USER"           );
		$transaction_id  = array_get( $tuple, "transaction_id" );
		$existing_status = array_get( $tuple, "status"         );

		if ( $transaction_id )
		{
			$transaction = Braintree_Transaction::find( $transaction_id );
			if ( $transaction )
			{
				$created_at           = $transaction->createdAt;
				$type                 = $transaction->type;
				$status               = $transaction->status;
				$payment_method_token = $transaction->creditCardDetails->token;
				$amount               = $transaction->amount;

				$timestamp = $created_at->getTimestamp();
				$date      = date( "Y-m-d H:i:s", $timestamp );
			
				$sql = "Payments_Transactions_Update_Details( '$TRANSACTION', '$date', '$type', '$status', '$payment_method_token', '$amount' )";
				DBi_callProcedure( DB, $sql, $debug );

				$out->println( NOW . ", 'updating transaction', $transaction_id, '$status', ok" );

//				$out->println( NOW . ", |_          TRANSACTION: $TRANSACTION"          );
//				$out->println( NOW . ", |_                 USER: $USER"                 );
//				$out->println( NOW . ", |_       transaction_id: $transaction_id"       );
//				$out->println( NOW . ", |_                 date: $date"                 );
//				$out->println( NOW . ", |_                 type: $type"                 );
//				$out->println( NOW . ", |_               status: $status"               );
//				$out->println( NOW . ", |_ payment_method_token: $payment_method_token" );
//				$out->println( NOW . ", |_               amount: $amount"               );
			}
			else
			{
				$out->println( NOW . ", 'complete transaction', error - Could not complete transaction $transaction_id" );

				foreach( $result->errors->deepAll() AS $error )
				{
					$out->println( $error->code . ": " . $error->message );
				}
			}
		}
    }
}

?>