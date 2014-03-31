<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

include( "BootstrapTextInput.php" );

class BootstrapPasswordInput extends BootstrapTextInput
{
	function __construct( $iv, $label, $name, $attributes )
	{
		parent::__construct( $iv, $label, $name, $attributes );
	
		$this->type = "password";
	}
}

?>