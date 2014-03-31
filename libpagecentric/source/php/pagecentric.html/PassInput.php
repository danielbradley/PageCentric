<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.html/HTMLUtils.php" );
//include_once( $SOURCE . "/pagecentric.html/TextInput.php" );

class PassInput extends TextInput
{
	function __construct( $iv, $label, $name, $attributes )
	{
		parent::__construct( $iv, $label, $name, $attributes );
		$this->type = "password";
	}
}

?>