<?php

class ArticleSync
{
	protected $path = "";

	public function __construct()
	{
		$this->path = defined( "ARTICLES_PATH" ) ? ARTICLES_PATH : "";
	}

	public function perform( $out, $debug )
	{
		self::RecursePath( $this->path, $out );
	}
	
	function RecursePath( $path, $out )
	{
		if ( ! is_dir( $path ) )
		{
			self::ProcessFile( $path, $out );
		}
		else
		{
			$dir = opendir( $path );
			
			if ( FALSE !== $dir )
			{
				while ( false !== ($file = readdir( $dir )) )
				{
					switch ( $file )
					{
					case ".":
					case "..":
						break;
						
					default:
						self::RecursePath( "$path/$file", $out );
					}
				}
				closedir( $dir );
			}
			else
			{
				$out->println( "Not a directory!... Ending recursion." );
			}
		}
	}

	function ProcessFile( $path, $out )
	{
		if ( string_endsWith( $path, "content.htm" ) )
		{
			$bits = array_reverse( explode( "/", $path ) );

			if ( 7 <= count( $bits ) )
			{
				$filename = str_replace( '_', ' ', $bits[0] );
				$title    = str_replace( '_', ' ', $bits[1] );
				$session  = str_replace( '_', ' ', $bits[2] );
				$daydate  = str_replace( '_', ' ', $bits[3] );
				$subject  = str_replace( '_', ' ', $bits[4] );
				$category = str_replace( '_', ' ', $bits[5] );
				$source   = str_replace( '_', ' ', $bits[6] );

				$bits = explode( " ", $daydate );

				if ( 2 <= count( $bits ) )
				{
					$day  = $bits[0];
					$date = $bits[1];

					switch ( $source )
					{
					case "videos":
					case "articles":
					case "questions":

						self::Save( $path, $source, $category, $subject, $day, $date, $session, $title, $out );
						break;
					}
				}
				else
				{
					//$out->println( "skipping: $path ($daydate)" );
				}
			}
			else
			{
				//$out->println( "skipping: $path" );
			}
		}
	}

	function Save( $path, $source, $category, $subject, $day, $date, $session, $title, $out )
	{
		$article = Article::LoadFrom( $path );

		$id                 = $date . "=" . $title;
		$source             = substr(  $source, 0, strlen( $source ) - 1 );
		$session            = substr( $session, 7, strlen( $source )     );

		$modified           = \Input::Filter( $article->getModified()          );
		$video_type         = \Input::Filter( $article->getVideoType()         );
		$video_code         = \Input::Filter( $article->getVideoCode()         );
		$h1                 = \Input::Filter( $article->getTitle()             );
		$address            = \Input::Filter( $article->getAuthor()            );
		$section1           = $article->getSection();
		$section2           = $article->getSectionReferences();

		$section1           = str_replace( "\r\n", "", $section1 );
		$section1           = str_replace(   "\n", "", $section1 );
		$section1           = str_replace(   "\r", "", $section1 );
		$section1           = str_replace(   "\t", "", $section1 );

		$s1 = substr( $section1, 0, 10 );
		$s2 = substr( $section2, 0, 10 );

		$sql = "Articles_Replace( '0', '$modified', '$id', '$source', '$category', '$subject', '$date', '$session', '$title', '$video_type', '$video_code', '$h1', '$address', '$section1', '$section2' )";

		if ( true )
		{
			if ( is_array( DBi_callProcedure( DB, $sql, new NullPrinter() ) ) )
			{
				$sql = "Articles_Replace( '0', '$modified', '$id', '$source', '$category', '$subject', '$date', '$session', '$title', '$video_type', '$video_code', '$h1', '$address', '$s1', '$s2' )";
			
				$out->println( $sql );
			}
			else
			{
				$out->println( "Error connection to DB!!!" );
			}
		}
		else
		{
			$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, new NullPrinter() );
			if ( "OK" == $result->status )
			{
				$sql = "Articles_Replace( '0', '$modified', '$id', '$source', '$category', '$subject', '$date', '$session', '$title', '$video_type', '$video_code', '$h1', '$address', '$s1', '$s2' )";
			
				$out->println( $sql );
			}
			else
			{
				$out->println( "Error connection to DB!!!" );
			}
		}
	}
}





