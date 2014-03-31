<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class TextArea
{
	function __construct( $iv, $label, $name, $attributes )
	{
		$this->label      = $label;
		$this->name       = $name;
		$this->attributes = $attributes;
		$this->value      = array_get( "$name", $iv->request );

		$this->level      = $iv->value( $name ) ? "warning" : "";
		$this->help       = "";
		$this->example    = "";
		$this->placeholder = HTMLUtils::extractAttribute( $attributes, "placeholder" );
	}

	function setLevel( $level )
	{
		$this->level = $level;
	}

	function setHelp( $help )
	{
		$this->help = $help;
	}

	function setExample( $example )
	{
		$this->example = $example;
	}

	function setValue( $value )
	{
		$this->value = $value;
	}

	function render( $out )
	{
		$pl = HTMLUtils::decideIEPlaceHolder( $this->value, $this->placeholder );

		$out->inprint( "<label class='$this->level'>" );
		{
			if ( $this->label ) $out->println( "<span>$this->label</span><br>" );

			if ( null !== $pl )
			{
				$out->inprint( "<div style='position:relative'>" );
				{
					$out->println( "<span data-class='placeholder' class='textarea_placeholder'>$pl</span>" );
					$out->printPRE( "<textarea $this->attributes name='$this->name' data-placeholder='$this->placeholder'>\n" );
					$out->printPRE( $this->value );
					$out->printPRE( "</textarea>\n" );
				}
				$out->outprint( "</div>" );
			}
			else
			{
				$out->println( "<textarea $this->attributes name='$this->name'>$this->value</textarea>" );
			}
			
			if ( $this->level )
			{
				$out->println( "<span class='inline-help'>$this->help</span>" );
			}
			else
			{
				$out->println( "<span class='inline-help'>$this->example</span>" );
			}
		}
		$out->outprint( "</label>" );
	}
}

?>