<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

function DBi_callFunction( $database, $function, $debug )
{
	error_log( $function );

	$debug->println( "<!-- DBi_callFunction( $database, $function ) start -->" );
	$debug->indent();
	{
		$ret = False;
		$db = DBi_anon();

		if ( $db->connect( $debug ) )
		{
			$sql = "SELECT $function";
			
			$ret = $db->callFunction( $database, $sql, $debug );
			
			$db->close( $debug );
		}
	}
	$debug->outdent();
	$debug->println( "<!-- DBi_callFunction() end -->" );
	
	return $ret;
}

function DBi_callProcedure( $database, $procedure, $debug )
{
	$debug->println( "<!-- DBi_callProcedure( $database, $procedure ) start -->" );
	$debug->indent();
	{
		$ret = False;
		$db = DBi_anon();
		
		if ( $db->connect( $debug ) )
		{
			$sql = "CALL $procedure";

			$ret = $db->callProcedure( $database, $sql, $debug );

			$db->close( $debug );
		}
	}
	$debug->outdent();
	$debug->println( "<!-- DBi_callProcedure() end -->" );
	return $ret;
}

function DBi_callProcedureStreamJSON( $database, $procedure, $out, $debug )
{
	$debug->println( "<!-- DBi_callProcedureStreamJSON( $database, $procedure ) start -->" );
	$debug->indent();
	{
		$ret = False;
		$db = DBi_anon();
		
		if ( $db->connect( $debug ) )
		{
			$sql = "CALL $procedure";

			$ret = $db->callProcedureStreamJSON( $database, $sql, $out, $debug );

			$db->close( $debug );
		}
	}
	$debug->outdent();
	$debug->println( "<!-- DBi_callProcedure() end -->" );
	return $ret;
}

function DBi_escape( $string )
{
	return $string;

	$db = DBi_anon();
	
	if ( $db->connect( new NullPrinter() ) )
	{
		return $db->escape( $string );
	}
}


function DBi_containsDatabase( $database, $debug )
{
	$fulldb = DB . DB_VERSION;

	$db = new DBi( "", HOSTNAME, "", False );
	if ( $db->canConnect( $debug ) )
	{
		return $db->containsDatabase( $fulldb, $debug );
	}
	else
	{
		return False;
	}
}

//function DBi_close( $debug )
//{
//	$db = DBi_anon();
//	
//	$db->close( $debug );
//}

//$DBi = null;

function DBi_anon()
{
	//if ( !$DBi )
	{
		$username = DB_USERNAME;
		$password = DB_PASSWORD;
		$hostname = DB_HOSTNAME;

		$DBi = new DBi( $username, $hostname, $password );
	}
	return $DBi;
}

class DBi
{
	var $mysqli;

	var $username;
	var $hostname;
	var $password;

	var $connection;
	
	function DBi( $username, $hostname, $password )
	{
		$this->username = $username;
		$this->hostname = $hostname;
		$this->password = $password;
	}

	function connect( $debug )
	{
		$user = $this->username;
		$host = $this->hostname;
		$pass = $this->password;

		$debug->println( "<!-- DBi::connect(): mysql://$user:$host:$pass/ -->" );

		$this->mysqli = mysqli_init();

		if ( $this->mysqli )
		{
			if ( file_exists( "/local/settings/services/mysql/ssl/client-key.pem" ) )
			{
				mysqli_ssl_set
				(
					$this->mysqli,
					"/local/settings/services/mysql/ssl/client-key.pem",
					"/local/settings/services/mysql/ssl/client-cert.pem",
					"/local/settings/services/mysql/ssl/ca-cert.pem",
					NULL,
					NULL
				);
			}
			$this->mysqli->real_connect( $host, $user, $pass );
		
			if ( NULL == $this->mysqli->connect_error )
			{
				return True;
			} else {
				$this->mysqli = NULL;
				return False;
			}
		}
		else
		{
			return False;
		}
	}

