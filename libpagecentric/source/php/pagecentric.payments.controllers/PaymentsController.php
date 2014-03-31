<?php

class PaymentsController extends Controller
{
	var $cls = "PaymentsController";

	function perform( $sid, $request, $debug )
	{
		$ret = null;
		
		$debug->inprint( "<!-- $this->cls::perform() start -->" );
		{
			if ( array_key_exists( "action", $request ) )
			{
				$msg = "<!-- performing: " . $request["action"] . " -->";
				$debug->println( $msg );
				
				switch ( $request["action"] )
				{
				case "payments_customers_replace":
					$ret = $this->replaceCustomer( $sid, $request, $debug );
					break;

				case "payments_customers_delete":
					$ret = $this->deleteCustomer( $sid, $request, $debug );
					break;

				case "payments_customers_retrieve_by_user":
					$ret = $this->retrieveCustomer( $sid, $request, $debug );
					break;

				case "payments_credit_cards_replace":
					$ret = $this->replaceCreditCard( $sid, $request, $debug );
					break;

				case "payments_credit_cards_delete":
					$ret = $this->deleteCreditCard( $sid, $request, $debug );
					break;

				case "payments_plans_replace":
					$ret = $this->replacePaymentPlan( $sid, $request, $debug );
					break;

				case "payments_details_replace":
					$ret = $this->replacePaymentDetails( $sid, $request, $debug );
					break;
				}
			}
		}
		$debug->outprint( "<!-- $this->cls::perform() end -->" );
		
		return $ret;
	}

	function replaceCustomer( $sid, $request, $debug )
	{
		$ret  = False;
		$USER = array_get( $request, "USER" );

		$sql = "Payments_Customers_Replace( '$sid', '$USER' )";
		$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}

	function deleteCustomer( $sid, $request, $debug )
	{
		$ret  = False;
		$USER = array_get( $request, "USER" );

		$sql = "Payments_Customers_Delete( '$sid', '$USER' )";
		$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}

	function retrieveCustomer( $sid, $request, $debug )
	{
		$ret  = False;
		$USER = array_get( $request, "USER" );

		$sql = "Payments_Customers_Retrieve_By_User( '$sid', '$USER' )";
		$ret = first( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}


	function replaceCreditCard( $sid, $request, $debug )
	{
		$ret = False;

		$USER       = array_get( $request, "USER"       );
		$final_four = array_get( $request, "final_four" );
		$number     = array_get( $request, "number"     );
		$cvv        = array_get( $request, "cvv"        );
		$month      = array_get( $request, "month"      );
		$year       = array_get( $request, "year"       );

		$sql = "Payments_Credit_Cards_Replace( '$sid', '$USER', '$final_four', '$number', '$cvv', '$month', '$year' )";
		$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}

	function deleteCreditCard( $sid, $request, $debug )
	{
		$ret = False;

		$submit = array_get( $request, "submit" );
		$USER   = array_get( $request, "USER"   );

		if ( "Yes" == $submit )
		{
			$sql = "Payments_Credit_Cards_Delete( '$sid', '$USER' )";
			$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		}
		return $ret;
	}

	function replacePaymentPlan( $sid, $request, $debug )
	{
		$ret = False;

		$USER       = array_get( $request, "USER"    );
		$plan_id    = array_get( $request, "plan_id" );
		$amount     = array_get( $request, "amount"  );
			
		$sql = "Payments_Plans_Replace( '$sid', '$USER', '$plan_id', '$amount' )";
		$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}

	function replacePaymentDetails( $sid, $request, $debug )
	{
		$ret = False;

		$USER        = array_get( $request, "USER"        );
		$given_name  = array_get( $request, "given_name"  );
		$family_name = array_get( $request, "family_name" );
		$address     = array_get( $request, "address"     );
		$address2    = array_get( $request, "address2"    );
		$suburb      = array_get( $request, "suburb"      );
		$state       = array_get( $request, "state"       );
		$country     = array_get( $request, "country"     );
		$postcode    = array_get( $request, "postcode"    );
			
		$sql = "Payments_Details_Replace( '$sid', '$USER', '$given_name', '$family_name', '$address', '$address2', '$suburb', '$state', '$country', '$postcode' )";
		$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		
		return $ret;
	}
	
	static function getRequired( $name )
	{
		$required = array();
	
		switch ( $name )
		{
		case "payments_credit_cards_replace":
			$required["number"] = "";
			$required["cvv"]    = "";
			$required["month"]  = "";
			$required["year"]   = "";
			break;
		}
		
		return $required;
	}

	static function retrievePayment( $sid, $USER, $debug )
	{
		$sql = "Payments_Retrieve( '$sid', '$USER' )";
		return first( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function retrievePaymentPlan( $sid, $USER, $debug )
	{
		$sql = "Payments_Plans_Retrieve( '$sid', '$USER' )";
		return first( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function retrieveTransactionsByUser( $sid, $USER, $debug )
	{
		$sql = "Payments_Transactions_Retrieve_By_User( '$sid', '$USER' )";
		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function retrievePaymentDetails( $sid, $USER, $debug )
	{
		$sql = "Payments_Details_Retrieve( '$sid', '$USER' )";
		return first( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function retrieveInvoices( $sid, $USER, $debug )
	{
		$sql = "Payments_Invoices_Retrieve_By_User( '$sid', '$USER' )";
		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function retrieveCreditCards( $sid, $USER, $debug )
	{
		$sql = "Payments_Credit_Cards_Retrieve_By_User( '$sid', '$USER' )";
		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function hasCreditCard( $sid, $USER, $debug )
	{
		$tuple = first( PaymentsController::retrieveCreditCards( $sid, $USER, $debug ) );
		
		return is_array( $tuple ) ? ("" != array_get( $tuple, "final_four" )) : false;
	}
}

?>