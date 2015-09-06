<?php

class CSVFile extends DataFile
{
	function __construct( $string )
	{
		parent::__construct();

		$tuples       = $this->ParseCSV( $string );
		$headings     = $this->ExtractHeadings  ( $tuples );
		$named_tuples = $this->CreateNamedTuples( $tuples, $headings );
		
		$this->init( $named_tuples );
	}
	
	static function ParseCSV( $string )
	{
		$tuples = array();
	
		if ( mb_detect_encoding( $string, 'ASCII', true ) )
		{
			//	Copied from:
			//	http://stackoverflow.com/questions/1483497/how-to-put-string-in-array-split-by-new-line
			
			$lines = preg_split( "/(\r\n|\n|\r)/", $string );

			foreach ( $lines as $line )
			{
				if ( "" != trim( $line ) )
				{
					$tuples[] = str_getcsv( $line );
				}
			}
		}
		return $tuples;
	}

	static function ExtractHeadings( $tuples )
	{
		$headings = array();

		if ( array_key_exists( 0, $tuples ) )
		{
			$headings = $tuples[0];
		}
		
		return $headings;
	}

	static function CreateNamedTuples( $tuples, $headings )
	{
		$named_tuples = array();
		
		foreach( $tuples as $tuple )
		{
			$associative_array = CSVFile::CreateAssociativeArray( $tuple, $headings );
			
			if ( 0 < count( $associative_array ) )
			{
				$named_tuples[] = $associative_array;
			}
		}
		
		return $named_tuples;
	}

	static function CreateAssociativeArray( $tuple, $headings )
	{
		//	headings  = { "Resource name", "Notes", "Gender", etc... }
		//	csv_tuple = {        "<Name>",      "",      "M", etc... }
		//	returns   = { "Resource name" => "<Name>", "Notes => "", "Gender" => "M", etc... }

		$associative = array();
		
		$i = 0;
		foreach ( $headings as $heading )
		{
			if ( array_key_exists( $i, $tuple ) )
			{
				$value = $tuple[$i];
			
				if ( $heading != $value )
				{
					$associative["$heading"] = "$value";
				}
			}
			$i++;
		}
		
		return $associative;
	}
}