	function close( $debug )
	{
		$result = True;
	
		if ( $this->mysqli )
		{
			if ( ! ($result = mysqli_close( $this->mysqli )) )
			{
				$debug->println( "<!-- Could not close MySQLi -->" );
			}
		}
		else
		{
			$debug->println( "<!-- mysqli is null -->" );
		}
		
		return $result;
	}
	
	function info( $sql_query )
	{
		$tuples = array();
		$resource = mysqli_query( $this->mysqli, $sql_query );
		if ( $resource )
		{
			while ( $row = mysqli_fetch_array( $resource, MYSQL_ASSOC ) )
			{
				$tuples[] = $row;
			}
		} else {
			echo "<!-- " . $this->lastErrorMessage() . " -->";
		}
		return $tuples;
	}

	function escape( $string )
	{
		return mysqli_real_escape_string( $this->mysqli, $string );
	}

	function callFunction( $database, $sql_query, $debug )
	{
		$ret = null;

		$database = $database . DB_VERSION;
	
		$debug->println( "<!-- DBi::callFunction() start -->" );
		$debug->indent();
	
		if ( mysqli_select_db( $this->mysqli, $database ) )
		{
			$resource = mysqli_query( $this->mysqli, $sql_query );
			if ( True === $resource )
			{
				$debug->println( "<!-- returned True -->" );
				$ret = True;
			}
			else if ( False === $resource )
			{
				$debug->println( "<!-- returned False -->" );
				$ret = False;
			}
			else if ( $resource )
			{
				$nr_results = 0;
				$debug->println( "<!-- SQL: $sql_query -->" );

				while ( $row = mysqli_fetch_array( $resource, MYSQL_NUM ) )
				{
					$ret = $row[0];
				}

				$debug->println( "<!-- returned Resource: $ret -->" );

				mysqli_free_result( $resource );
			} else {
				$error = "<!-- Error: SQL: $sql_query - " . $this->lastErrorMessage() . " -->";
				$debug->println( $error );
			}
		} else {
			$debug->println( "<!-- Error: Could not select database: $database -->" );
		}

		$debug->outdent();
		$debug->println( "<!-- DBi::callFunction() end -->" );

		return $ret;
	}

	function callProcedure( $database, $sql_query, $debug )
	{
		$tuples = False;

		$database = $database . DB_VERSION;
	
		$debug->println( "<!-- DBi::callProcedure() start -->" );
		$debug->indent();
	
		if ( mysqli_select_db( $this->mysqli, $database ) )
		{
			$resource = mysqli_query( $this->mysqli, $sql_query );
			if ( True === $resource )
			{
				$debug->println( "<!-- returned True -->" );
				$tuples = array();
			}
			else if ( False === $resource )
			{
				$debug->println( "<!-- returned False -->" );
			}
			else if ( $resource )
			{
				$debug->println( "<!-- returned Resource -->" );
				$nr_results = 0;
				$debug->println( "<!-- SQL: $sql_query -->" );

				$tuples = array();
				while ( $row = mysqli_fetch_array( $resource, MYSQL_ASSOC ) )
				{
					$tuples[] = $row;
				}

				mysqli_free_result( $resource );
			} else {
				$error = "<!-- Error: SQL: $sql_query - " . $this->lastErrorMessage() . " -->";
				$debug->println( $error );
			}
		} else {
			$debug->println( "<!-- Error: Could not select database: $database -->" );
		}

		$debug->outdent();
		$debug->println( "<!-- DBi::callProcedure() end -->" );

		return $tuples;
	}

