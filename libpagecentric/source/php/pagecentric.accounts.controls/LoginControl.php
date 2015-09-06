<?php

class LoginControl extends Control
{
	function __construct( $page )
	{
		switch ( $page->getRequest( "action" ) )
		{
		case "users_login":
			if ( $page->isAuthenticated() )
			{
				$this->success();
//				unset( $page->request["username"] );
//				unset( $page->request["password"] );
//				
//				$page->debug->println( "<!-- Authenticated redirecting... -->" );
//				$this->success();
			}
			else
			if ( "true" == $page->getRequest( "mobile" ) )
			{
				$page->showModal( "mobile-login" );
				$page->debug->println( "<!-- Not authenticated !!! -->" );
			}
			else
			{
				$page->showModal( "modal-member_login" );
				$page->debug->println( "<!-- Not authenticated !!! -->" );
			}
			break;
		}
		$this->form  = new LoginForm( $page->request );
	}

	function render( $out )
	{
		$out->inprint( "<div class='CreateAccountControl'>" );
		{
			$this->form->render( $out );
		}
		$out->outprint( "</div>" );
	}
	
	function success()
	{
		if ( defined( "AUTH_REDIRECT" ) )
		{
			header( "Location:" . AUTH_REDIRECT );
		}
		else
		{
			header( "Location:/" );
		}
	}
}

?>