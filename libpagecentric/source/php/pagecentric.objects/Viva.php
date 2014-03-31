<?php
//	Copyright (c) 2011 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Viva
{
	var $session;
	var $status;
	var $authenticated;
	var $credentials;
	
	var $model;

	function __construct( $request, $debug )
	{
		$this->request = $request;
	
		$username = array_get( $request, "username" );
		$password = array_get( $request, "password" );
	
		$this->establishSession( $username, $password, $debug );
		$this->retrieveCredentials( $debug );

//		switch ( array_get( $this->request, "action" ) )
//		{
//		case "users_signin":
//			unset( $this->request["username"] );
//			unset( $this->request["password"] );
//			break;
//		}
		
		$this->iv = new InputValidation( $this->request, array() );
	}

		function establishSession( $username, $password, $debug )
		{
			$request = $this->request;
		
			$this->session = new SessionSP( $request, DB_HOSTNAME, "", $username, $password, True );
			$this->status  = $this->session->establish( $debug );
			
			if ( False === $this->status )
			{
				$this->authenticated = False;
			} else {
			
				$debug->println( "<!-- Status: $this->status -->" );
			
				switch ( $this->status )
				{
				case "AUTHENTICATED":
					$this->authenticated = True;
					break;

				case "INVALID_PASSWORD":
				case "INVALID_LOGINS":
				case "INVALID_USER":
				case "False":
					$this->authenticated = False;
				}
			}
		}

		function retrieveCredentials( $debug )
		{
			$this->credentials = array();

			if ( $this->authenticated )
			{
				if ( isset( $this->session ) )
				{
					$sid = $this->session->sessionid;
					
					$debug->println( "<!-- CALL Users_Authenticate( '$sid' ) -->" );
					$this->credentials = first( DBi_callProcedure( DB, "Users_Authenticate( '$sid' )", $debug ) );
				}
			}
		}

	function releaseDB( $debug )
	{
		//DBi_kill( $debug );
	}

	function getSession()
	{
		return isset( $this->session ) ? $this->session : null;
	}

	function getSessionId()
	{
		return isset( $this->session ) ? $this->session->sessionid : null;
	}
	
	function getCredentials()
	{
		return $this->credentials;
	}

	function getEmail()
	{
		return array_get( "email", $this->credentials );
	}

	function getUser()
	{
		return array_get( "USER", $this->credentials );
	}

	function getIDType()
	{
		return array_get( "idtype", $this->credentials );
	}

	function getUserStatus()
	{
		return array_get( $this->credentials, "user_status" );
	}

	function getGivenName()
	{
		return array_get( $this->credentials, "given_name" );
	}

	function getFamilyName()
	{
		return array_get( $this->credentials, "family_name" );
	}

	function getUserHash()
	{
		return array_get( $this->credentials, "user_hash" );
	}

	function getTargetUser()
	{
		$value = $this->getUser();

		$this->debug->inprint( "<!-- VivaPage::getTargetUser() start : USER=$value -->" );
		{
			if ( ($profile = array_get( $this->request, "profile" )) )
			{
				$t     = Users::retrieveByUserHash( $this->getSessionId(), $profile, $this->debug );
				$value = array_get( $t, "USER" ); 
			}
		}
		$this->debug->outprint( "<!-- VivaPage::getTargetUser() end : return $value -->" );

		return $value;
	}

	function getTargetGivenName()
	{
		$value = $this->getGivenName();

		if ( ($profile = array_get( $this->request, "profile" )) )
		{
			$t     = Users::retrieveByUserHash( $this->getSessionId(), $profile, $this->debug );
			$value = array_get( $t, "given_name" ); 
		}
		return $value;
	}

	function getTargetFamilyName()
	{
		$value = $this->getFamilyName();

		if ( ($profile = array_get( $this->request, "profile" )) )
		{
			$t     = Users::retrieveByUserHash( $this->getSessionId(), $profile, $this->debug );
			$value = array_get( $t, "family_name" ); 
		}
		return $value;
	}

	function logout( $debug )
	{
		$this->session->terminate( $debug );
		unset( $this->session );
		unset( $this->authenticated );
	}
	
	function setInputValidation( $iv )
	{
		$this->iv = $iv;
	}
	
	function getInputValidation()
	{
		return $this->iv;
	}
	
	function setModel( $model )
	{
		$this->model = $model;
	}
	
	function getModel()
	{
		return $this->model;
	}
	
	function isAuthenticated()
	{
		return isset( $this->authenticated ) ? $this->authenticated : False;
	}
	
	function getAuthenticationStatus()
	{
		return $this->status;
	}
}


?>