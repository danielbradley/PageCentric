<?php

class InitialisationPage extends Page
{
	function __construct()
	{
		parent::__construct();
	}

	function redirect( $debug )
	{
		// Do not remove. Prevents redirection to /connection_error.

		$this->control = new InitialisationControl( $this, $this->debug );
	}

	function bodyMiddle( $out )
	{
		$out->inprint( "<div class='center w940'>" );
		{
			$this->control->render( $out );
		}
		$out->outprint( "</div>" );
	}
}
