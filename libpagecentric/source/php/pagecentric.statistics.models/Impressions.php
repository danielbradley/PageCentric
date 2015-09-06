<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Impressions extends Model
{
	static function Replace( $ip_address, $session, $debug )
	{
		$sql = "Statistics_Impressions_Replace( '$ip_address', '$session' )";
		
		return DBi_callProcedure( DB, $sql, $debug );
	}
}