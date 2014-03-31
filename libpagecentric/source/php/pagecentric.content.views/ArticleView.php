<?php

class ArticleView extends View
{
	function __construct( $page, $width, $height )
	{
		$this->width      = $width;
		$this->height     = $height;
		$this->category   = $page->getRequest( "category" );
		$this->article_id = $page->getRequest( "article_id" );

		$resource_path    = $this->generateResourcePath( ARTICLES_PATH, $this->category, $this->article_id );

		if ( file_exists( $resource_path ) )
		{
			$this->content = file_get_contents( $resource_path );
		}
		else
		{
			$this->content = "Could not find file! -- $resource_path";
		}
	}

	function render( $out )
	{
		$appname   = APP_NAME;
		$videohost = VIDEO_HOST;
	
		$out->inprint( "<div data-class='ArticleView' class='Article' id='article' data-category='$this->category' data-article-id='$this->article_id' data-host='$videohost' data-app='$appname' data-width='$this->width' data-height='$this->height'>" );
		{
			$out->println( $this->content );
		}
		$out->outprint( "</div>" );
	}

	static function generateResourcePath( $articles_path, $category, $article_id )
	{
		$resource_path  = $articles_path;
		$resource_path .= $category   ? "/$category"   : "";
		$resource_path .= $article_id ? "/$article_id" : "";
		$resource_path .= "/content.htm";
		
		return $resource_path;
	}
}