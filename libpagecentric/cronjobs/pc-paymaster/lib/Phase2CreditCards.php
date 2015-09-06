<?php

class Phase2CreditCards
{
	function perform( $out, $debug )
	{
		$unsynced = force_array( DBi_callProcedure( DB, "Payments_Credit_Cards_Retrieve_Unsynced", $debug ) );

		if ( 0 < count( $unsynced ) )
		{
			foreach ( $unsynced as $tuple )
			{
				$this->syncCreditCard( $tuple, $out, $debug );
			}
		}
	}

	function syncCreditCard( $tuple, $out, $debug )
	{
		$USER        = array_get( $tuple, "USER"        );

		$customer_id = array_get( $tuple, "customer_id" );
		$number      = array_get( $tuple, "number"      );
		$cvv         = array_get( $tuple, "cvv"         );
		$month       = array_get( $tuple, "month"       );
		$year        = array_get( $tuple, "year"        );

		$customer    = Braintree_Customer::find( $customer_id );
		if ( $customer )
		{
			$len = count( $customer->creditCards );

			for ( $i=0; $i < $len; $i++ )
			{
				$token  = $customer->creditCards[$i]->token;
				$result = Braintree_CreditCard::delete( $token );
				if ( $result->success )
				{
					$out->println( NOW . ", 'remove card', '$token', ok" );
				}
				else
				{
					$out->println( NOW . ", 'remove card',  error - Could not find customer." );
				}
			}
		}
		else
		{
			$out->println( NOW . ", error - Could not find customer." );
		}
		
		$card = array
		(
			"customerId"      => $customer_id,
			"number"          => $number,
			"expirationMonth" => $month,
			"expirationYear"  => $year,
			"cvv"             => $cvv
		);
		
		$result = Braintree_CreditCard::create( $card );
		
		if ( $result->success )
		{
			$token = $result->creditCard->token;
		
			DBi_callProcedure( DB, "Payments_Credit_Cards_Synced( '$USER', '$token' )", $debug );
			$out->println( NOW . ", 'sync card', '$token', ok" );
		}
		else
		{
			$out->println( NOW . ", 'sync card', error - Could not sync card" );
			$out->println( NOW . ", 'sync card', number | $number" );
			$out->println( NOW . ", 'sync card',    cvv | $cvv"    );
			$out->println( NOW . ", 'sync card',  month | $month"  );
			$out->println( NOW . ", 'sync card',   year | $year"   );

			foreach( $result->errors->deepAll() AS $error )
			{
				$out->println( $error->code . ": " . $error->message );
			}
		}
	}
}

?>