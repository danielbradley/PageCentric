<?php
//	Copyright (c) 2014 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class JSON
{
	static function encode( $results )
	{
		$ret = "{";
	
		if ( is_array( $results ) && (0 < count( $results ) ) )
		{
			$ret .= '"results":';
			$ret .= "[";
			$sep = "";
		
			foreach ( $results as $tuple )
			{
				$ret .= $sep;
				$ret .= JSON::encodeTuple( $tuple );
				$sep = ",";
			}
			
			$ret .= "]";
		}

		$ret .= "}";

		return $ret;
	}

	static function encodeObject( $tuple )
	{
		return JSON::encodeTuple( $tuple );
	}

	static function encodeTuple( $tuple )
	{
		$ret = "{";
		$sep = "";
	
		foreach ( $tuple as $key => $value )
		{
			$ret .= $sep;

			//error_log( "JSON::encodeKeyValue( $key, $value )" );
			$ret .= JSON::encodeKeyValue( $key, $value );

			$sep = ",";
		}

		$ret .= "}";

		return $ret;
	}

	static function encodeKeyValue( $key, $value )
	{
		$ret = "";
	
		if ( is_array( $value ) )
		{
			$ret = "\"$key\":" . JSON::encodeArray( $value );

			//error_log( $ret );
		}
		else
		{
			$ret = "\"$key\":\"$value\"";

			//error_log( $ret );
		}
		return $ret;
	}

	static function encodeX( $obj )
	{
		$ret = "{";

		if ( is_array( $obj ) && (0 < count( $obj ) ) )
		{
			$ret .= '"results":';
			$ret .= "[";
			$sep  = "";
		
			foreach ( $obj as $tuple )
			{
				$ret .= $sep;

				if ( is_array( $tuple ) && JSON::is_assoc( $tuple ) )
				{
					$ret .= JSON::encodeTupleX( $tuple );
				}
				else
				if ( is_array( $tuple ) )
				{
					$ret .= JSON::encodeX( $tuple );
				}
				$sep = ",";
			}
			
			$ret .= "]";
		}

		$ret .= "}";

		return $ret;
	}

	static function encodeTupleX( $tuple )
	{
		$ret = "{";
		$sep = "";
	
		foreach ( $tuple as $key => $value )
		{
			$ret .= $sep;
			$ret .= JSON::encodeKeyValueX( $key, $value );

			$sep = ",";
		}

		$ret .= "}";

		return $ret;
	}

	static function encodeKeyValueX( $key, $value )
	{
		$ret = "\"$key\":";
		
		if ( is_array( $value ) )
		{
			$ret .= JSON::encodeArray( $value );
		}
		else
		{
			$ret .= "\"$value\"";
		}
		return $ret;
	}

	static function encodeArray( $obj )
	{
		//error_log( "encodeArray( $obj )" );
	
		$ret = "[";
	
		if ( is_array( $obj ) && (0 < count( $obj ) ) )
		{
			$sep  = "";

			if ( JSON::is_assoc( $obj ) )
			{
				foreach ( $obj as $name => $tuple )
				{
					$ret .= $sep;
					
					$ret .= "{\"$name\":";

					if ( is_array( $tuple ) && JSON::is_assoc( $tuple ) )
					{
						$ret .= JSON::encodeTupleX( $tuple );
					}
					else
					if ( is_array( $tuple ) )
					{
						$ret .= JSON::encodeArray( $tuple );
					}
					else
					{
						$ret .= "\"$tuple\"}";
					}
					$sep = ",";
				}
			}
			else
			{
				foreach ( $obj as $tuple )
				{
					$ret .= $sep;

					if ( is_array( $tuple ) && JSON::is_assoc( $tuple ) )
					{
						$ret .= JSON::encodeTupleX( $tuple );
					}
					else
					if ( is_array( $tuple ) )
					{
						$ret .= JSON::encodeX( $tuple );
					}
					$sep = ",";
				}
			}
		}
		$ret .= "]";

		return $ret;
	}


	static function is_assoc( $a )
	{
		$b = array_keys($a);

		return ($a != array_keys($b));
	}
}
