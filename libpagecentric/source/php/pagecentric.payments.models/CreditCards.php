<?php

namespace pagecentric\payments\models;

class CreditCards
{
	static function Replace( $sid, $request, $debug )
	{
		$USER       = array_get( $request, "USER"       );
		$final_four = array_get( $request, "final_four" );
		$month      = array_get( $request, "month"      );
		$year       = array_get( $request, "year"       );
		$nonce      = array_get( $request, "nonce"      );
		
		$sql = "Payments_Credit_Cards_Replace( '$sid', '$USER', '$final_four', '$month', '$year', '$nonce' )";

		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
	
	static function Delete( $sid, $request, $debug )
	{
		$USER = array_get( $request, "USER" );
		
		$sql = "Payments_Customers_Delete( '$sid', '$USER' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
	
	static function Retrieve( $sid, $request, $debug )
	{
		$USER = array_get( $request, "USER" );
		
		$sql = "Payments_Customers_Retrieve_By_User( '$sid', '$USER' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
}
