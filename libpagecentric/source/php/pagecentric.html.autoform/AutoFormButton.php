<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormButton extends AutoFormInput
{
	function __construct( $type, $dAttributes, $value, $name, $iAttributes )
	{
		parent::__construct( $name, $value );
	
		$this->type        = $type;
		$this->dAttributes = $dAttributes;
		$this->iAttributes = $iAttributes;
	}

	function render( $out )
	{
		$name  = $this->getName();
		$value = $this->getValue();

		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->inprint( "<label>" );
			{
				$out->println( "<input $this->iAttributes type='$this->type' name='$name' value='$value'>" );
			}
			$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}
