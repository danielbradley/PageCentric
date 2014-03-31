<?php

class YesNoForm extends Form
{
	function __construct()
	{
		$this->hidden = array();
	}

	function render( $out )
	{
		$out->inprint( "<form method='post'>" );
		{
			$out->inprint( "<div>" );
			{
				foreach ( $this->hidden as $name => $value )
				{
					$out->println( "<input type='hidden' name='$name' value='$value'>" );
				}
			}
			$out->outprint( "</div>" );
		
			$out->inprint( "<div class='row'>" );
			{
				$out->inprint( "<div class='span'>" );
				{
					$out->println( "<input class='red span2 button' type='submit' name='submit' value='Yes'>" );
				}
				$out->outprint( "</div>" );

				$out->inprint( "<div style='float:right'>" );
				{
					$out->println( "<input class='gray span2 button' type='submit' name='' value='No'>" );
				}
				$out->outprint( "</div>" );
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</form>" );
	}
	
	function addHidden( $name, $value )
	{
		$this->hidden[$name] = $value;
	}
}