<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

include_once( LIBOPENPAGE . "/HTML/Menu.php" );

class BootstrapDropdownButton extends Menu
{
	function __construct( $items, $attributes )
	{
		$this->attributes = $attributes;
		$this->items = $items;
		$this->label = "";
	}

	function setLabel( $label )
	{
		$this->label = $label;
	}

	function render( $out )
	{
		$out->inprint( "<a $this->attributes data-toggle='dropdown' href='#'>" );
		{
			$out->println( "<span>$this->label</span>" );
			$out->println( "<span class='caret'></span>" );
		}
		$out->outprint( "</a>" );
			
		$out->inprint( "<ul class='dropdown-menu'>" );
		{
			foreach ( $this->items as $key => $url )
			{
				if ( "-" == substr( $key, 0, 1 ) )
				{
					$out->println( "<li class='divider'></li>" );
				}
				else
				{
					$out->println( "<li><a href='$url'>$key</a></li>" );
				}
			}
		}
		$out->outprint( "</ul>" );
	}
}

?>