<?php

namespace pagecentric\payments\models;

class Customers
{
	static function Replace( $sid, $request, $debug )
	{
		$USER        = array_get( $request, "USER"        );
		$customer_id = array_get( $request, "customer_id" );
		
		if ( "" == $customer_id )
		{
			$result = \Braintree_Customer::create();

			if ( $result->success )
			{
				$customer_id = $result->customer->id;
			}
		}
		
		$sql = "Payments_Customers_Replace( '$USER', '$customer_id' )";
		
		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
	
	static function Delete( $sid, $request, $debug )
	{
		$USER = array_get( $request, "USER" );
		
		$sql = "Payments_Customers_Delete( '$sid', '$USER' )";
		
		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
	
	static function Retrieve( $sid, $request, $debug )
	{
		$USER = array_get( $request, "USER" );
		
		$sql = "Payments_Customers_Retrieve_By_User( '$sid', '$USER' )";
		
		error_log( $sql );
		
		$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
		if ( "OK" == $result->status )
		{
			$parameters["customerId"] = $result->results[0]->customer_id;
			
			$result->results[0]->clientToken = \Braintree_ClientToken::generate( $parameters );
		}
		return $result;
	}
}
