<?php

class ContentView extends View
{
	function __construct( $page )
	{
		$this->pageId = $page->getPageId();
	}

	function render( $out )
	{
		$htm = Content::getHTMFor( $this->pageId, "content" );
	
		$out->inprint( "<div data-class='ContentView'>" );
		{
			$out->println( $htm );
		}
		$out->outprint( "</div>" );
	}
}