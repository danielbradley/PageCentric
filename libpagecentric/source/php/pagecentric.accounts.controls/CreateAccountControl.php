<?php

class CreateAccountControl extends View
{
	function __construct( $page )
	{
		$this->completed = False;
		$this->status    = "";

		$ctrl = new Accounts();
		if ( "OK" == ($this->status = $ctrl->perform( $page->getSessionId(), $page->request, $page->debug )) )
		{
			$username = $page->getRequest( "email"    );
			$password = $page->getRequest( "password" );
			
			$page->viva->establishSession( $username, $password, $page->debug );
			$page->viva->retrieveCredentials( $page->debug );
			
			if ( $page->isAuthenticated() )
			{
				$this->success( $page );
			}

			$this->completed = True;
		}

		$this->form  = new CreateAccountForm( $page, $page->request, $page->debug );
	}

	function render( $out )
	{
		$out->inprint( "<div class='CreateAccountControl'>" );
		{
			$this->form->render( $out );
		}
		$out->outprint( "</div>" );
	}
	
	function success( $page )
	{
		header( "Location:" . AUTH_REDIRECT );
	}

	function isCompleted()
	{
		return $this->completed;
	}
	
	function getStatus()
	{
		return $this->status;
	}
}

?>