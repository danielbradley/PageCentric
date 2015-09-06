<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class InitialisationController extends Controller
{
	function __construct()
	{}
	
	function perform( $session, $request, $debug )
	{
		$ret = null;
	
		$debug->println( "<!-- InstallController::perform() start -->" );
		$debug->indent();
		{
			if ( array_key_exists( "action", $request ) )
			{
				$msg = "<!-- performing: " . $request["action"] . " -->";
				$debug->println( $msg );
				
				switch ( $request["action"] )
				{
				case "initialise_db":
					$ret = $this->initialiseDB( $request, $debug );
					break;
				}
			}
		}
		$debug->outdent();
		$debug->println( "<!-- InstallController::perform() end -->" );

		return $ret;
	}

	function initialiseDB( $request, $debug )
	{
		$statuses = False;

		$debug->inprint( "<!-- InstallController::initialiseDB() start -->" );
		{
			if ( $this->createDatabase( $request, $debug ) )
			{
				$statuses = $this->installTables( $request, $debug );
			}
			else
			{
				$db = DB . DB_VERSION;
				$debug->println( "<!-- Could not create db: $db -->" );
			}
		}
		$debug->outprint( "<!-- InstallController::initialiseDB() end -->" );

		return $statuses;
	}

	function endsWith( $str, $sub )
	{
		return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
	}

	function installTables( $request, $debug )
	{
		$status   = array();
		$status[] = $this->perform_install_action( "CREATE DATABASE", True );
		$status[] = $this->perform_install_action( "GRANT EXECUTE TO PUBLIC", $this->grantExecuteToPublic( $request, $debug ) );

		$status = $this->installTablesFor( $status, "v", $request, $debug );
		$status = $this->installTablesFor( $status, "w", $request, $debug );

		return $status;
	}

	function installTablesFor( $status, $nspace, $request, $debug )
	{
		$i = 1;
		while ( true )
		{
			$key = $nspace . $i;
			$debug->println( "<!-- $key -->" );
			if ( array_key_exists( $key, $request ) )
			{
				$dir = $request[$key];
				if ( file_exists( $dir ) )
				{
					$status[] = $this->perform_install_action( $dir, $this->installTablesIn( $request, $debug, $dir ) );
				}
			} else {
				break;
			}
			$i++;
		}
		
		return $status;
	}
	
		function perform_install_action( $label, $result )
		{
			$checked = ("1" == $result) ? "checked='checked'" : "";

			$check_box = "<input type='checkbox' $checked disabled>";
			$row = "<tr><td style='font-size:10px;'>$check_box $label</td></tr>";
			return $row;
		}

	function createDatabase( $request, $debug )
	{
		$status = False;

		$debug->println( "<!-- InstallController::createDatabase start -->" );
		$debug->indent();
		{
			$dbadmin    = $request["dbadmin"];
			$dbpassword = $request["dbpassword"];
			
			if ( $dbadmin && $dbpassword )
			{
				$database_name = DB . DB_VERSION;
				
				$db = new DBi( $dbadmin, DB_HOSTNAME, $dbpassword );
				if ( $db->connect( $debug ) )
				{
					$debug->println( "<!-- Connected -->" );
					$sql = "CREATE DATABASE $database_name";
					if ( $db->change( "", $sql, $debug ) )
					{
						$status = True;
						$debug->println( "<!-- Created Database: $database_name -->" );
					} else {
						$debug->println( "<!-- Could not create database! -->" );
					}
				}
				else
				{
					$debug->println( "<!-- Invalid credentials for connection to db -->" );
				}
			}
			else
			{
				$debug->println( "<!-- No credentials for connection to db -->" );
			}
		}
		$debug->outdent();
		$debug->println( "<!-- InstallController::createDatabase end : $status -->" );
		
		return $status;
	}

	function grantExecuteToPublic( $request, $debug )
	{
		$status = True;

		$debug->println( "<!-- InstallController::grantExecuteToPublic start -->" );
		$debug->indent();
		{
			$dbadmin    = $request["dbadmin"];
			$dbpassword = $request["dbpassword"];

			$db_username = DB_USERNAME;
			$db_password = DB_PASSWORD;
			$db_hostname = DB_HOSTNAME;

			$database_name = DB . DB_VERSION;
			
			$db = new DBi( $dbadmin, DB_HOSTNAME, $dbpassword, False );
			if ( $db->connect( $debug ) )
			{
				$sql = "GRANT EXECUTE ON $database_name.* TO '$db_username'@'$db_hostname' IDENTIFIED BY '$db_password' require x509";
				$status = $db->change( DB, $sql, $debug );
				if ( $status )
				{
					$debug->println( "<!-- Added auth -->" );
				}
			}
			else
			{
				$debug->println( "<!-- Invalid credentials for connection to db -->" );
			}
		}
		$debug->outdent();
		$debug->println( "<!-- InstallController::grantExecuteToPublic end : $status -->" );
		
		return $status;
	}
	
	function installTablesIn( $request, $debug, $sql_dir )
	{
		$status = True;

		$debug->println( "<!-- InstallController::installTables start ($sql_dir) -->" );
		$debug->indent();
		{
			$dbadmin    = $request["dbadmin"];
			$dbpassword = $request["dbpassword"];

			$db_username = DB_USERNAME;
			$db_password = DB_PASSWORD;
			$db_hostname = DB_HOSTNAME;

			$database_name = DB . DB_VERSION;
			
			$files = scandir( $sql_dir );
			if ( ! empty( $files ) )
			{
				$db = new DBi( $dbadmin, DB_HOSTNAME, $dbpassword, False );
				if ( $db->connect( $debug ) )
				{
					/*
					   Below are two almost identical loops, the first processes .sql files starting with '_'.
					   The second processes .sql files that don't.
					 */

					foreach ( $files as $pos => $file )
					{
						if ( ("." != $file[0]) && ("_blank.sql" != "$file") && $this->endsWith( $file, ".sql" ) )
						{
							if ( "_" == $file[0] )
							{
								$debug->println( "<!-- Trying to load: $file -->" );
								$sql = SQL_loadfile( $sql_dir . "/" . $file );
								if ( False !== $sql )
								{
									if ( $db->multichange( DB, $sql, $debug ) )
									{
										$debug->println( "<!-- Added $file -->");
									}
									else
									{
										$status = False;
									}
								}
							}
						}
					}

					foreach ( $files as $pos => $file )
					{
						if ( ("." != $file[0]) && ("_blank.sql" != "$file") && $this->endsWith( $file, ".sql" ) )
						{
							if ( "_" != $file[0] )
							{
								$debug->println( "<!-- Trying to load: $file -->" );
								$sql = SQL_loadfile( $sql_dir . "/" . $file );
								if ( False !== $sql )
								{
									if ( $db->multichange( DB, $sql, $debug ) )
									{
										$debug->println( "<!-- Added $file -->");
									}
									else
									{
										$status = False;
									}
								}
							}
						}
					}
				}
				else
				{
					$debug->println( "<!-- Invalid credentials for connection to db -->" );
				}
			}
		}
		$debug->outdent();
		$debug->println( "<!-- InstallController::installTables end : $status -->" );
		
		return $status;
	}
}
?>