<?php

class ArticleSummaryElement extends Element
{
	function __construct( $group, $category, $date, $title, $filename, $width, $height )
	{
		$group = $group ? $group : "articles";
	
		$this->category   = str_replace( ' ', '_', $category );
	
		$this->filepath   = $this->GenerateFilePath     ( $category, $date, $title, $filename );
		$this->videopath  = $this->GenerateVideoPath    ( $category, $date, $title, $filename );
		$this->resourceID = $this->GenerateResourceID   ( $category, $date, $title, $filename );
		$this->thumbnail  = $this->GenerateThumbnailPath( $category, $date, $title, $filename, $width, $height );
		$this->width      = $width;
		$this->height     = $height;

		$this->title     = $title;
		$this->summary   = "";
		
		$this->isVideo = file_exists( $this->videopath );

		if ( file_exists( $this->filepath ) )
		{
			$htm = Input::unidecode( file_get_contents( $this->filepath, false ) );
			
			if ( $htm )
			{
				$dom = new DOMDocument();
				$dom->loadHTML( $htm );

				$this->title   = $this->ExtractElement(      "h1", $dom );
				$this->summary = $this->ExtractElement( "summary", $dom );
			}
			else
			{
				$this->title   = "File appears empty!";
				$this->summary = "";
			}
		}
		else
		{
			$this->title   = "File does not exist";
			$this->summary = "";
		}
	}
	
	function render( $out )
	{
		$out->println( $this->createElement() );
	}
	
	function createElement()
	{
		$category    = $this->category;
		$article_id  = $this->resourceID;
	
		$filepath    = $this->filepath;
		$videopath   = $this->videopath;
		$link_text   = $this->isVideo ? "Watch video" : "Read more";
		$cls         = $this->isVideo ? "video" : "article";
		$play        = $this->isVideo ? "<div class='play'></div>" : "";

		$at_category = $category ? "category=$category&" : "";
		$at_article  = "article_id=$article_id";

		$href        = "./article/?$at_category$at_article";

		return

"
<div data-class='ArticleSummaryElement' class='article_summary' data-filepath='$filepath' data-videopath='$videopath'>
	<a href='$href'>
		<div class='relative'>
			$play
			<img width='$this->width' height='$this->height' alt='Thumbnail' src='$this->thumbnail'>
		</div>
	</a>
	<div class='mtop20'>
		<p style='min-height:30px;'>
			<a href='$href'>
				<span class='title dark uc'>$this->title</span>
			</a>
		</p>
		<p>
		$this->summary
		</p>
		<p>
			<a class='red' href='$href'>$link_text &nbsp; &nbsp; &rarr;</a>
		</p>
	</div>
</div>
";
	}

	function ExtractElement( $tagName, $dom )
	{
		$ret = null;
		{
			$elements = $dom->getElementsByTagName( $tagName );

			if ( 0 < count( $elements ) )
			{
				foreach ( $elements as $element )
				{
					$ret = $element->nodeValue;
					break;
				}
			}
		}
		return $ret;
	}

	function ExtractElementAttribute( $tagName, $attribute, $dom )
	{
		$ret = "";
		{
			$elements = $dom->getElementsByTagName( $tagName );

			if ( 0 < count( $elements ) )
			{
				foreach ( $elements as $element )
				{
					$ret = $element->getAttribute( $attribute );
					break;
				}
			}
			else
			{
				$ret = "Could not find tag!";
			}
		}
		return $ret;
	}

	function HasElement( $tagName, $dom )
	{
		$elements = $dom->getElementsByTagName( $tagName );

		return (0 != $elements->length);
	}

	function GeneratePageId( $filepath )
	{
		$article_path = dirname( $filepath );
		$short_path   = str_replace( RESOURCES_PATH, "", $article_path );
		$page_id      = Page::GeneratePageId( $short_path );
		$resource_id  = str_replace( "index", "", $page_id );

		return $resource_id;
	}
	
	static function GenerateFilePath( $category, $date, $title, $filename )
	{
		$articles_path = ARTICLES_PATH;

		$encoded_category = str_replace( ' ', '_', $category );
		$encoded_date     = str_replace( ' ', '_', $date     );
		$encoded_title    = str_replace( ' ', '_', $title    );

		$fs_category = $encoded_category ? $encoded_category . "/"              : "";
		$fs_title    = $encoded_title    ? $encoded_title    . "/"              : "";

		return $articles_path . "/$fs_category$date-$fs_title" . $filename;
	}
	
	static function GenerateVideoPath( $category, $date, $title, $filename )
	{
		$video_dir = VIDEO_DIR;
	
		$encoded_category = str_replace( ' ', '_', $category );
		$encoded_date     = str_replace( ' ', '_', $date     );
		$encoded_title    = str_replace( ' ', '_', $title    );

		$fs_category = $encoded_category ? $encoded_category . "/"              : "";
	
		$resource_id      = "$fs_category$encoded_date-$encoded_title";
	
//		$fs_category = $encoded_category ? $encoded_category . "/"              : "";
//		$fs_date     = $encoded_date     ? str_replace( '-', '/', $date ) . "/" : "";
//		$fs_title    = $encoded_title    ? $encoded_title    . "/"              : "";
		
		return $video_dir . "/articles/$resource_id/MP4/$resource_id.mp4";
	}

	static function GenerateResourceID( $category, $date, $title, $filename )
	{
		$video_dir = VIDEO_DIR;
	
		$encoded_category = str_replace( ' ', '_', $category );
		$encoded_date     = str_replace( ' ', '_', $date     );
		$encoded_title    = str_replace( ' ', '_', $title    );
	
		$resource_id      = "$encoded_date-$encoded_title";

		return $resource_id;
	}

	static function GenerateThumbnailPath( $category, $date, $title, $filename, $width, $height )
	{
		$resource_ID = ArticleSummaryElement::GenerateResourceID( $category, $date, $title, $filename );
	
		$encoded_category = str_replace( ' ', '_', $category );
		$encoded_date     = str_replace( ' ', '_', $date     );
		$encoded_title    = str_replace( ' ', '_', $title    );

		$encoded_category = $encoded_category ? "/" . $encoded_category : "";
	
		//$thumbnail = VIDEO_HOST . "/" . APP_NAME . "/articles$encoded_category/$resource_ID/thumbnail-" . $width . "x" . $height . ".png";
		$thumbnail = VIDEO_HOST . "/" . APP_NAME . "/articles$encoded_category/$resource_ID/image.png";

		return $thumbnail;
	}


}

?>