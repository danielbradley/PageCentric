<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormFile extends AutoFormInput
{
	function __construct( $type, $dAttributes, $title, $name, $iAttributes )
	{
		parent::__construct( $name, $title );
	
		$this->type        = $type;
		$this->dAttributes = $dAttributes;
		$this->iAttributes = $iAttributes;
	}

	function render( $out )
	{
		$name  = $this->getName();
		$title = $this->getValue();

		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->inprint( "<label>" );
			{
				$out->println( "<span>$title</span><br>" );
				$out->println( "<input $this->iAttributes type='$this->type' name='$name'>" );
			}
			$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}
