<?php

class Selects extends \Model
{
	static function RetrieveMulti( $sid, $request, $debug )
	{
		$ret            = array();
		$ret["results"] = array();
		$ret["status"]  = "OK";

		$kinds  = array_get( $request, "kinds"  );
		$filter = array_get( $request, "filter" );

		if ( $kinds )
		{
			error_log( $kinds );
		
			$bits = explode( ",", $kinds );

			foreach( $bits as $kind )
			{
				$id = "";
				$k  = "";
			
				if ( string_contains( $kind, ":" ) )
				{
					$bits2 = explode( ":", trim( $kind ) );

					$id = $bits2[0] . ":";
					$k  = $bits2[1];
				}
				else
				{
					$k  = trim( $kind );
				}

				$v = Selects::RetrieveOptionsFor( $sid, $k, $filter, $debug );

				//if ( 0 < count( $v ) )
				{
					$list = array();
					$list["name"]   = $id . $k;
					$list["tuples"] = $v;
					
					$ret["results"][] = (object) $list;
				}
			}
		}
		http_response_code( 200 );

		return (object) $ret;
	}

	static function RetrieveOptionsFor( $sid, $kind, $filter, $debug )
	{
		$options = array();
		$id      = "";
		$value   = "";
		$sql     = "";

		$kind = ucwords( $kind );
		$sql  = "Selects_$kind( '$sid', '$id', '$value', '$filter' )";

		if ( $sql )
		{
			//\Logs::Append( "DEBUG", "Selects::Retrieve", $sql, $debug );

			error_log( $sql );

			$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
			if ( "OK" == $result->status )
			{
				if ( FALSE === $result->results )
				{
					error_log( "CallProcedure returned FALSE" );
				}
				else
				{
					$options = $result->results;
				}
			}
		}
		return $options;
	}
}
