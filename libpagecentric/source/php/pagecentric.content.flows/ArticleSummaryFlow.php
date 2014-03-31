<?php

class ArticleSummaryFlow extends View
{
	function __construct( $tuples )
	{
		$this->elements = array();
		
		foreach ( $tuples as $tuple )
		{
			if ( $tuple instanceof ArticleInfo )
			{
				$info = $tuple;

				$subject  = $info->category;
				$date     = $info->pubdate;
				$title    = $info->titlecode;
				$filename = $info->filename;
			}
			else
			{
				$subject  = array_get( $tuple, "subject" );
				$date     = array_get( $tuple, "date"    );
				$title    = array_get( $tuple, "title"   );
				$filename = "content.htm";
			}
		
			$this->elements[] = $this->createElement( $subject, $date, $title, $filename, 300, 169 );
		}
	}

	function createElement( $subject, $date, $title, $filename, $width, $height )
	{
		return new ArticleSummaryElement( $subject, $date, $title, $filename, 300, 169 );
	}

	function render( $out )
	{
		foreach ( $this->elements as $element )
		{
			$out->inprint( "<div class='span span4 mtop20' style='height:420px;'>" );
			{
				$element->render( $out );
			}
			$out->outprint( "</div>" );
		}
	}
}

?>