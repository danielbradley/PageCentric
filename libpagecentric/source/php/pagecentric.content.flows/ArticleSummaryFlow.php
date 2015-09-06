<?php

class ArticleSummaryFlow extends View
{
	function __construct( $articles )
	{
		$this->elements = array();
		
		foreach ( $articles as $article )
		{
			if ( $article instanceof Article )
			{
				$group    = $article->getGroup();
				$subject  = $article->getCategory();
				$date     = $article->getPubdate();
				$title    = $article->getTitlecode();
				$filename = $article->getFilename();
			}
			else
			{
				$subject  = array_get( $article, "subject" );
				$date     = array_get( $article, "date"    );
				$title    = array_get( $article, "title"   );
				$filename = "content.htm";
			}
		
			$this->elements[] = $this->createElement( $group, $subject, $date, $title, $filename, 300, 169 );
		}
	}

	function createElement( $group, $subject, $date, $title, $filename, $width, $height )
	{
		return new ArticleSummaryElement( $group, $subject, $date, $title, $filename, 300, 169 );
	}

	function render( $out )
	{
		$out->inprint( "<div data-type='ArticleSummaryFlow'>" );
		{
			foreach ( $this->elements as $element )
			{
				$out->inprint( "<div class='span span4 mtop20'>" );
				{
					$element->render( $out );
				}
				$out->outprint( "</div>" );
			}
			
			$this->renderMore( $out );
		}
		$out->outprint( "</div>" );
	}

	function renderMore( $out )
	{}
}

?>