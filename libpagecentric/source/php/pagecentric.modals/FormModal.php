<?php

class FormModal extends View
{
	function __construct( $view, $title )
	{
		$this->view = $view;
		$this->title = $title;
	}

	function render( $out )
	{
		$out->inprint( "<div class='FormModal relative'>" );
		{
			$out->inprint( "<a data-toggle='modal' href='#'>" );
			{
				$out->println( "<div class='absolute close'>&cross;</div>" );
			}
			$out->outprint( "</a>" );

			if ( isset( $this->title ) )
			{
				$out->inprint( "<div class='section header'>" );
				{
					$out->println( "<div class='form_title'>$this->title</div>" );
				}
				$out->outprint( "</div>" );
			}
		
			$out->inprint( "<div class='section body'>" );
			{
				$this->view->render( $out );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div>" );
	}
}