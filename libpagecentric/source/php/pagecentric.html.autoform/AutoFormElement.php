<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormElement extends AutoFormInput
{
	function __construct( $type, $dAttributes, $title, $name, $element )
	{
		parent::__construct( $name, "" );
	
		$this->type         = $type;
		$this->dAttributes  = $dAttributes;
		$this->title        = $title;
		$this->element      = $element;
	}
	
	function render( $out )
	{
		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->inprint( "<label>" );
			{
				if ( $this->title ) $out->println( "<span>$this->title</span>" );
				$this->element->render( $out );
			}
			$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}
