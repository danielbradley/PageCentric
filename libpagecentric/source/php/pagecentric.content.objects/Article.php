<?php

class Article
{
//	var title;
//	var summary;
//	var htm;

	static function LoadUsing( $category, $resource_id, $base = ARTICLES_PATH )
	{
		$filepath = "$base/$category/$resource_id/content.htm";
	
		return Article::LoadFrom( $filepath );
	}

	static function LoadFrom( $filepath )
	{
		$article = null;
	
		if ( file_exists( $filepath ) )
		{
			$article = new Article( $filepath );
		}
		return $article;
	}

	function __construct( $filepath )
	{
		$this->htm      = Input::unidecode( file_get_contents( $filepath, false ) );

		$this->video    = string_contains( $this->htm, "<video" );

		$this->modified = date( 'Y-m-d', filemtime( $filepath ) );

		$this->info = ArticleInfo::Decode( $filepath );
			
		if ( $this->htm )
		{
			libxml_use_internal_errors(true);

			$dom = new DOMDocument();
			$dom->loadHTML( $this->htm );

			$h1      = ArticleSummaryElement::ExtractElement(      "h1", $dom );
			$author  = ArticleSummaryElement::ExtractElement( "address", $dom );
			$summary = ArticleSummaryElement::ExtractElement( "summary", $dom );
			$views   = ArticleSummaryElement::ExtractElementAttribute( "address", "data-views", $dom );

			$summary = str_replace( "<p>", "", str_replace( "</p>", "", str_replace( "\n", "", str_replace( "\t", "", $summary ) ) ) );

			$this->title      = Input::Filter( $h1      );
			$this->author     = Input::Filter( $author  );
			$this->summary    = Input::Filter( $summary );
			$this->views      = Input::Filter( $views   );
			$this->videoType  = Input::Filter( self::GetAttribute( $dom, "video", "data-target" ) );
			$this->videoCode  = Input::Filter( self::GetAttribute( $dom, "video", "data-code"   ) );
			$this->section    = Input::Filter( self::GetElement( $dom, "section" ) );
			$this->sectionRef = Input::Filter( self::GetElement( $dom, "section", "references" ) );
			$this->sectionAns = Input::Filter( self::GetElement( $dom, "section", "answer"     ) );
		}
		else
		{
			$this->title   = "File appears empty!";
			$this->summary = "";
		}
	}

	static function GetElement( $dom, $tagName, $cls = "" )
	{
		$value    = "";
		$elements = $dom->getElementsByTagName( $tagName );

		foreach ( $elements as $element )
		{
			if ( $cls )
			{
				if ( $element->hasAttributes() )
				{
					if ( string_contains( $element->attributes->getNamedItem( "class" )->value, $cls ) )
					{
						//$value = $element->nodeValue;
						$value = self::InnerHTML( $dom, $element );
						break;
					}
				}
			}
			else
			{
				$value = self::InnerHTML( $dom, $element );
				break;
			}
		}
		return $value;
	}

	static function GetElementById( $dom, $tagName, $id )
	{
		$value    = "";
		$elements = $dom->getElementsByTagName( $tagName );

		foreach ( $elements as $element )
		{
			if ( $id )
			{
				if ( $element->hasAttributes() )
				{
					if ( $id == $element->attributes->getNamedItem( "id" ) )
					{
						$value = self::InnerHTML( $dom, $element );
						break;
					}
				}
			}
		}
		return $value;
	}




	static function GetAttribute( $dom, $tagName, $attribute )
	{
		$value    = "";
		$elements = $dom->getElementsByTagName( $tagName );
		
		if ( 1 == $elements->length )
		{
			if ( $elements->item( 0 )->hasAttributes() )
			{
				$value = $elements->item( 0 )->attributes->getNamedItem( $attribute )->value;
			}
		}
		return $value;
	}

	function getGroup()
	{
		return $this->info->group;
	}

	function getCategory()
	{
		return $this->info->category;
	}

	function getResourceID()
	{
		return $this->info->resourceID;
	}

	function getArticleID()
	{
		return $this->info->resourceID;
	}
	
	function getPubDate()
	{
		return $this->info->pubdate;
	}
	
	function getTitleCode()
	{
		return $this->info->titlecode;
	}

	function getFilepath()
	{
		return $this->info->filepath;
	}

	function getDirpath()
	{
		return $this->info->dirpath;
	}

	function getFilename()
	{
		return $this->info->filename;
	}

	function getImagePath()
	{
		return $this->info->imagePath;
	}

	function getImageURL()
	{
		return $this->info->imageURL;
	}

	function getContent()
	{
		return $this->htm;
	}
	
	function getModified()
	{
		return $this->modified;
	}

	function getTitle()
	{
		return $this->title;
	}

	function getAuthor()
	{
		return $this->author;
	}

	function getSummary()
	{
		return $this->summary;
	}

	function getViews()
	{
		return $this->views;
	}

	function getVideoType()
	{
		return $this->videoType;
	}

	function getVideoCode()
	{
		return $this->videoCode;
	}

	function getSection()
	{
		return $this->section;
	}

	function getSectionReferences()
	{
		return $this->sectionRef;
	}

	function getSectionAnswer()
	{
		return $this->sectionAns;
	}
	
	function hasImage()
	{
		$ret = false;

		$img = $this->getDirpath() . "/image.png";

		if ( file_exists( $img ) )
		{
			$ret = true;
		}
		else
		{
			//error_log( "Article::hasImage: Could not find: $img" );
		}

		return $ret;
	}

	function hasVideo()
	{
		return $this->video;
	}

	function InnerHTML( $dom, $element )
	{
		$innerHTML = "";

		foreach ( $element->childNodes as $child )
		{
			$innerHTML .= $dom->saveHTML( $child );
		}
		
		return $innerHTML;
	}
}

?>