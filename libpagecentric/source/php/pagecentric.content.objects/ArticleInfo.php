<?php

class ArticleInfo
{
	static function Decode( $filepath )
	{
//		error_log( ">>> Decode( $filepath )" );

		$group       = "";
		$category    = "";
		$resource_id = "";
		$pubdate     = "";
		$titlecode   = "";
		$filename    = "";
	
		//$real = str_replace( BASE_PATH, "", $filepath );
		$bits = explode( '/', $filepath );
		$n    = count( $bits );

//			error_log( "           real = $real" );

		if ( $n > 0 )
		{
			$n--;
			$filename = $bits[$n];

//			error_log( "       filename = $filename" );
		}

		if ( $n > 0 )
		{
			$n--;
			$resource_id = $bits[$n];
			$pubdate     = ArticleInfo::extractPubdate  ( $resource_id );
			$titlecode   = ArticleInfo::extractTitlecode( $resource_id );

//			error_log( "    resource_id = $resource_id" );
		}

		if ( $n > 0 )
		{
			$n--;
			$category = $bits[$n];
		}

		if ( $n > 0 )
		{
			$n--;
			$group = $bits[$n];
		}

		return new ArticleInfo( $filepath, $group, $category, $resource_id, $pubdate, $titlecode, $filename );
	}

	function __construct( $filepath, $group, $category, $resource_id, $pubdate, $titlecode, $filename )
	{
		$this->filepath   = $filepath;
		$this->group      = $group;
		$this->category   = $category;
		$this->resourceID = $resource_id;
		$this->pubdate    = $pubdate;
		$this->titlecode  = $titlecode;
		$this->filename   = $filename;
		$this->dirpath    = dirname( $filepath );

		$this->imagePath  = "$group/$category/$resource_id/image.png";
		$this->imageURL   = VIDEO_HOST . "/" . APP_NAME . "/" . $this->imagePath;

		$this->title = str_replace( '_', ' ', $titlecode );
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

		$t = is_numeric( $month ) ? "$year-$month-$day" : "$year-$month";

		return $t;
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