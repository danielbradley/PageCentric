<?php

class ContentView extends View
{
	function __construct( $page, $content_path = CONTENT_PATH )
	{
		$this->pageId      = $page->getPageId();
		$this->contentPath = $content_path;
	}

	function render( $out )
	{
		$htm = Content::getHTMFor( $this->pageId, "content", $this->contentPath );
	
		$out->inprint( "<div data-class='ContentView' id='article' data-page-id='$this->pageId'>" );
		{
			$out->println( $htm );
			$out->println( "&nbsp;" );
		}
		$out->outprint( "</div>" );
	}
}