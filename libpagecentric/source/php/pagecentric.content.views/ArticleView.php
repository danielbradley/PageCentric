<?php

class ArticleView extends View
{
	function __construct( $page, $width, $height, $hidden = true )
	{
		$this->width        = $width;
		$this->height       = $height;
		$this->hidden       = $hidden;
		$this->group        = $page->getRequest( "group"      );
		$this->category     = $page->getRequest( "category"   );
		$this->article_id   = $page->getRequest( "article_id" );

		$this->group        = $this->group ? $this->group : "articles";

		$this->imageURL     = VIDEO_HOST . "/" . APP_NAME . "/" . "$this->group/$this->category/$this->article_id/image.png";
		$this->resourcePath = $this->generateResourcePath( BASE_PATH, $this->group, $this->category, $this->article_id );

		if ( file_exists( $this->resourcePath ) )
		{

			$this->article  = Article::loadFrom( $this->resourcePath );
			$this->title    = $this->article->getTitle();
			$this->summary  = $this->article->getSummary();
			$this->content  = $this->article->getContent();
			$this->modified = $this->article->getModified();
		}
		else
		{
			$this->title    = "Could not find file! -- $this->resourcePath";
			$this->content  = "Could not find file! -- $this->resourcePath";
			$this->summary  = "Could not find file! -- $this->resourcePath";
			$this->modified = "0000-00-00";
		}
	}

	function getModified()
	{
		return $this->modified;
	}

	function getTitle()
	{
		return $this->title;
	}

	function getSummary()
	{
		return $this->summary;
	}
	
	function getImageURL()
	{
		return $this->imageURL;
	}

	function getResourcePath()
	{
		return $this->resourcePath;
	}

	function render( $out )
	{
		$appname   = APP_NAME;
		$videohost = VIDEO_HOST;
		$hidden    = $this->hidden ? "hidden" : "";
	
		$out->inprint( "<div data-class='ArticleView' class='Article $hidden' id='article' data-category='$this->category' data-article-id='$this->article_id' data-host='$videohost' data-app='$appname' data-width='$this->width' data-height='$this->height'>" );
		{
			$out->println( $this->content );
		}
		$out->outprint( "</div>" );
		$out->println( "&nbsp;" );
	}

	static function generateResourcePath( $base_path, $group, $category, $article_id )
	{
		$resource_path  = $base_path;
		$resource_path .= $group      ? "/$group"      : "";
		$resource_path .= $category   ? "/$category"   : "";
		$resource_path .= $article_id ? "/$article_id" : "";
		$resource_path .= "/content.htm";
		
		return $resource_path;
	}
}