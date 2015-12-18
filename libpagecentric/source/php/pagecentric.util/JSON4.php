<?php
//	Copyright (c) 2015 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class JSON4
{
	static function EncodeResults( $results )
	{
		$json = "";
	
		$json .= "{";
		
		if ( is_array( $results ) && (0 < count( $results ) ) )
		{
			$json .= '"results":';
			$json .= "[";
			
			$sep = "";
			
			foreach ( $results as $tuple )
			{
				$json .= $sep;
				$json .= self::EncodeTuple( $tuple );
				$sep = ",";
			}
			
			$json .= "]";
		}
		
		$json .= "}";

		return $json;
	}

	static function EncodeTuple( $tuple )
	{
		$json = "";
		$json .= "{";
		$sep = "";
	
		foreach ( $tuple as $key => $value )
		{
			$json .= $sep;

			$json .= self::EncodeStringValue( $key, $value );

			$sep = ",";
		}

		$json .= "}";

		return $json;
	}

	static function Encode( $something )
	{
		$json = "";
		if ( is_array( $something ) )
		{
			$json .= self::EncodeArray( $something );
		}
		else
		{
			$json .= self::EncodeObject( $something );
		}

		return $json;
	}

	static function EncodeArray( $array )
	{
		$json = "";
		$json .= "[";
		$sep = "";
		
		if ( self::is_assoc( $array ) )
		{
			foreach ( $array as $string => $value )
			{
				$json .= $sep . "{";
				
				$json .= self::EncodeStringValue( $string, $value );
				
				$json .= "}";

				$sep = ",";
			}
		}
		else
		{
			foreach ( $array as $value )
			{
				$json .= $sep;
				
				$json .= self::EncodeValue( $value );
				
				$sep = ",";
			}
		}
		$json .= "]";

		return $json;
	}

	static function EncodeObject( $object )
	{
		$json = "";
		$json .= "{";
		$sep = "";

		foreach ( $object as $member => $value )
		{
			$json .= $sep;
			
			$json .= self::EncodeStringValue( $member, $value );

			$sep = ",";
		}
		$json .= "}";

		return $json;
	}

	static function EncodeStringValue( $string, $value )
	{
		$json = "";
		$json .= self::EncodeString( $string );
		$json .= ":";
		$json .= self::EncodeValue( $value );

		return $json;
	}

	static function EncodeString( $string )
	{
		$json = "";
		$escaped = str_replace( "\\", "\\\\", $string );

		$json .= "\"$escaped\"";

		return $json;
	}

	static function EncodeValue( $value )
	{
		$json = "";
		if ( is_array( $value ) )
		{
			$json .= self::EncodeArray( $value );
		}
		else
		if ( is_string( $value ) )
		{
			$json .= self::EncodeString( $value );
		}
		else
		if ( is_numeric( $value ) )
		{
			$json .= self::EncodeNumber( $value );
		}
		else
		if ( is_null( $value ) )
		{
			$json .= "null";
		}
		else
		if ( true === $value )
		{
			$json .= "true";
		}
		else
		if ( false === $value )
		{
			$json .= "false";
		}
		else
		{
			$json .= self::EncodeObject( $value );
		}

		return $json;
	}

	static function EncodeNumber( $number )
	{
		$json = "";
		$json .= $number;

		return $json;
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
