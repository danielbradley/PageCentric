<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class BootstrapTextInputPrepend
{
	function __construct( $iv, $label, $name, $attributes, $prepend, $append )
	{
		$this->label      = $label;
		$this->name       = $name;
		$this->attributes = $attributes;
		$this->prepend    = $prepend;
		$this->append     = $append;
		$this->value      = array_get( "$name", $iv->request );

		$this->level      = $iv->value( "$name" ) ? " warning" : "";
		$this->help       = "";
		$this->example    = "";
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

	function render( $out )
	{
		$l = ("" != $this->label) ? true : false;
		$h = ("" != $this->help)  ? true : false;

		$out->inprint( "<div class='control-group$this->level'>" );
		{
			//$out->inprint( "<label class='control-label'>" );
			{
				//$out->inprint( "<div class='controls'>" );
				{
					if ( $l ) $out->println( "<span>$this->label</span><br>" );

					$input_prepend = ("" != $this->prepend ) ? "input-prepend" : "";
					$input_append  = ("" != $this->append  ) ? "input-append"  : "";

					$out->inprint( "<div class='$input_prepend $input_append'>" );
					{
						$prepend = $this->prepend ? "<span class='add-on'>$this->prepend</span>" : "";
						$append  = $this->append  ? "<span class='add-on'>$this->append</span>"  : "";

						$out->println( "$prepend<input type='text' $this->attributes name='$this->name' value='$this->value'>$append" );
					}
					$out->outprint( "</div>" );
					
					if ( $this->level )
					{
						if ( $h ) $out->println( "<span class='inline-help'>$this->help</span>" );
					}
					else
					{
						$out->println( "<span class='inline-help'>$this->example</span>" );
					}
				}
				//$out->outprint( "</div>" );
			}
			//$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}

?>