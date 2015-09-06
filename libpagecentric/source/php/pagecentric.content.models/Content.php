<?php

class Content
{
	static function IsHTMFor( $page_index, $key, $content_path = CONTENT_PATH )
	{
		$f = $content_path . "/" . $page_index . "/" . $key . ".htm";
		
		return file_exists( $f );
	}

	static function getHTMFor( $page_index, $key, $content_path = CONTENT_PATH )
	{
		$h = "";
		$f = $content_path . "/" . $page_index . "/" . $key . ".htm";
		
		if ( file_exists( $f ) )
		{
			//echo "<!-- Loading: $f -->";
			$h = file_get_contents( $f, false );
		}
		else
		{
			$h = "<!-- Could not find: $f -->";// . LoremView::loremShort();
		}

		return Input::unidecode( $h );
	}

	static function retrieveArticlesX()
	{
		$articles = array();
		$articles[] = array( "subject" => "English",          "date" => "2013-09-21", "title" => "English Advanced Module A", "views" =>  814 );
		$articles[] = array( "subject" => "Biology",          "date" => "2013-04-27", "title" => "Biology",                   "views" => 3539 );
		$articles[] = array( "subject" => "Business Studies", "date" => "2013-04-21", "title" => "Business Studies",          "views" => 2120 );
		
		return $articles;
	}

	static function retrieveArticles( $search_dir = ARTICLES_PATH )
	{
		$files = Files::recurseFiles( $search_dir, ".htm" );
		$infos = array();

		foreach ( $files as $filepath )
		{
			//error_log( $filepath );

			$infos[] = Article::LoadFrom( $filepath );
		}

		return $infos;
	}

	static function retrieveArticlesByCategory( $category, $search_dir = ARTICLES_PATH )
	{
		$search_dir = $search_dir . "/$category";

		$files = Files::recurseFiles( $search_dir, ".htm" );
		$infos = array();

		foreach ( $files as $filepath )
		{
			$infos[] = Article::LoadFrom( $filepath );
		}

		return $infos;
	}

	static function retrieveModArticles( $page, $mod )
	{
		$nr = $page->getRequest( "display" );
		$nr = $nr ? $nr : $mod;
	
		$search_dir = ARTICLES_PATH;

		$files = Files::recurseFiles( $search_dir, ".htm" );
		$infos = array();

		foreach ( $files as $filepath )
		{
			error_log( $filepath );
		
			$infos[] = ArticleInfo::Decode( $filepath );
		}

		$sorted = Content::SortByDate( $infos );

		return array_slice( $sorted, 0, $nr );
	}

	static function retrieveNArticles( $page, $nr, $pg )
	{
		$i = $nr * ($pg - 1);

		$search_dir = ARTICLES_PATH;

		$files = Files::recurseFiles( $search_dir, ".htm" );
		$infos = array();

		foreach ( $files as $filepath )
		{
			error_log( $filepath );
		
			$infos[] = ArticleInfo::Decode( $filepath );
		}

		$sorted = Content::SortByDate( $infos );

		return array_slice( $sorted, $i, $nr );
	}

	static function encodeQuotes( $h )
	{
		$h = str_replace( " '", " &lsquo;", $h );
		$h = str_replace( "' ", "&rsquo; ", $h );
		$h = str_replace(  "'",  "&rsquo;", $h );

		return $h;
	}

	static function SortRandom( $articles )
	{
		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = $article->getPubDate();
		
			if ( ! array_key_exists( $key, $dict ) ) $keys[] = $key;
			
			$dict[$key][] = $article;
		}

		shuffle( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByDate( $articles )
	{
		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = $article->getPubDate();
		
			if ( ! array_key_exists( $key, $dict ) ) $keys[] = $key;
			
			$dict[$key][] = $article;
		}

		arsort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByReverseDate( $articles )
	{
		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = $article->getPubDate();
		
			if ( ! array_key_exists( $key, $dict ) ) $keys[] = $key;
			
			$dict[$key][] = $article;
		}

		asort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByAuthor( $articles )
	{
		$articles = Content::SortByDate( $articles );

		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = trim( str_replace( 'Dr', '', str_replace( 'Dr.', '', $article->getAuthor() ) ) );

			if ( ! array_key_exists( "$key", $dict ) ) $keys[] = $key;
		
			$dict[$key][] = $article;
		}

		asort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByTitle( $articles )
	{
		$articles = Content::SortByDate( $articles );

		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = $article->getTitle();

			if ( ! array_key_exists( "$key", $dict ) ) $keys[] = $key;
		
			$dict[$key][] = $article;
		}

		asort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByArticleID( $articles )
	{
		$articles = Content::SortByDate( $articles );

		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$key = $article->getArticleID();

			if ( ! array_key_exists( "$key", $dict ) ) $keys[] = $key;
		
			$dict[$key][] = $article;
		}

		asort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $a )
			{
				$sorted[] = $a;
			}
		}
		
		return $sorted;
	}

	static function SortByViews( $articles, $counts )
	{
		//$articles = Content::SortByDate( $articles );

		$dict   = array();
		$keys   = array();
		$sorted = array();

		foreach ( $articles as $article )
		{
			$id  = $article->getResourceID();

			if ( array_key_exists( $id, $counts ) )
			{
				$tuple = array_get( $counts, $id );

				$key = array_get( $tuple, "nr_of_views" );
			}
			else
			{
				$key = "0";
			}

			if ( ! array_key_exists( "$key", $dict ) ) $keys[] = $key;
		
			$dict[$key][] = $article;
		}

		arsort( $keys );

		foreach ( $keys as $key )
		{
			$array = $dict[$key];
			
			foreach ( $array as $article )
			{
				$sorted[] = $article;

				error_log( "Adding: " . $article->getArticleID() );
			}
		}
		
		return $sorted;
	}
	
	static function filter( $all_articles, $keyword )
	{
		$keyword = strtolower( $keyword );
		$filtered = array();
		
		foreach ( $all_articles as $article )
		{
			if ( string_contains( strtolower( $article->getTitle() ), $keyword ) || string_contains( strtolower( $article->getCategory() ), $keyword ) )
			{
				$filtered[] = $article;
			}
		}
	
		return $filtered;
	}
}

?>