<?php
//	Copyright (c) 2016 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

namespace replicantdb;

class Log
{
	static function Retrieve( $sid, $request, $debug )
	{
		$filter = array_get( $request, "filter" );
		$order  = array_get( $request, "order"  );
		$limit  = array_get( $request, "limit"  );
		$offset = array_get( $request, "offset" );
	
		$sql = "ReplicantDB_Log_Retrieve( '$sid', '$filter', '$order', '$limit', '$offset' )";

		return \replicantdb\ReplicantDB::CallUnloggedProcedure( DB, $sql, $debug );
	}
}