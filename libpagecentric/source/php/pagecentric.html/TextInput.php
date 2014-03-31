<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.html/HTMLUtils.php" );

class TextInput
{
	function __construct( $iv, $label, $name, $attributes )
	{
		$this->label       = $label;
		$this->name        = $name;
		$this->attributes  = $attributes ? " $attributes" : "";
		$this->value       = array_get( $iv->request, "$name" );

		$this->level           = $iv->value( "$name" ) ? " warning" : "";
		$this->help            = "";
		$this->example         = "";
		$this->type            = "text";
		$this->placeholder     = HTMLUtils::extractAttribute( $attributes, "placeholder" );
		$this->dataPlaceholder = HTMLUtils::extractAttribute( $attributes, "data-placeholder" );
	}

	function setLevel( $level )
	{
		$this->level = " " . $level;
	}

	function setHelp( $help )
	{
		$this->help = $help;
	}

	function setExample( $example )
	{
		$this->example = $example;
	}

	function setIV( $iv )
	{
		$this->level = $iv->value( "$this->name" ) ? " warning" : "";
	}

	function render( $out )
	{
		$l = ("" != $this->label) ? true : false;
		$h = ("" != $this->help)  ? true : false;

		if ( $this->dataPlaceholder )
		{
			$pl = $this->dataPlaceholder;
		}
		else
		{
			$pl = HTMLUtils::decideIEPlaceHolder( $this->value, $this->placeholder );
		}

		$out->inprint( "<label class='$this->level'>" );
		{
			if ( $l ) $out->println( "<tt>$this->label</tt>" );

			if ( null !== $pl )
			{
				$out->inprint( "<span style='position:relative'>" );
				{
					$out->println( "<input$this->attributes type='$this->type' name='$this->name' value='$this->value' data-placeholder='$this->placeholder'>" );
					$out->println( "<span data-class='placeholder' class='placeholder'>$pl</span>" );
				}
				$out->outprint( "</span>" );
			}
			else
			{
				$out->println( "<input$this->attributes type='$this->type' name='$this->name' value='$this->value'>" );
			}
				
			if ( $this->level && $this->help )
			{
				if ( $h ) $out->println( "<span class='help-inline'>$this->help</span>" );
			}
			else if ( $this->example )
			{
				$out->println( "<span class='help-inline'>$this->example</span>" );
			}
		}
		$out->outprint( "</label>" );
	}
}

?>