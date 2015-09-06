<?php

/*

An article is found by locating a .htm file underneath the RESOURCES PATH.

./English/2013/01/01/English_101/content.htm

Category = English
Date     = 2014-01-01
Title    = English 101





 */
class ArticlesView extends View
{
	function __construct( $page, $cls )
	{
		$this->cls   = $cls;
		$this->files = Files::recurseFiles( ARTICLES_PATH, ".htm" );
		$this->elements = array();

		//
		//	Image sizes are based on a 16:9 ratio.
		//
		switch ( $cls )
		{
		case "span4":
			$width  = "380";
			$height = "214";
			break;

		case "span3":
			$width  = "300";
			$height = "169";
			break;
		}
		
		foreach ( $this->files as $filepath )
		{
			$info = ArticleInfo::Decode( $filepath );
			
			$this->elements[] = new ArticleSummaryElement( $info->group, $info->category, $info->pubdate, $info->titlecode, $info->filename, $width, $height );
		}
	}

	function render( $out )
	{
		$out->inprint( "<div data-class='ArticlesView'>" );
		{
			if ( 0 < count( $this->elements ) )
			{
				$i = 0;
			
				foreach ( $this->elements as $element )
				{
					if ( 0 == ($i % 2) ) $out->inprint( "<div class='row'>" );
				
					$out->inprint( "<div class='span $this->cls mbot30 relative'>" );
					{
						$element->render( $out );
					}
					$out->outprint( "</div>" );
					
					$i++;
					
					if ( 0 == ($i % 2) ) $out->outprint( "</div>" );
				}
				
				if ( 1 == ($i % 2) ) $out->outprint( "</div>" );
			}
			else
			{
				$out->println( "No articles available..." );
			}
		}
		$out->outprint( "</div>" );
	}
}