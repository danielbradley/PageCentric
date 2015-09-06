<?php

class Phase3Subscriptions
{
	function perform( $out, $debug )
	{
		$tuples = force_array( DBi_callProcedure( DB, "Payments_Plans_Retrieve_Unsubscribed", $debug ) );

		if ( 0 < count( $tuples ) )
		{
			foreach ( $tuples as $tuple )
			{
				$this->subscribeBraintreeCustomer( $tuple, $out, $debug );
			}
		}
	}

	function subscribeBraintreeCustomer( $tuple, $out, $debug )
	{
		$USER            = array_get( $tuple, "USER"            );
		$PLAN            = array_get( $tuple, "PLAN"            );
		$plan_id         = array_get( $tuple, "plan_id"         );
		$customer_id     = array_get( $tuple, "customer_id"     );
		$subscription_id = array_get( $tuple, "subscription_id" );
		$token           = array_get( $tuple, "token"           );
		$cost            = array_get( $tuple, "cost"            );

		if ( $customer_id )
		{
			$subscription = array
			(
				"paymentMethodToken" => $token,
				"planId"             => $plan_id,
				"price"              => $cost
			);

			$action = "";
			$result = null;
			if ( "" == $subscription_id )
			{
				$action = "create subscription";
				$result = Braintree_Subscription::create( $subscription );
			}
			else
			{
				$subscription["options"] = array( "prorateCharges" => true );

				$action = "update subscription";
				$result = Braintree_Subscription::update( $subscription_id, $subscription );
			}

			if ( $result->success )
			{
				$id = $result->subscription->id;
				DBi_callProcedure( DB, "Payments_Plans_Update_Subscription_Id( '$PLAN', '$id' )", $debug );

				$out->println( NOW . ", '$action', $id, ok" );

				$transaction = array_get( $result->subscription->transactions, 0 );
				if ( $transaction )
				{
					$tid = $transaction->id;
				
					$sql = "Payments_Transactions_Replace( '$USER', '$tid', '$plan_id subscription ($$cost)' )";
					DBi_callProcedure( DB, $sql, $debug );
				}
			}
			else
			{
				$out->println( NOW . ", '$action', error - Could not update subscription $subscription_id" );

				foreach( $result->errors->deepAll() AS $error )
				{
					$out->println( $error->code . ": " . $error->message );
				}
			}
		}
		else
		{
			$out->println( NOW . ", 'subscriptions', error no customer id" );
		}
    }
}

?>