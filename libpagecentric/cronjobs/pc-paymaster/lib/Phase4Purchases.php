<?php

class Phase4Purchases
{
	function perform( $out, $debug )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Purchases_Retrieve_Unprocessed", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->processPurchase( $tuple, $out, $debug );
			}
		}
	}

	function processPurchase( $tuple, $out, $debug )
	{
		$PURCHASE        = array_get( $tuple, "PURCHASE"        );
		$USER            = array_get( $tuple, "USER"            );
		$purchased       = array_get( $tuple, "purchased"       );
		$description     = array_get( $tuple, "description"     );
		$cost            = array_get( $tuple, "cost"            );
		$customer_id     = array_get( $tuple, "customer_id"     );
		$token           = array_get( $tuple, "token"           );

//		$out->println( NOW . ", |_          PURCHASE: $PURCHASE"             );
//		$out->println( NOW . ", |_              USER: $USER"                 );
//		$out->println( NOW . ", |_       customer_id: $customer_id"          );
//		$out->println( NOW . ", |_         purchased: $purchased"            );
//		$out->println( NOW . ", |_       description: $description"          );
//		$out->println( NOW . ", |_              cost: $cost"                 );
//		$out->println( NOW . ", |_             token: $token"                );

		if ( $customer_id )
		{
			$transaction = array
			(
				"amount"             => "$cost",
				"customerId"         => "$customer_id",
				"paymentMethodToken" => "$token"
			);

			$result = Braintree_Transaction::sale( $transaction );

			if ( $result->success )
			{
				$id = $result->transaction->id;
				if ( is_array( DBi_callProcedure( DB, "Payments_Purchases_Transacted( '$PURCHASE', '$id' )", $debug ) ) )
				{
					$sql = "Payments_Transactions_Replace( '$USER', '$id', '$description ($$cost)' )";
					DBi_callProcedure( DB, $sql, $debug );

					$out->println( NOW . ", 'transacted purchase', $id, ok" );
				}
				else
				{
					$out->println( NOW . ", 'transacted purchase', error - Could not update purchase" );
				}
			}
			else
			{
				$out->println( NOW . ", 'transacted purchase', error - Could not create transaction" );

				foreach( $result->errors->deepAll() AS $error )
				{
					$out->println( $error->code . ": " . $error->message );
				}
			}
		}
		else
		{
			$out->println( NOW . ", 'purchases', error no customer id" );
		}
    }
}

?>