	function callProcedureStreamJSON( $database, $sql_query, $out, $debug )
	{
		$tuples = False;

		$database = $database . DB_VERSION;
	
		$debug->println( "<!-- DBi::callProcedure() start -->" );
		$debug->indent();
	
		if ( mysqli_select_db( $this->mysqli, $database ) )
		{
			$resource = mysqli_query( $this->mysqli, $sql_query );
			if ( True === $resource )
			{
				$debug->println( "<!-- returned True -->" );
				$tuples = array();
			}
			else if ( False === $resource )
			{
				$debug->println( "<!-- returned False -->" );
			}
			else if ( $resource )
			{
				$debug->println( "<!-- returned Resource -->" );
				$nr_results = 0;
				$debug->println( "<!-- SQL: $sql_query -->" );

				$out->println("{ \"results\" : [" );
				$sep = "";
				while ( $row = mysqli_fetch_array( $resource, MYSQL_ASSOC ) )
				{
					$out->println( $sep );
					$out->printf( \JSON::encodeObject( $row ) );
					$sep = ", ";
				}
				$out->println( " " );
				$out->println( "]}" );

				mysqli_free_result( $resource );
			} else {
				$error = "<!-- Error: SQL: $sql_query - " . $this->lastErrorMessage() . " -->";
				$debug->println( $error );
			}
		} else {
			$debug->println( "<!-- Error: Could not select database: $database -->" );
		}

		$debug->outdent();
		$debug->println( "<!-- DBi::callProcedure() end -->" );

		return $tuples;
	}

	function query( $database, $sql_query, $debug )
	{
		$database = $database . DB_VERSION;
	
		$debug->println( "<!-- DB::query() start -->" );
		$debug->indent();
	
		$tuples = array();
		if ( mysqli_select_db( $this->mysqli, $database ) )
		{
			$resource = mysqli_query( $this->mysqli, $sql_query );
			if ( $resource )
			{
				//$resources = mysqli_store_result( $this->mysqli );
			
				$nr_results = 0;
				$debug->println( "<!-- SQL: $sql_query -->" );
				while ( $row = mysqli_fetch_array( $resource, MYSQL_ASSOC ) )
				{
					$tuples[] = $row;
					$nr_results++;
				}
				$debug->println( "<!-- Returned: $nr_results -->" );

				mysqli_free_result( $resource );

			} else {
				$error = "<!-- Error: SQL: $sql_query - " . $this->lastErrorMessage() . " -->";
				$debug->println( $error );
			}
		} else {
			$debug->println( "<!-- Error: Could not select database: $database -->" );
		}


		$debug->outdent();
		$debug->println( "<!-- DB::query() end -->" );
		
		return $tuples;
	}

	function multiquery( $database, $sql_query, $debug )
	{
		$database = $database . DB_VERSION;
	
		$debug->println( "<!-- DB::query() start -->" );
		$debug->indent();
	
		$tuples = array();
		if ( mysqli_select_db( $this->mysqli, $database ) )
		{
			$resource = mysqli_multi_query( $this->mysqli, $sql_query );
			if ( $resource === False )
			{
				$error = "<!-- Error: SQL: $sql_query - " . $this->lastErrorMessage() . " -->";
				$debug->println( $error );
			}
			else if ( $resource === True )
			{
				$debug->println( "<!-- Access violoation -->" );
				$debug->println( "<!-- SQL: $sql_query -->" );
			}
			else
			{
				$nr_results = 0;
				$debug->println( "<!-- SQL: $sql_query -->" );
				while ( $row = mysqli_fetch_assoc( $resource ) )
				{
					$tuples[] = $row;
					$nr_results++;
				}
				$debug->println( "<!-- Returned: $nr_results -->" );
			}
		} else {
			$debug->println( "<!-- Error: Could not select database: $database -->" );
		}

		$debug->outdent();
		$debug->println( "<!-- DB::query() end -->" );
		
		return $tuples;
	}

