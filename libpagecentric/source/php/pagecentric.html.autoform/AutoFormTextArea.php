<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormTextArea
{
	var $type;
	var $divClasses;
	var $title;
	var $name;
	var $inputClasses;

	function __construct( $type, $dAttributes, $title, $name, $iAttributes )
	{
		$this->type         = $type;
		$this->dAttributes  = $dAttributes;
		$this->title        = $title;
		$this->name         = $name;
		$this->iAttributes  = $iAttributes;
		$this->value        = "";
	}
	
	function setValue( $value )
	{
		$this->value = $value;
	}

	function getName()
	{
		return $this->name;
	}
	
	function render( $out )
	{
		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->inprint( "<label>" );
			{
				$out->println( "<span>$this->title</span>" );
				$out->println( "<textarea $this->iAttributes type='$this->type' name='$this->name'>$this->value</textarea>" );
			}
			$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}
