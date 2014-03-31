<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormButton.php" );
//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormElement.php" );
//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormFile.php" );
//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormFreeForm.php" );
//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormTextArea.php" );
//include_once( $SOURCE . "/pagecentric.html.autoform/AutoFormTextInput.php" );
//include_once( $SOURCE . "/pagecentric.util/HTML.php" );

class AutoForm extends Form
{
	var $action;
	var $hidden;
	var $fields;
	var $buttons;

	static function create( $type, $dAttributes, $title, $name, $iAttributes )
	{ 
		$field = null;

		switch( $type )
		{
		case "element":
			$field = new AutoFormElement( $type, $dAttributes, $title, $name, $iAttributes );
			break;

		case "freeform":
			$field = new AutoFormFreeForm( $dAttributes, $title );
			break;

		case "file":
			$field = new AutoFormFile( $type, $dAttributes, $title, $name, $iAttributes );
			break;

		case "button":
		case "submit":
			$field = new AutoFormButton( $type, $dAttributes, $title, $name, $iAttributes );
			break;

		case "textarea":
			$field = new AutoFormTextArea( "textarea", $dAttributes, $title, $name, $iAttributes );
			break;

		case "password":
			$field = new AutoFormTextInput( "text", $dAttributes, $title, $name, $iAttributes );
			break;

		case "text":
		default:
			$field = new AutoFormTextInput( "text", $dAttributes, $title, $name, $iAttributes );
		}
		
		return $field;
	}
	
	function __construct( $page, $tuple, $attributes, $debug )
	{
		$this->page       = $page;
		$this->tuple      = $tuple;
		$this->attributes = $attributes;
		
		$this->action  = "";
		$this->hidden  = array();
		$this->fields  = array();
		$this->buttons = array();
	}

	function setIV( $iv )
	{
		$this->iv = $iv;
	}

	function setAction( $action )
	{
		$this->action = $action;
	}
	
	function setHidden( $array )
	{
		$this->hidden = $array;
	}

	function addField( $name, $field )
	{
		$this->fields[$name] = $field;
	}

	function addButton( $button )
	{
		$this->buttons[] = $button;
	}
	
	function getField( $name )
	{
		return array_key_exists( $name, $this->fields ) ? $this->fields[$name] : null; 
	}
	
	function render( $out )
	{
		$out->inprint( "<form $this->attributes>" );
		{
			$out->inprint( "<div>" );
			{
				if ( $this->action ) $out->println( "<input type='hidden' name='action' value='$this->action'>" );
				foreach ( $this->hidden as $name )
				{
					$value = array_get( $this->tuple, $name );
					$out->println( "<input type='hidden' name='$name' value='$value'>" );
				}
			}
			$out->outprint( "</div>" );

			$out->inprint( "<div class='row'>" );
			{
				foreach ( $this->fields as $field )
				{
					if ( isset( $this->iv ) ) $field->setIV( $this->iv );
				
					$value = array_get( $this->tuple, $field->getName() );
					if ( $value ) $field->setValue( $value );
					$field->render( $out );
				}
			}
			$out->outprint( "</div>" );

			$out->inprint( "<div>" );
			{
				foreach ( $this->buttons as $button )
				{
					$button->render( $out );
				}
			}
			$out->outprint( "</div>" );
		}
		$out->outprint( "</form>" );
	}
}

