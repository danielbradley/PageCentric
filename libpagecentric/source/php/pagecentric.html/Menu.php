<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.util/HTML.php" );

class Menu extends Element
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
//			$out->println( "<ul id='$this->id' class='Menu $this->horizontal $this->vertical $this->left $this->right'>" );
//			$out->indent();
			{
				$first = "first";
			
				foreach ( $this->items as $id => $page )
				{
					$request_uri = REQUEST_URI;
					$bits = explode( '?', $page );
					$link = $bits[0];
					
//					$out->println( "<!-- SF: $request_uri -->" );
//					$out->println( "<!-- L:  $link -->" );
//					$out->println( "<!-- L:  $page -->" );

					if ( "" == $link )
					{
						$out->println( "<li class='$first'><span>$id</span></li>" );
					}
					else if ( $request_uri == $link )
					{
						$out->println( "<li class='$first on'><span>$id</span></li>" );
					}
//					else if ( ("/" == $page) && ("/index.php" != $script_filename) )
//					{
//						$out->println( "<li class='$first'><a href='$page'>$id</a></li>" );
//					}
					else if ( FALSE !== strpos( $request_uri, $link ) )
					{
						$out->println( "<li class='$first on'><span><a href='$page'>$id</a></span></li>" );
						//$out->println( "<li $first><span>$id</span></li>" );
					}
					else
					{
						//$out->println( "<li $first><span>$id</span></li>" );
						
						if ( FALSE !== strpos( $page, "#modal" ) )
						{
							$out->println( "<li class='$first'><a data-toggle='modal' href='$page'>$id</a></li>" );
						}
						else
						if ( FALSE !== strpos( $page, "#submenu" ) )
						{
							$out->println( "<li class='$first'><a data-action='submenu' href='$page'>$id</a></li>" );
						}
						else
						{
							$out->println( "<li class='$first'><a href='$page'>$id</a></li>" );
						}
					}
					$first = "";
				}
			}
//			$out->outdent();
//			$out->println( "</ul>" );
	}



}

?>