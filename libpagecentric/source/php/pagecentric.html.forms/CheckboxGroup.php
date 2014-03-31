<?php
//	Copyright (c) 2012 Daniel Robert Bradley. All rights reserved.
?>
<?php

class CheckboxGroup extends Element
{
	//	Usage:
	//	new CheckboxGroup( "Select your favourite cities", "name='city'", $array, $options ); 

	function __construct( $title, $name, $attributes, $tuples, $options )
	{
		$this->title      = $title;
		$this->name       = $name;
		$this->attributes = $attributes;
		$this->options    = $options;

		$this->selected = array();
		foreach ( $tuples as $tuple )
		{
			$key = str_replace( '_', ' ', array_get( $tuple, $this->name ) );
			$key = str_replace( '&#039;', "'", $key );
			$this->selected["$key"] = "";
		}
	}
	
	function render( $out )
	{
		$out->inprint( "<div class='checkboxgroup'><div style='margin-bottom:10px;'>$this->title</div>" );
		{
			foreach( $this->options as $text => $value )
			{
				if ( "" == $value ) $value = $text;

				$checked = array_key_exists( $value, $this->selected ) ? "checked" : "";
				
				$out->inprint( "<div class='relative' style='padding-right:50px;'>" );
				{
					$out->println( "<span>$text</span>" );

					$out->inprint( "<div class='absolute' style='right:0;top:0;'>" );
					{
						$out->println( "<input type='checkbox' $this->attributes name=\"$value\" value=\"$this->name\" $checked>" );
					}
					$out->outprint( "</div>" );
				}
				$out->outprint( "</div>" );
			}
		}
		$out->outprint( "</div>" );
	}
}

?>