	function change( $database, $sql_query, $debug )
	{
		$database = is_null( $database ) ? "" : $database;
	
		$success = False;

		$fulldatabase = $database . DB_VERSION;
	
		if ( "" == $database )
		{
			$debug->println( "<!-- no database specified -->" );
			$result = mysqli_query( $this->mysqli, $sql_query );
		}
		else if ( mysqli_select_db( $this->mysqli, $fulldatabase ) )
		{
			$debug->println( "<!-- selected database $fulldatabase -->" );
			$result = mysqli_query( $this->mysqli, $sql_query );
		}
		else
		{
			$debug->println( "<!-- could not select specified database $fulldatabase -->" );
		}
		
		if ( $result === True )
		{
			$debug->println( "<!-- SQL: $sql_query -->" );
			$success = True;
		}
		else if ( $result === False )
		{
			$error = "<!-- Error: $sql_query - " . $this->lastErrorMessage() . " -->";
			$debug->println( $error );
		}
		else if ( $result )
		{
			mysqli_free_result( $result );
		}
		return $success;
	}

	function multichange( $database, $sql_query, $debug )
	{
		$success = False;

		$fulldatabase = $database . DB_VERSION;
	
		if ( "" == "$database" )
		{
			$debug->println( "<!-- no database specified -->" );
			mysqli_multi_query( $this->mysqli, $sql_query );
			if ( $result = mysqli_store_result( $this->mysqli ) )
			{
				mysqli_result_free( $result );
				$debug->println( "<!-- one down -->" );
				$success = True;
			}
			
			while ( mysqli_more_results( $this->mysqli ) )
			{
				if ( $result = mysqli_next_result( $this->mysqli ) )
				{
					mysqli_free_result( $result );
					$debug->println( "<!-- and another: " . $success . " -->" );
					$success &= True;
				}
			}
		}
		else if ( mysqli_select_db( $this->mysqli, $fulldatabase ) )
		{
			$debug->println( "<!-- selected database $fulldatabase -->" );
			mysqli_multi_query( $this->mysqli, $sql_query );
			$result = mysqli_store_result( $this->mysqli );
			if ( 0 == mysqli_errno( $this->mysqli ) )
			{
				// ??? mysql_free_result( $result );
				$debug->println( "<!-- one down -->" );
				$success = True;
			}
			
			while ( mysqli_more_results( $this->mysqli ) )
			{
				$result = mysqli_next_result( $this->mysqli );
				
				if ( True === $result )
				{
					$success &= True;
				}
				else if ( False == $result )
				{
					$success = False;
				}
				else if ( $result )
				{
					mysqli_free_result( $result );
					$debug->println( "<!-- and another: " . $success . " -->" );
					$success &= True;
				}
			}
		}
		else
		{
			$debug->println( "<!-- could not select specified database $fulldatabase -->" );
		}
		
		if ( $success === False )
		{
			$error = "<!-- Error: $sql_query - " . $this->lastErrorMessage() . " -->";
			$debug->println( $error );
		}
		return $success;
	}

	function lastErrorMessage()
	{
		return mysqli_error( $this->mysqli );
	}

	function lastErrorValue()
	{
		return mysqli_errno( $this->mysqli );
	}
	
	function databaseContainsTable( $database, $table )
	{
		$sql = "SHOW TABLES";
		
		if ( $this->connect() && ($tuples = $this->query( $database, $sql )) )
		{
			foreach ( $tuples as $tuple )
			{
				$tmp = $tuple["Tables_in_$databse"];
				if ( "$table" == "$tmp" )
				{
					return True;
				}
			}
		} else {
			$this->error( $sql );
		}
		return False;
	}

	function canConnect( $debug )
	{
		if ( $this->connect( $debug ) )
		{
			return True;
		}
		else
		{
			return False;
		}
	}
	
	function containsDatabase( $database, $debug )
	{
		$sql = "SHOW DATABASES";
		if ( $this->connect( $debug ) && ($tuples = $this->info( $sql )) )
		{
			foreach ( $tuples as $tuple )
			{
				$tmp = $tuple["Database"];
				if ( "$database" == "$tmp" )
				{
					return True;
				}
			}
		} else {
			$this->error( $sql );
		}
		return False;
	}
	
	function error ( $sql, $debug )
	{
		$error = "<!-- Error: " . $this->lastErrorMessage() . " -->";
		$debug->println( "<!-- SQL: $sql -->" );
		$debug->println( $error );
	}
}

?>
