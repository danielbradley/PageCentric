<?php

class Articles
{
	static function Retrieve( $sid, $request, $debug )
	{
		$ARTICLE = array_get( $request, "ARTICLE"  );
		$filter  = array_get( $request, "filter"   );
		$order   = array_get( $request, "order"    );
		$limit   = array_get( $request, "limit"    );
		$offset  = array_get( $request, "offset"   );

		$category   = array_get( $request, "category" );

		$filter .= "category=$category";

		$sql = "Articles_Retrieve( '$ARTICLE', '$filter', '$order', '$limit', '$offset' )";

		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	static function RetrieveSubset( $sid, $request, $debug )
	{
		$ARTICLE  = array_get( $request, "ARTICLE"    );
		$source   = array_get( $request, "source"     );
		$category = array_get( $request, "category"   );
		$subject  = array_get( $request, "subject"    );
		$session  = array_get( $request, "session_nr" );
		$filter   = array_get( $request, "filter"     );
		$order    = array_get( $request, "order"      );
		$limit    = array_get( $request, "limit"      );
		$offset   = array_get( $request, "offset"     );

		$sql = "Articles_Retrieve_Subset( '$ARTICLE', '$source', '$category', '$subject', '$session', '$filter', '$order', '$limit', '$offset' )";

		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	static function RetrieveSubjects( $sid, $request, $debug )
	{
		$source = array_get( $request, "source" );

		$sql = "Articles_Retrieve_Subjects( '$source' )";

		error_log( $sql );
		
		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}

	static function RetrieveInfo( $sid, $request, $debug )
	{
		$hashed_id = array_get( $request, "hashed_id" );
		$filter    = array_get( $request, "filter"    );
		$order     = array_get( $request, "order"     );
		$limit     = array_get( $request, "limit"     );
		$offset    = array_get( $request, "offset"    );


		$sql = "Articles_Info_Retrieve( '$hashed_id', '$filter', '$order', '$limit', '$offset' )";

		return \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
	}
}