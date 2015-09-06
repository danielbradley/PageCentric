<?php

class HTMLFile
{
	function __construct( $filepath )
	{
		$this->htm = Input::unidecode( file_get_contents( $filepath, false ) );

		libxml_use_internal_errors(true);

		$this->dom = new DOMDocument();
		$this->dom->loadHTML( $this->htm );

		libxml_clear_errors();
	}

	function getElementById( $id )
	{
		$value = "";
	
		$element = $this->dom->getElementById( $id );
		
		if ( $element )
		{
			$value = self::InnerHTML( $this->dom, $element );
		}
		
		return $value;
	}

	function getElementByTagName( $tagName )
	{
		$value = "";
		
		$values = $this->getElementsByTagName( $tagName );
		if ( 0 < count( $values ) )
		{
			$value = $values[0];
		}

		return $value;
	}

	function getElementsByTagName( $tagName )
	{
		$values   = array();
		$elements = $this->dom->getElementsByTagName( $tagName );

		foreach ( $elements as $element )
		{
			$values[] = self::InnerHTML( $this->dom, $element );
		}
		return $values;
	}

	function getElementsByTagNameWithClass( $tagName )
	{
		$values   = array();
		$elements = $this->dom->getElementsByTagName( $tagName );

		foreach ( $elements as $element )
		{
			if ( $cls )
			{
				if ( $element->hasAttributes() )
				{
					$classes = self::GetClasses( $element );

					if ( ClassesContains( $classes, $cls ) )
					{
						$values[] = self::InnerHTML( $this->dom, $element );
					}
				}
			}
		}
		return $values;
	}

//	static function GetElement( $dom, $tagName, $cls = "" )
//	{
//		$value    = "";
//		$elements = $dom->getElementsByTagName( $tagName );
//
//		foreach ( $elements as $element )
//		{
//			if ( $cls )
//			{
//				if ( $element->hasAttributes() )
//				{
//					if ( string_contains( $element->attributes->getNamedItem( "class" )->value, $cls ) )
//					{
//						//$value = $element->nodeValue;
//						$value = self::InnerHTML( $dom, $element );
//						break;
//					}
//				}
//			}
//			else
//			{
//				$value = self::InnerHTML( $dom, $element );
//				break;
//			}
//		}
//		return $value;
//	}

	function InnerHTML( $dom, $element )
	{
		$innerHTML = "";

		if ( $element->hasChildNodes() )
		{
			foreach ( $element->childNodes as $child )
			{
				$innerHTML .= $dom->saveHTML( $child );
			}
		}
		else
		{
			$innerHTML .= $dom->saveHTML( $element );
		}
		
		return $innerHTML;
	}
	
	function GetClasses( $element )
	{
		return explode( " ", $element->attributes->getNamedItem( "class" )->value );
	}

	function ClassesContains( $classes, $cls )
	{
		foreach ( $classes as $c )
		{
			if ( $c == $cls ) return true;
		}
		return false;
	}
}