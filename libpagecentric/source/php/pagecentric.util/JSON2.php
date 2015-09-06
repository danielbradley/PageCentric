<?php
//	Copyright (c) 2015 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class JSON2
{
	static function Encode( $something )
	{
		$encoded = "";
	
		if ( is_array( $something ) )
		{
			$encoded .= JSON2::EncodeArray( $something );
		}
		else
		{
			$encoded .= JSON2::EncodeObject( $something );
		}
		return $encoded;
	}

	static function EncodeArray( $array )
	{
		$encoded = "[";
		$sep     = "";
		
		if ( JSON2::is_assoc( $array ) )
		{
			foreach ( $array as $string => $value )
			{
				$encoded .= $sep . "{" . JSON2::EncodeStringValue( $string, $value ) . "}";
				$sep      = ",";
			}
		}
		else
		{
			foreach ( $array as $value )
			{
				$encoded .= $sep . JSON2::EncodeValue( $value );
				$sep      = ",";
			}
		}
		return $encoded . "]";
	}

	static function EncodeObject( $object )
	{
		$encoded = "{";
		$sep     = "";

		foreach ( $object as $member => $value )
		{
			$encoded .= $sep . JSON2::EncodeStringValue( $member, $value );
			$sep      = ",";
		}
		return $encoded . "}";
	}

	static function EncodeStringValue( $string, $value )
	{
		return JSON2::EncodeString( $string ) . ":" . JSON2::EncodeValue( $value );
	}

	static function EncodeString( $string )
	{
		return "\"$string\"";
	}

	static function EncodeValue( $value )
	{
		$encodedd = "";
	
		if ( is_array( $value ) )
		{
			$encoded = JSON2::EncodeArray( $value );
		}
		else
		if ( is_string( $value ) )
		{
			$encoded = JSON2::EncodeString( $value );
		}
		else
		if ( is_numeric( $value ) )
		{
			$encoded = JSON2::EncodeNumber( $value );
		}
		else
		if ( is_null( $value ) )
		{
			$encoded = "null";
		}
		else
		if ( true === $value )
		{
			$encoded = "true";
		}
		else
		if ( false === $value )
		{
			$encoded = "false";
		}
		else
		{
			$encoded = JSON2::EncodeObject( $value );
		}
		
		return $encoded;
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
