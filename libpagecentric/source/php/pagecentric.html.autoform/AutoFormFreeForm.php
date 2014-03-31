<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormFreeForm extends AutoFormInput
{
	function __construct( $dAttributes, $html )
	{
		parent::__construct( "", "" );
	
		$this->dAttributes = $dAttributes;
		$this->html        = $html;
	}

	function render( $out )
	{
		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->println( $this->html );
		}
		$out->outprint( "</div>" );
	}
}
