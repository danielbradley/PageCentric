<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Visits extends Model
{
	static function Replace( $ip_address, $debug )
	{
		$sql = "Statistics_Visits_Replace( '$ip_address' )";
		
		return DBi_callProcedure( DB, $sql, $debug );
	}

	static function Exists( $ip_address, $debug )
	{
		$sql = "Statistics_Visits_Exists( '$ip_address' )";
		
		return DBi_callFunction( DB, $sql, $debug );
	}
}