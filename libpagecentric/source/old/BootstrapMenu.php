<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

include_once( LIBOPENPAGE . "/HTML/Menu.php" );

class BootstrapMenu extends Menu
{
	function __construct( $id, $items )
	{
		$this->id    = $id;
		$this->items = $items;
		$this->horizontal  = "MenuHorizontal";
		$this->vertical    = "";
		$this->left        = "MenuLeft";
		$this->right       = "";
	}

	function setHorizontal()
	{
		$this->horizontal = "MenuHorizontal";
		$this->vertical   = "";
	}
	
	function setVertical()
	{
		$this->horizontal = "";
		$this->vertical   = "MenuVertical";
	}
	
	function setLeft()
	{
		$this->left        = "MenuLeft";
		$this->right       = "";
	}
	
	function setRight()
	{
		$this->left        = "";
		$this->right       = "MenuRight";
	}
	
	function render( $out )
	{
		$first = "first";
	
		foreach ( $this->items as $id => $page )
		{
			$script_filename      = $_SERVER["SCRIPT_NAME"];
			$script_filename_base = str_replace( "index.php", "", $script_filename );
			$bits = explode( '?', $page );
			$link = $bits[0];
			
//					$out->println( "<!-- SF: $script_filename_base -->" );
//					$out->println( "<!-- L:  $link -->" );
//					$out->println( "<!-- L:  $page -->" );

			if ( "" == $link )
			{
				$out->println( "<li class='active'><a href='#'>$id</a></li>" );
			}
			else if ( $script_filename_base == $link )
			{
				$out->println( "<li class='active'><a href='#'>$id</a></li>" );
			}
			else if ( ("/" == $page) && ("/index.php" != $script_filename) )
			{
				$out->println( "<li><a href='$page'>$id</a></li>" );
			}
			else if ( FALSE !== strpos( $script_filename_base, $link ) )
			{
				$out->println( "<li><a href='$page'>$id</a></li>" );
			}
			else
			{
				$out->println( "<li><a href='$page'>$id</a></li>" );
			}
			$first = "";
		}
	}
}

?>