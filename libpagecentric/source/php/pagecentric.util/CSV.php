<?php
//	Copyright (c) 2014 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class CSV
{
	static function PlainText()
	{
		header( "Content-Type: text/plain" );
	}

	static function InitiateDownload( $filename="download.csv" )
	{
		header( "Content-Type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=\"$filename\"" );
	}

	static function encode( $results, $numbered = true )
	{
		$ret = "";

		if ( is_array( $results ) && (0 < count( $results ) ) )
		{
			$ret .= CSV::Heading( $results, $numbered );
			$ret .= CSV::Rows( $results, $numbered );
		}
		
		return $ret;
	}
	
	static function Heading( $results, $numbered )
	{
		$heading = "";
	
		foreach ( $results as $tuple )
		{
			$sep = "";
		
			foreach ( $tuple as $key => $value )
			{
				$heading .= $sep;
				$heading .= $key;
				$sep = ",";
			}
			break;
		}
		if ( $numbered ) $heading .= ",#";

		return $heading .= "\n";
	}

	static function Rows( $results, $numbered )
	{
		$nr   = 0;
		$rows = "";
	
		foreach ( $results as $tuple )
		{
			$nr++;
			$sep = "";
		
			foreach ( $tuple as $key => $value )
			{
				$rows .= $sep;

				$value = html_entity_decode( $value, ENT_QUOTES );

				if ( string_contains( $value, "," ) )
				{
					$rows .= "\"$value\"";
				}
				else
				{
					$rows .= "$value";
				}
				$sep = ",";
			}
			if ( $numbered ) $rows .= ",$nr";

			$rows .= "\n";
 		}

		return $rows;
	}
	
	static function MapValues( $map, $tuples )
	{
		$mapped_tuples = array();
	
		foreach ( $tuples as $obj )
		{
			$tuple  = (array) $obj;
			$mapped = array();
			
			foreach ( $map as $label => $key )
			{
				$mapped[$label] = $tuple[$key];
			}
			
			$mapped_tuples[] = $mapped;
		}
		
		return $mapped_tuples;
	}
}
