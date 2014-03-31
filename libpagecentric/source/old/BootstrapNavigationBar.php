<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

include_once( LIBOPENPAGE . "/HTML/Menu.php" );

class BootstrapNavigationBar extends Menu
{
	function setSeparator( $separator )
	{
		$this->separator = $separator;
	}

	function setLinkAttributes( $attributes )
	{
		//data-toggle='modal'
 		$this->attr = $attributes;
	}

	function render( $out )
	{
			$attr = isset( $this->attr ) ? $this->attr : "";
	
			$out->println( "<ul id='$this->id' class='nav'>" );
			$out->indent();
			{
				$first = "first";
				foreach ( $this->items as $id => $page )
				{
					$attributes = strpos( $page, "://" ) ? "target='_blank'" : $attr;
				
					$script_filename      = $_SERVER["SCRIPT_NAME"];
					$script_filename_base = str_replace( "index.php", "", $script_filename );
					$bits = explode( '?', $page );
					$link = $bits[0];
					
					if ( !$first && isset( $this->separator ) ) $out->println( "<li class='separator'><span>$this->separator</span></li>" );
					
					if ( "" == $link )
					{
						$out->println( "<li class='active'><a $attributes>$id</a></li>" );
					}
					else if ( $script_filename_base == $link )
					{
						$out->println( "<li class='active'><a $attributes>$id</a></li>" );
					}
					else if ( ("/" == $page) && ("/index.php" != $script_filename) )
					{
						$out->println( "<li><a $attributes href='$page'>$id</a></li>" );
					}
					else if ( FALSE !== strpos( $script_filename_base, $link ) )
					{
						$out->println( "<li class='active'><a $attributes href='$page'>$id</a></li>" );
						//$out->println( "<li $first><span>$id</span></li>" );
					}
					else
					{
						//$out->println( "<li $first><span>$id</span></li>" );
						$out->println( "<li><a $attributes href='$page'>$id</a></li>" );
					}
					$first = "";
				}
			}
			$out->outdent();
			$out->println( "</ul>" );
	}



}

?>