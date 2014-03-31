<?php
//	Copyright (c) 2012 Daniel Robert Bradley. All rights reserved.
?>
<?php

class SelectGroup extends Element
{
	//	Usage:
	//	new CheckboxGroup( "Select your favourite cities", "name='city'", $array, $options ); 

	function __construct( $title, $attributes, $selected, $options )
	{
		$this->title      = $title;
		$this->attributes = $attributes;
		$this->selected   = $selected;
		$this->options    = $options;
	}
	
	function render( $out )
	{
		$out->inprint( "<div class='selectgroup'><p>$this->title</p>" );
		{
			foreach( $this->options as $text => $value )
			{
				if ( "" == $value ) $value = $text;

				$checked = array_key_exists( $value, $this->selected ) ? "checked" : "";
				
				$out->println( "<input type='checkbox' $this->attributes value='$value' $checked>$text</input><br>" );
			}
		}
		$out->outprint( "</div>" );
	}
}

?>