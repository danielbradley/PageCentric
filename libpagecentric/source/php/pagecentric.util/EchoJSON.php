<?php
//	Copyright (c) 2015 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class EchoJSON
{
	static function EncodeResults( $results )
	{
		echo "{";
		
		if ( is_array( $results ) && (0 < count( $results ) ) )
		{
			echo '"results":';
			echo "[";
			
			$sep = "";
			
			foreach ( $results as $tuple )
			{
				echo $sep;
				self::EncodeTuple( $tuple );
				$sep = ",";
			}
			
			echo "]";
		}
		
		echo "}";
	}

	static function EncodeTuple( $tuple )
	{
		echo "{";
		$sep = "";
	
		foreach ( $tuple as $key => $value )
		{
			echo $sep;

			self::EncodeStringValue( $key, $value );

			$sep = ",";
		}

		echo "}";
	}

	static function Encode( $something )
	{
		if ( is_array( $something ) )
		{
			self::EncodeArray( $something );
		}
		else
		{
			self::EncodeObject( $something );
		}
	}

	static function EncodeArray( $array )
	{
		echo "[";
		$sep = "";
		
		if ( self::is_assoc( $array ) )
		{
			foreach ( $array as $string => $value )
			{
				echo $sep . "{";
				
				self::EncodeStringValue( $string, $value );
				
				echo "}";

				$sep = ",";
			}
		}
		else
		{
			foreach ( $array as $value )
			{
				echo $sep;
				
				self::EncodeValue( $value );
				
				$sep = ",";
			}
		}
		echo "]";
	}

	static function EncodeObject( $object )
	{
		echo "{";
		$sep = "";

		foreach ( $object as $member => $value )
		{
			echo $sep;
			
			self::EncodeStringValue( $member, $value );

			$sep = ",";
		}
		echo "}";
	}

	static function EncodeStringValue( $string, $value )
	{
		self::EncodeString( $string );
		echo ":";
		self::EncodeValue( $value );
	}

	static function EncodeString( $string )
	{
		$escaped = str_replace( "\\", "\\\\", $string );

		echo "\"$escaped\"";
	}

	static function EncodeValue( $value )
	{
		if ( is_array( $value ) )
		{
			self::EncodeArray( $value );
		}
		else
		if ( is_string( $value ) )
		{
			self::EncodeString( $value );
		}
		else
		if ( is_numeric( $value ) )
		{
			self::EncodeNumber( $value );
		}
		else
		if ( is_null( $value ) )
		{
			echo "null";
		}
		else
		if ( true === $value )
		{
			echo "true";
		}
		else
		if ( false === $value )
		{
			echo "false";
		}
		else
		{
			self::EncodeObject( $value );
		}
	}

	static function EncodeNumber( $number )
	{
		echo $number;
	}

	static function is_assoc( $a )
	{
		$assoc = true;
	
		$keys = array_keys( $a );
		foreach ( $keys as $key )
		{
			if ( is_numeric( $key ) && (0 == $key) ) $assoc = false;
			break;
		}
		return $assoc;
	}

	static function is_assocx( $a )
	{
		$b = array_keys($a);

		return ($a != array_keys($b));
	}
}
