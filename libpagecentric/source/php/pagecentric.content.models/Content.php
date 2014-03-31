<?php

class Content
{
	static function getHTMFor( $page_index, $key )
	{
		$h = "";

		$base         = $_SERVER["DOCUMENT_ROOT"];
		$content_path = defined( "CONTENT_PATH" ) ? CONTENT_PATH : BASE . "/share/content";
		$f            = $content_path . "/" . $page_index . "/" . $key . ".htm";
		
		if ( file_exists( $f ) )
		{
			//echo "<!-- Loading: $f -->";
			$h = file_get_contents( $f, false );
		}
		else
		{
			$h = "<!-- Could not find: $f -->";// . LoremView::loremShort();
		}

		return $h;
	}

	static function retrieveArticles()
	{
		$articles = array();
		$articles[] = array( "subject" => "English",          "date" => "2013-09-21", "title" => "English Advanced Module A", "views" =>  814 );
		$articles[] = array( "subject" => "Biology",          "date" => "2013-04-27", "title" => "Biology",                   "views" => 3539 );
		$articles[] = array( "subject" => "Business Studies", "date" => "2013-04-21", "title" => "Business Studies",          "views" => 2120 );
		
		return $articles;
	}

	static function retrieveArticlesByCategory( $category )
	{
		$search_dir = ARTICLES_PATH . "/$category";

		$files = Files::recurseFiles( $search_dir, ".htm" );
		$infos = array();

		foreach ( $files as $filepath )
		{
			error_log( $filepath );
		
			$infos[] = ArticleInfo::Decode( $filepath );
		}

		return $infos;
	}

	static function encodeQuotes( $h )
	{
		$h = str_replace( " '", " &lsquo;", $h );
		$h = str_replace( "' ", "&rsquo; ", $h );
		$h = str_replace(  "'",  "&rsquo;", $h );

		return $h;
	}
}

?>