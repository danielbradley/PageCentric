<?php
//	Copyright (c) 2012 Daniel Robert Bradley. All rights reserved.
?>
<?php

class RadioGroup extends Radio
{
	function __construct( $title, $attributes, $selected, $options )
	{
		parent::__construct( $attributes, $selected, $options );
	
		$this->title = $title;
	}
	
	function render( $out )
	{
		$out->inprint( "<fieldset class='radiogroup'><legend>$this->title</legend>" );
		{
			parent::render( $out );
		}
		$out->outprint( "</fieldset>" );
	}
}

?>