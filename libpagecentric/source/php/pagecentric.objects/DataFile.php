<?php

class DataFile
{
	private $namedTuples;
	private $valid;

	function __construct()
	{
		$this->namedTuples = null;
		$this->valid       = false;
	}

	function init( $named_tuples )
	{
		if ( $this->TuplesHaveEqualLength( $named_tuples ) )
		{
			$this->namedTuples = $named_tuples;
			$this->valid       = true;
		}
		else
		{
			error_log( "DataFile::init: tuples have unequal length!" );
		}
	}

	function getNamedTuples()
	{
		return $this->namedTuples;
	}

	function extractFirst( $field )
	{
		$array = $this->extractValues( $field );
		
		return (0 < count( $array )) ? $array[0] : "";
	}

	function extractValues( $key )
	{
		$values = array();
	
		foreach ( $this->namedTuples as $tuple )
		{
			if ( array_key_exists( $key, $tuple ) )
			{
				$values[] = $tuple[$key];
			}
			else
			{
				error_log( "DataFile::extractValues( Array, $key ): Error" );
			}
		}

		error_log( "DataFile::extractValues( Array, $key ), found unique : " . count( $values ) );

		return array_unique( $values );
	}

	function hasNamedTuples()
	{
		return $this->namedTuples ? true : false;
	}

	function isValid()
	{
		return $this->valid;
	}

	function asJSON()
	{
		return \JSON::encode( $this->namedTuples );
	}

	function errorLog()
	{
		foreach ( $this->namedTuples as $tuple )
		{
			$comma = "";
			$line  = "{";
			
			foreach ( $tuple as $key => $value )
			{
				$line .= $comma . '"' . $key . '":"' . $value . '"';
				$comma = ", ";
			}
			
			$line .= "}";
			
			error_log( $line );
		}
	}

	static function TuplesHaveEqualLength( $tuples )
	{
		$equal = true;
	
		if ( is_array( $tuples ) && (0 < count( $tuples )) && is_array( $tuples[0] ) )
		{
			$len = count( $tuples[0] );

			foreach ( $tuples as $tuple )
			{
				$equal &= (count( $tuple ) == $len);
			}
		}

		return $equal;
	}

	static function ToCSV( $tuple )
	{
		$csv   = "";
		$comma = " ";

		foreach ( $tuple as $field )
		{
			$csv .= $comma . '"' . $field . '"';

			$comman = ", ";
		}

		return $csv;
	}
}