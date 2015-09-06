<?php

class JSONFile extends DataFile
{
	function __construct( $string )
	{
		parent::__construct();

		$named_tuples = $this->ParseJSON( $string );
		
		$this->init( $named_tuples );
	}
	
	static function ParseJSON( $encoded_json )
	{
		$named_tuples = array();
		$json         = html_entity_decode( urldecode( $encoded_json ) );
	
		if ( $json && mb_detect_encoding( $json, 'ASCII', true ) )
		{
			$obj = json_decode( $json, false, 512 );
			
			if ( $obj && $obj->results )
			{
				foreach ( $obj->results as $object )
				{
					if ( is_object( $object ) )
					{
						$associative = array();
					
						foreach ( $object as $field => $value )
						{
							$associative[$field] = $value;
						}
						
						$named_tuples[] = $associative;
					}
				}
			}
		}
		return $named_tuples;
	}
}