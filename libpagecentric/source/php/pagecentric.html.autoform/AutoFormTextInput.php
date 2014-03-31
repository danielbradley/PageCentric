<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class AutoFormTextInput extends AutoFormInput
{
	var $type;
	var $divClasses;
	var $title;
	var $name;
	var $inputClasses;

	function __construct( $type, $dAttributes, $title, $name, $iAttributes )
	{
		parent::__construct( $name, $title );
	
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
		$iv      = $this->getIV();
		$example = $this->getExampleText();
		$warn    = $this->getIV()->value( $this->name ) ? "class='warn'" : "";
		$warning = $warn ? $this->getWarningText() : "";

		$help    = $this->getHelpText();
	
		$out->inprint( "<div $this->dAttributes>" );
		{
			$out->inprint( "<label $warn>" );
			{
				$out->println( "<tt>$this->title</tt>" );
				$out->println( "<input $this->iAttributes type='$this->type' name='$this->name' value='$this->value'>" );
				if ( $help    ) $out->println( "<div class='help' title='$help'>?</div>" );
				if ( $warning ) $out->println( "<span>$warning</span>" );
				else
				if ( $example ) $out->println( "<span>$example</span>" );
			}
			$out->outprint( "</label>" );
		}
		$out->outprint( "</div>" );
	}
}
