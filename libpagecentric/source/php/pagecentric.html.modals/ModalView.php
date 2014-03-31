<?php

class ModalView extends View
{
	function __construct( $cls, $label, $view, $options )
	{
		$this->cls      = $cls;		// CSS class applied to top-level div.
		$this->label    = $label;	// label name used to generate div id.
		$this->view     = $view;		// view to be displayed within modal.
		$this->options  = $options;	// pass options, i.e. "visible"

		$this->hide     = array_get( $this->options, "visible" ) ? False : True;

		$this->position = "";
		if ( array_key_exists( "position", $this->options ) )
		{
			$this->position = " position:" . array_get( $this->options, "position" );
		}
	}

	function setTopView( $view )
	{
		$this->top = $view;
	}

	function setHide( $boolean )
	{
		$this->hide = $boolean;
	}

	function render( $out )
	{
		$cls   = $this->cls;
		$id    = $this->toId( $this->label );

		$hide  = $this->hide ? "hide" : "";

		$noesc      = array_get(   "noescape", $this->options ) ? "data-keyboard='false'"   : "";
		$noclk      = array_get(    "noclick", $this->options ) ? "data-backdrop='static'"  : "";
		$clkhome    = array_get(  "clickhome", $this->options ) ? "data-action='clickhome'" : "";
		$closevideo = array_get( "closevideo", $this->options ) ? true : false;
		
		$out->inprint( "<div data-class='modal' class='$cls modal $hide' $noesc $noclk id='$id' style='$this->position'>" );
		{
			$out->inprint( "<div class='relative'>" );
			{
				$out->inprint( "<div class='modal_top'>" );
				{
					if ( isset( $this->top ) )
					{
						$this->top->render( $out );
					}
//				
//					if ( $closevideo )
//					{
//						$out->println( "<a $clkhome data-action='closevideo' data-dismiss='modal' href='#'><div class='close_button'>&cross;</div></a>" );
//					}
//					else
//					{
//						$out->println( "<a $clkhome data-toggle='modal' href='#'><div class='close_button'></div></a>" );
//					}
				}
				$out->outprint( "</div>" );

				$out->inprint( "<div class='modal_content'>" );
				{
					$this->view->render( $out );
				}
				$out->outprint( "</div>" );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div>" );
	}

	function toId( $title )
	{
		return str_replace( '&amp;', 'and', strtolower( str_replace( ',', '', str_replace( ' ', '_', str_replace( '/', '_', $title ) ) ) ) );
	}
}