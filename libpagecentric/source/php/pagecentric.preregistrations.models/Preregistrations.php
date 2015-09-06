<?php

class Preregistrations
{
	function Retrieve( $sid, $request, $debug )
	{
		$sql = "Preregistrations_Retrieve( '$sid' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Replace( $sid, $request, $debug )
	{
		$name  = array_get( $request, "name"  );
		$email = array_get( $request, "email" );
		$info  = array_get( $request, "info"  );

		$sql = "Preregistrations_Replace( '$name', '$email', '$info' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Unsent( $sid, $request, $debug )
	{
		$sql = "Preregistrations_Unsent( '$sid' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Sent( $sid, $request, $debug )
	{
		$TID = array_get( $request, "TID" );
	
		$sql = "Preregistrations_Sent( '$sid', '$TID' )";
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
}
