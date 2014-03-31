<?php

class ArticleInfo
{
	function __construct( $category, $pubdate, $titlecode, $filename )
	{
		$this->category  = $category;
		$this->pubdate   = $pubdate;
		$this->titlecode = $titlecode;
		$this->filename  = $filename;

		$this->title = str_replace( '_', ' ', $titlecode );
	}
	
	static function Decode( $filepath )
	{
		error_log( ">>> Decode( $filepath )" );

		$category   = "";
		$resource_id = "";
		$pubdate    = "";
		$titlecode  = "";
		$filename   = "";
	
		$real = str_replace( ARTICLES_PATH, "", $filepath );
		$bits = explode( '/', $real );
		$n    = count( $bits );

			error_log( "           real = $real" );
		
		if ( $n > 0 )
		{
			$n--;
			$filename = $bits[$n];

			error_log( "       filename = $filename" );
		}

		if ( $n > 0 )
		{
			$n--;
			$resource_id = $bits[$n];
			$pubdate     = ArticleInfo::extractPubdate  ( $resource_id );
			$titlecode   = ArticleInfo::extractTitlecode( $resource_id );

			error_log( "    resource_id = $resource_id" );
		}

		if ( $n > 0 )
		{
			$n--;
			$category = $bits[$n];
		}

		return new ArticleInfo( $category, $pubdate, $titlecode, $filename );
	}
	
	static function extractPubdate( $resource_id )
	{
		$year  = "";
		$month = "";
		$day   = "";

		$bits = explode( '-', $resource_id );
		$n    = count( $bits );
		$i    = 0;
		
		if ( $i < $n )
		{
			$year = $bits[$i];
			$i++;
		}
		
		if ( $i < $n )
		{
			$month = $bits[$i];
			$i++;
		}
		
		if ( $i < $n )
		{
			$day = $bits[$i];
			$i++;
		}

		return "$year-$month-$day";
	}

	static function extractTitleCode( $resource_id )
	{
		$titlecode = "";

		$bits = explode( '-', $resource_id );
		$n    = count( $bits );

		if ( 3 < $n )
		{
			$titlecode = $bits[3];
		}

		return $titlecode;
	}
}

?>