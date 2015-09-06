<?php

namespace pagecentric\users\models;

class Sessions
{
	function Retrieve( $sid, $request, $debug )
	{
		$USER      = array_get( $request, "USER"      );
		$user_hash = array_get( $request, "user_hash" );
		$order     = array_get( $request, "order"     );
		$limit     = array_get( $request, "limit"     );
		$offset    = array_get( $request, "offset"    );

		$sql = "Users_Sessions_Retrieve( '$sid', '$USER', '$user_hash', '$order', '$limit', '$offset' )";

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Current( $sid, $request, $debug )
	{
		$sql = "Users_Sessions_Retrieve_Current( '$sid' )";

		error_log( $sql );

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
	
	function Replace( $sid, $request, $debug )
	{
		$email    = array_get( $request, "email"    );
		$password = array_get( $request, "password" );
	
		$sql = "Users_Sessions_Replace( '$email', '$password' )";

		$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );

		if ( "OK" == $result->status )
		{
			if ( FALSE !== $result->results )
			{
				if ( 1 == count( $result->results ) )
				{
					$sessionid = $result->results[0]->sessionid;

					error_log( $sql . " ($sessionid)" );
		
					$cookie = "Set-Cookie: sid=";
					$cookie = $cookie . $sessionid;
					$cookie = $cookie . "; path=/; HttpOnly";
				
					header( $cookie );
				}
			}
		}

		return $result;
	}

	function Terminate( $sid, $request, $debug )
	{
		$sql = "Users_Sessions_Terminate( '$sid' )";

		error_log( $sql );

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	function Verify( $sid, $request, $debug )
	{
		$sql = "Users_Sessions_Verify( '$sid' )";

		error_log( $sql );

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
}


