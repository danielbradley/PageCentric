<?php

class Phase7Remover
{
	function perform( $out, $debug )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Remove_Cards_Retrieve", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->deleteCards( $tuple, $out, $debug );
			}
		}
	}

	function deleteCard( $tuple, $out, $debug )
	{
		$now = date( "Y-m-d H:i:s", time() );

		$USER        = array_get( $tuple, "USER" );
		$customer_id = array_get( $tuple, "customer_id" );
		$token       = array_get( $tuple, "token" );

		$result = Braintree_CreditCard::delete( $token );

		if ( $result->success )
		{
			if ( is_array( DBi_callProcedure( DB, "Payments_Remove_Cards_Removed( '$USER', '$customer_id', '$token' )", $debug ) ) )
			{
				$out->println( "$now, 'remove card', $customer_id, $token, ok" );
			}
			else
			{
				$out->println( "$now, 'remove card', $customer_id, $token, error (could not remove)" );
			}
		}
		else
		{
			$out->println( "$now, 'remove card', $customer_id, error" );
		}
	}

	function deleteCards( $tuple, $out, $debug )
	{
		$now = date( "Y-m-d H:i:s", time() );

		$USER        = array_get( $tuple, "USER" );
		$customer_id = array_get( $tuple, "customer_id" );

		$customer    = Braintree_Customer::find( $customer_id );
		$len         = count( $customer->creditCards );

		$protected   = retrieveAddedCreditCardToken( $USER );

		$success = true;

		if ( 0 < $len )
		{
			for ( $i=0; $i < $len; $i++ )
			{
				$token = $customer->creditCards[$i]->token;
				if ( $token != $protected )
				{
					$result = Braintree_CreditCard::delete( $token );
					$success &= $result->success;
				}
			}
		}

		if ( $success )
		{
			if ( is_array( DBi_callProcedure( DB, "Payments_Remove_Cards_Removed( '$USER', '$customer_id' )", $debug ) ) )
			{
				$out->println( "$now, 'remove card', $customer_id, ok" );
			}
			else
			{
				$out->println( "$now, 'remove card', $customer_id, error (could not remove)" );
			}
		}
		else
		{
			$out->println( "$now, 'remove card', $customer_id, error" );
		}
	}

	static function retrieveAddedCreditCardToken( $USER )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Credit_Cards_Retrieve_By_User( '', '$USER' )" ) );
		
		$token = "";
		if ( 0 < count( $tuples ) )
		{
			$token = array_get( $tuples[0], $token );
		}
		
		return $token;
	}
}

?>