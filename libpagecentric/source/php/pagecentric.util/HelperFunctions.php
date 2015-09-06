<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

function array_get( $key, $array )
{
	//return isset( $array ) && array_key_exists( $key, $array ) ? $array[$key] : "";

	$ret = "";
	if ( isset( $key ) && isset( $array ) )
	{
		if ( is_array( $key ) && is_string( $array) )
		{
			$ret = array_key_exists( $array, $key ) ? $key[$array] : "";
		}
		else
		if ( is_array( $array ) && is_string( $key ) )
		{
			$ret = array_key_exists( $key, $array ) ? $array[$key] : "";
		}
	}
	return $ret;
}

function force_array( $object )
{
	if ( isset( $object ) && is_array( $object ) )
	{
		return $object;
	} else {
		return array();
	}
}

function first( $tuples )
{
	if ( is_array( $tuples ) )
	{
		return array_key_exists( 0, $tuples ) ? $tuples[0] : array();
	}
	else
	{
		return array();
	}
}

function printContent( $filename, $out )
{
	if ( file_exists( $filename ) )
	{
		$str = file_get_contents( $filename );
		echo $str;
	}
}

function string_contains( $hay, $needle )
{
	return ( strpos( $hay, $needle ) !== false );
}

function string_replace( $text, $array )
{
	foreach ( $array as $key => $value )
	{
		$text = str_replace( "%" . $key . "%", $value, $text );
	}
	
	return $text;
}

function string_startsWith( $hay, $needle )
{
    return $needle === "" || strpos( $hay, $needle) === 0;
}

//	Adapted from: http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
function string_endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function string_truncate( $string, $nr_of_words )
{
	$ret = "";

	if ( $string )
	{
		$words = explode( " ", $string );
		for ( $i=0; $i < $nr_of_words; $i++ )
		{
			$ret .= (" " . $words[$i]);
		}
		$ret .= "...";
	}
	
	return $ret;
}

function first_word( $str )
{
	$words = explode( " ", $str );
	if ( $words !== FALSE )
	{
		return $words[0];
	}
	else
	{
		return "";
	}
}

function calculate_approx_age( $birth_year )
{
	if ( $birth_year )
	{
		$year = date( "Y" );
		$next = $year - $birth_year;
	
		return "~$next years old";
	}
	else
	{
		return "No age recorded";
	}
}

function convert_to_english_list( $array )
{
	$list = "";

	$c = count( $array );
	if ( $c )
	{
		$c -= 1;

		for ( $i=0; $i < $c; $i++ )
		{
			$list .= $array[$i] . ", ";
		}

		$list .= ($c > 0) ? "and " : "";
		$list .= $array[$i];
	}

	return $list;
}

function convert_to_english_sentences( $array )
{
	$list = "";
	$c = count( $array );

	for ( $i=0; $i < $c; $i++ )
	{
		$list .= $array[$i] . ". ";
	}

	return $list;
}

function extract_field_into_array( $tuples, $field )
{
	$ret = array();
	
	foreach ( $tuples as $tuple )
	{
		$ret[] = array_get( $tuple, $field );
	}
	
	return $ret;
}

function date_conversion( $date, $pattern )
{
	$formatted = "";
	$date      = substr( $date, 0, 10 );
	$datetime  = date_create_from_format( 'Y-m-d', $date );
	if ( $datetime )
	{
		$timestamp = $datetime->getTimestamp();
		$formatted = date( $pattern, $timestamp );
	}
	return $formatted;
}

function date_isValid( $date )
{
	$date = substr( $date, 0, 10 );

	return (FALSE !== date_create_from_format( 'Y-m-d', $date ));
}

function content_decode( $content )
{
	$content = str_replace( "&lt;div&gt;",   "<div>", $content );
	$content = str_replace( "&lt;/div&gt;", "</div>", $content );

	$content = str_replace( "&lt;b&gt;",       "<b>", $content );
	$content = str_replace( "&lt;/b&gt;",     "</b>", $content );

	$content = str_replace( "&lt;b&gt;",       "<b>", $content );
	$content = str_replace( "&lt;/b&gt;",     "</b>", $content );

	$content = str_replace( "&lt;br&gt;",     "<br>", $content );
	
	return $content;
}

function IsHTTPS()
{
	return (isset( $_SERVER ) && ("" != array_get( $_SERVER, "HTTPS" )));
}

//function array_isAssociative( $array )
//{
//	$keys = array_keys( $array );
//
//	return ($keys[0] != '0');
//}
//
//function array_isAssociativeX( $array )
//{
//	$associative = false;
//	$keys        = array_keys( $array );
//	
//	foreach ( $keys as $obj )
//	{
//		if ( ! is_numeric( $obj ) ) $associative = true;
//	}
//
//	if ( ! $associative ) echo "XXX";
//
//	return $associative;
//}
//


