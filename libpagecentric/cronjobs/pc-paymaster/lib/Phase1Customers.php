<?php

class Phase1Customers
{
	function perform( $out, $debug )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Customers_Uncreated", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->addCustomer( $tuple, $out, $debug );
			}
		}
	}

	function addCustomer( $tuple, $out, $debug )
	{
		$now = date( "Y-m-d H:i:s", time() );

		$USER   = array_get( $tuple, "USER" );
		$result = Braintree_Customer::create();

		$out->println( $now . " ???" );

		if ( $result->success )
		{
			$customer_id = $result->customer->id;
		
			$sql = "Payments_Customers_Replace( '$USER', '$customer_id' )";

			if ( is_array( DBi_callProcedure( DB, $sql, $debug ) ) )
			{
				$out->println( "$now, 'add customer', $customer_id, ok" );
			}
			else
			{
				$out->println( "$now, 'add customer', $customer_id, error - could not store in DB" );
			}
		}
		else
		{
			$out->println( "$now, 'add customer', error - could not add Braintree customer" );
		}
	}
}

?>