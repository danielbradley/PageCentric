<?php

class AdminUsers extends Model
{
	static function retrieveUsers( $sid, $debug )
	{
		$sql = "Admin_Users_Retrieve( '$sid' )";
		
		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}
}

?>