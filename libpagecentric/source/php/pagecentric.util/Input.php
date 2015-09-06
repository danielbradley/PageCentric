<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Input
{
	static function FilterInput( $request, $debug )
	{
		$filtered = array();

		$debug->println( "<!-- FilterInput() start -->" );
		$debug->indent();
		{
			
			$debug->println( "<!-- REQUEST -->" );
			$debug->indent();
			{
				foreach ( $_REQUEST as $key => $val )
				{
					$filtered_key = Input::Filter( $key );
					$filtered_val = Input::Filter( $val );

					$filtered[$filtered_key] = $filtered_val;

					if ( is_array( $filtered_val ) )
					{
						$debug->println( "<!-- \"$filtered_key\" | Array -->" );
					}
					else
					{
						$debug->println( "<!-- \"$filtered_key\" | \"$filtered_val\" -->" );
					}
				}
			}
			$debug->outdent();

			$debug->println( "<!-- COOKIE -->" );
			$debug->indent();
			{
				foreach ( $_COOKIE as $key => $val )
				{
					if ( ! array_key_exists( $key, $filtered ) )
					{
						$filtered_key = Input::Filter( $key );
						$filtered_val = Input::Filter( $val );

						$filtered[$filtered_key] = $filtered_val;
						$debug->println( "<!-- \"$filtered_key\" | \"$filtered_val\" -->" );
					}
				}
			}
			$debug->outdent();
		}
		$debug->outdent();
		$debug->println( "<!-- FilterInput() end -->" );

		return $filtered;
	}

	static function Filter( $value )
	{
		if ( is_array( $value ) )
		{
			$ret = array();
			
			foreach ( $value as $key => $val )
			{
				$filtered_key = Input::Filter( $key );
				$filtered_val = Input::Filter( $val );
			
				$ret[$filtered_key] = $filtered_val;
			}
			
			return $ret;
		}
		else
		if ( is_string( $value ) )
		{
			$value = Input::unidecode( $value );
			$value = htmlentities( $value, ENT_QUOTES, 'UTF-8', false );
			$value = get_magic_quotes_gpc() ? $value : addslashes( $value );
			$value = DBi_escape( $value );

			return $value;
		}
		else
		if ( is_null( $value ) )
		{
			return "";
		}
		else
		{
			error_log( "Input::Filter( $value ): unexpected value!" );
		}
	}

	static function unidecode( $value )
	{
		$str = "";
		$n   = strlen( $value );
		$i   = 0;
		
		while ( $i < $n )
		{
			$ch  = substr( $value, $i, 1 );
			$val = ord( $ch );

			if ( ($val == (0xFC | $val)) && ($i+5 < $n) )		// 6 byte unicode
			{
				$str .= Input::utf2html( substr( $value, $i, 6 ) );
				$i   += 6;
			}
			else
			if ( ($val == (0xF8 | $val)) && ($i+4 < $n) )		// 5 byte unicode
			{
				$str .= Input::utf2html( substr( $value, $i, 5 ) );
				$i   += 5;
			}
			else
			if ( ($val == (0xF0 | $val)) && ($i+3 < $n) ) 		// 4 byte unicode
			{
				$str .= Input::utf2html( substr( $value, $i, 4 ) );
				$i   += 4;
			}
			else
			if ( ($val == (0xE0 | $val)) && ($i+2 < $n) )		// 3 byte unicode
			{
				$str .= Input::utf2html( substr( $value, $i, 3 ) );
				$i   += 3;
			}
			else
			if ( ($val == (0xC0 | $val)) && ($i+1 < $n) )	// 2 byte unicode
			{
				$str .= Input::utf2html( substr( $value, $i, 2 ) );
				$i   += 2;
			}
			else
			if ( $val == (0x80 | $val) )		// extra byte
			{
				error_log( "Warning detected invalid unicode" );
				$str .= '?';
				$i++;
			}
			else								// ascii character
			{
				$str .= $ch;
				$i++;
			}
		}
		return $str;
	}

	static function utf2html( $string )
	{
		$array  = Input::utf8_to_unicode( $string );
		$string = Input::unicode_to_entities( $array );
		
		return $string;
	}

	static function utf8_to_unicode( $str )
	{
        $unicode = array();
        $values = array();
        $lookingFor = 1;
        
        for ($i = 0; $i < strlen( $str ); $i++ ) {

            $thisValue = ord( $str[ $i ] );
            
            if ( $thisValue < 128 ) $unicode[] = $thisValue;
            else {
            
                if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                
                $values[] = $thisValue;
                
                if ( count( $values ) == $lookingFor ) {
            
                    $number = ( $lookingFor == 3 ) ?
                        ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                    	( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
                        
                    $unicode[] = $number;
                    $values = array();
                    $lookingFor = 1;
            
                } // if
            
            } // if
            
        } // for

        return $unicode;
    
	}

	static function unicode_to_entities( $unicode )
	{
		$entities = '';
		foreach( $unicode as $value ) $entities .= '&#' . $value . ';';
		return $entities;
	}
}

?>