<?php

class Invoicer
{
	function sync( $out, $debug )
	{
		$unsynced = force_array( DBi_callProcedure( DB, "Payments_Plans_Retrieve_Today", $debug ) );

		if ( 0 < count( $unsynced ) )
		{
			foreach ( $unsynced as $tuple )
			{
				$this->invoiceCustomer( $tuple, $out, $debug );
			}
		}
	}

	function invoiceCustomer( $tuple, $out, $debug )
	{
		$now      = date( "Y-m-d H:i:s", time() );

		$USER     = array_get( $tuple, "USER"     );
		$switched = array_get( $tuple, "switched" );
		$plan_id  = array_get( $tuple, "plan_id"  );
		$cost     = array_get( $tuple, "cost"     );
		$INVOICE  = array_get( $tuple, "INVOICE"  );
		
		if ( "" == $INVOICE )
		{
			$sql = "Payments_Invoices_Replace( '', '$USER', 'AUD', '$cost', '0.00', '$cost', '0.00', '' )";
			if ( is_array( DBi_callProcedure( DB, $sql, $debug ) ) )
			{
				$out->println( "$now, 'invoiced', $USER, ok - $sql" );
			}
			else
			{
				$out->println( "$now, 'invoiced', $USER, error" );
			}
		}
    }
}

?>