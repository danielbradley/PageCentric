<?php

class Logs
{
	static function Prime( $sid, $source, $message, $debug )
	{
		$sql = "Logs_Prime( '$sid', '$source', '$message' )";
		
		$result  = first( DBi_callProcedure( DB, $sql, $debug ) );
		$CALL_ID = array_get( $result, "CALL_ID" );

		//error_log( $sql . " $CALL_ID" );
		
		return $CALL_ID;
	}

	static function Append( $level, $source, $message, $debug )
	{
		$CALL_ID = defined( "LOGS_CALL_ID" ) ? LOGS_CALL_ID : 0;
	
		$level   = Input::Filter( $level   );
		$source  = Input::Filter( $source  );
		$message = Input::Filter( $message );
	
		$sql = "Logs_Append( '$CALL_ID', '$level', '$source', '$message' )";
		
		//error_log( $sql );
		
		DBi_callProcedure( DB, $sql, $debug );
	}

	static function Retrieve( $sid, $request, $debug )
	{
		$after = array_get( $request, "after" );

		$sql = "Logs_Retrieve( '$sid', '$after' )";
		
		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}
}
