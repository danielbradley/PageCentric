<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

abstract class AutoFormInput
{
	var $name;
	var $value;
	var $iv;
	var $example;
	var $help;
	var $warning;

	function __construct( $name, $value )
	{
		$this->name  = $name;
		$this->value = $value;
		$this->iv    = null;
	}
	
	function setValue( $value )
	{
		$this->value = $value;
	}

	function getName()
	{
		return $this->name;
	}

	function getValue()
	{
		return $this->value;
	}
	
	function setIV( $iv )
	{
		$this->iv = $iv;
	}

	function getIV()
	{
		return $this->iv;
	}

	function setExampleText( $example )
	{
		$this->example = $example;
	}

	function getExampleText()
	{
		return $this->example ? $this->example : "";
	}
	
	function setHelpText( $help )
	{
		$this->help = $help;
	}

	function getHelpText()
	{
		return $this->help;
	}

	function setWarningText( $warning )
	{
		$this->warning = $warning;
	}

	function getWarningText()
	{
		return $this->warning;
	}
	
	abstract function render( $out );
}
