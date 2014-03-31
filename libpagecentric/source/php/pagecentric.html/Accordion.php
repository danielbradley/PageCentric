<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Accordion extends View
{
	function __construct( $page, $id, $data, $attributes )
	{
		$this->page       = $page;
		$this->id         = $id;
		$this->data       = $data;
		$this->attributes = $attributes;
	}

//				/?accordian:comments=Comments

	function render( $out )
	{
		$id   = $this->id;
		$attr = $this->attributes;
		$key  = "accordian:" . $id;
		$show = $this->page->getRequest( $key );

		$url_parameters = new URLParameters( array( "CONTRIBUTION" ), $this->page->request );
		
		$params = $url_parameters->encodeWithout( $key );
	
		$out->inprint( "<div class='accordion' id='$id'>" );
		{
			$out->inprint( "<div $attr>" );
			{
				if ( is_array( $this->data ) )
				{
					$nr = 0;
					foreach ( $this->data as $heading => $object )
					{
						$nr++;

						$out->inprint( "<div class='accordion-row'>" );
						{
							$out->inprint( "<div class='accordion-heading'>" );
							{
								if ( string_contains( $show, $heading ) )
								{
									$out->println( "<a href='./?$params'>$heading</a>" );
								}
								else
								{
									$out->println( "<a href='./?$params&$key=$heading'>$heading</a>" );
								}
							}
							$out->outprint( "</div>" );

							if ( string_contains( $show, $heading ) )
							{
								$out->inprint( "<div class='accordion-body'>" );
								{
									$out->inprint( "<div class='accordion-inner'>" );
									{
										if ( is_string( $object ) )
										{
											$out->println( $object );
										}
										else
										{
											$object->render( $out );
										}
									}
									$out->outprint( "</div>" );
								}
								$out->outprint( "</div>" );
							}
						}
						$out->outprint( "</div>" );
					}
				}
				else
				{
					$out->println( "<pre>data is not an array</pre>" );
				}
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</div>" );
	}
}

?>