<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Accounts extends Model
{
	function __construct()
	{}

	static function CreateAndLogin( $page, $sid, $request, $debug )
	{
		$sid = "";

		$result = array();
	
		if ( ! $page->isAuthenticated() )
		{
			$required["email"]       = "EMAIL";
			$required["password"]    = "";

			$iv = new \InputValidation( $request, $required );
			if ( $iv->validate() )
			{
				$result = self::Create( $sid, $request, $debug );

				if ( "OK" == $result->status )
				{
					error_log( "Establishing session" );

					$tuple  = first( $result->results );
				
					$username = $page->getRequest( "email"    );
					$password = $page->getRequest( "password" );
					
					$page->viva->establishSession( $username, $password, $debug );
					$page->viva->retrieveCredentials( $page->debug );

					$result->sessionid = $page->getSessionId();
				}
			}
			else
			{
				$result["status"]  = "ERROR";
				$result["message"] = "INVALID_INPUT";
			}
		}
		else
		{
			$result["status"]  = "ERROR";
			$result["message"] = "USER_ALREADY_AUTHENTICATED";
		}
		
		return $result;
	}

	static function Create( $sid, $request, $debug )
	{
		$success = False;
	
		$email       = array_get( $request, "email"       );
		$password    = array_get( $request, "password"    );
		$given_name  = array_get( $request, "given_name"  );
		$family_name = array_get( $request, "family_name" );
		$name        = array_get( $request, "name"        );
		$user_type   = array_get( $request, "user_type"   );

		if ( ("" == $given_name) && ("" == $family_name) && ("" != $name ) )
		{
			$bits = explode( ' ', $name );
			$n    = count( $bits );
			
			$family_name = $bits[$n-1];
			$given_name  = implode( " ", array_slice( $bits, 0, -1 ) );
		}

		$user_type   = $user_type ? $user_type : "DEFAULT";

		$sql = "users_create( '$email', '$password', '$given_name', '$family_name', '$user_type' )";

		error_log( $sql );
		
		$result = \replicantdb\ReplicantDB::CallProcedure( DB, $sql, $debug );
		$tuple   = first( $result->results );

		$user   = array_get( $tuple, "USER"   );
		$status = array_get( $tuple, "status" );
		
		$debug->println( "<!-- status: $status -->" );
		
		switch ( $status )
		{
		case "OK":
			$debug->println( "<!-- User created -->" );
			break;
			
		case "ERROR":
			$debug->println( "<!-- User was not created due to unexpected error -->" );
			break;
			
		case "USER_EXISTS":
			$debug->println( "<!-- User already exists -->" );
			break;
			
		default:
			$debug->println( "<!-- !!! Unexpected status returned !!! -->" );
		}
		
		return $result;
	}

	static function SimpleCreateUser( $page )
	{
		$sid = "";
	
		if ( ! $page->isAuthenticated() )
		{
			$required["email"]       = "EMAIL";
			$required["given_name"]  = "";
			$required["family_name"] = "";
			$required["password"]    = "";

			$iv = new \InputValidation( $page->request, $required );
			if ( $iv->validate() )
			{
				switch( $status = \Accounts::createUser( $page->getSessionId(), $page->request, $page->debug ) )
				{
				case "USER_EXISTS":
					$sid = $status;
					break;
					
				default:
					$username = $page->getRequest( "email"    );
					$password = $page->getRequest( "password" );
					
					$page->viva->establishSession( $username, $password, $page->debug );
					$page->viva->retrieveCredentials( $page->debug );

					$sid = $page->getSessionId();
					break;
				}
			}
		}
		return $sid;
	}
	
	function perform( $session, $request, $debug )
	{
		$ret = null;
	
		$debug->println( "<!-- AccountsController::perform() start -->" );
		$debug->indent();
		{
			if ( array_key_exists( "action", $request ) )
			{
				$msg = "<!-- performing: " . $request["action"] . " -->";
				$debug->println( $msg );
				
				switch ( $request["action"] )
				{
				case "users_create":
					$ret = $this->createUser( $session, $request, $debug );
					break;

				case "users_update":
					$ret = $this->updateUser( $session, $request, $debug );
					break;

				case "resend_activation":
					$ret = $this->resendActivation( $session, $request, $debug );
					break;

				case "confirm":
					$ret = $this->confirm( $session, $request, $debug );
					break;
					
				case "users_reset_passwords_replace":
					$ret = $this->replaceResetPassword( $session->getSessionId(), $request, $debug );
					break;

				case "users_reset_passwords_reset_password":
					$ret = $this->resetPassword( $session->getSessionId(), $request, $debug );
					break;

				case "users_change_password":
					$ret = $this->changePassword( $session->getSessionId(), $request, $debug );
					break;

				case "users_termination_schedule_replace":
					$ret = $this->scheduleTermination( $session, $request, $debug );
					break;
				}
			}
		}
		$debug->outdent();
		$debug->println( "<!-- AccountsController::perform() end -->" );

		return $ret;
	}

	static function createUser( $sid, $request, $debug )
	{
		$success = False;
	
		$debug->println( "<!-- UsersController::createUser() start -->" );
		$debug->indent();
		{
			$email       = array_get( $request, "email"       );
			$password    = array_get( $request, "password"    );
			$given_name  = array_get( $request, "given_name"  );
			$family_name = array_get( $request, "family_name" );
			$name        = array_get( $request, "name"        );
			$user_type   = array_get( $request, "user_type"   );

			if ( ("" == $given_name) && ("" == $family_name) && ("" != $name ) )
			{
				$bits = explode( ' ', $name );
				
				if ( 1 <= count( $bits ) ) $given_name  = $bits[0];
				if ( 2 <= count( $bits ) ) $family_name = $bits[1];
			}

			$user_type   = $user_type ? $user_type : "DEFAULT";

			$sql = "users_create( '$email', '$password', '$given_name', '$family_name', '$user_type' )";

			$tuple   = first( DBi_callProcedure( DB, $sql, $debug ) );

			$user   = array_get( $tuple, "USER"   );
			$status = array_get( $tuple, "status" );
			
			$debug->println( "<!-- status: $status -->" );
			
			switch ( $status )
			{
			case "OK":
				$debug->println( "<!-- User created -->" );
				break;
				
			case "ERROR":
				$debug->println( "<!-- User was not created due to unexpected error -->" );
				break;
				
			case "USER_EXISTS":
				$debug->println( "<!-- User already exists -->" );
				break;
				
			default:
				$debug->println( "<!-- !!! Unexpected status returned !!! -->" );
			}
		}
		$debug->outdent();
		$debug->println( "<!-- UsersController::createUser() end -->" );
		
		return $status;
	}

	static function updateUser( $sid, $request, $debug )
	{
		$success = False;
	
		$debug->inprint( "<!-- UsersController::updateUser() start -->" );
		{
			$email       = array_get( $request, "email"       );
			$USER        = array_get( $request, "USER"        );
			$given_name  = array_get( $request, "given_name"  );
			$family_name = array_get( $request, "family_name" );

			$sql = "Users_Update( '$sid', '$USER', '$email', '$given_name', '$family_name' )";

			$success = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		}
		$debug->outprint( "<!-- UsersController::updateUser() end -->" );
		
		return $success;
	}
	
	static function confirm( $session, $request, $debug )
	{
		$token = array_get( $request, "token" );
		
		if ( defined( "CONFIRM_AND_AUTHENTICATE" ) )
		{
			$debug->println( "<!-- CONFIRM_AND_AUTHENTICATE defined -->" );
		
			$sql = "Users_Activations_Confirm_Account_And_Authenticate( '$token' )";
			$sid = DBi_callFunction( DB, $sql, $debug );
			return $sid;
		}
		else
		{
			$debug->println( "<!-- CONFIRM_AND_AUTHENTICATE not defined -->" );

			$sql  = "Users_Activations_Confirm_Account( '$token' )";
			$bool = is_array( DBi_callProcedure( DB, $sql, $debug ) );
			return $bool;
		}
	}

	static function resendActivation( $session, $request, $debug )
	{
		$username    = $request['email'];
		$first_name  = "user";

		return $this->createAndSendActivationEmail( $username, $first_name, $debug );
	}

		static function createAndSendActivationEmail( $email_address, $first_name, $debug )
		{
			$success = False;
		
			$sql = "Users_Activations_Create( '$email_address' )";
			$token = DBi_callFunction( DB, $sql, $debug );
					
			$url = "http://" . $_SERVER["SERVER_NAME"] . PAGE . "/confirm/?action=confirm&token=" . $token;

			$AppName = APPNAME;
			$subject = "Please Confirm Your $AppName Account";

			$message =            "Hi $first_name,\n";
			$message = $message . "\n";
			$message = $message . "To confirm your account please click the following link:";
			$message = $message . "\n";
			$message = $message . "$url\n";
			$message = $message . "\n";
			$message = $message . "Regards,\n";
			$message = $message . $_SERVER["SERVER_NAME"] . "\n";
			$message = $message . "\n";

			$account = defined( "MAILBOX" ) ? MAILBOX : "contact";

			if ( defined( "USE_SENDGRID" ) )
			{
				$sendgrid = new SendGrid( USE_SENDGRID_USER, USE_SENDGRID_PW );
				$email    = new SendGrid\Mail();

				if ( SENDUSERMAIL )
				{
					$email->addTo( $email_address );
				}
				else
				{
					$email->addTo( "$account@" . MAILDOMAIN );
				}
				$email->setBCC( "$account@" . MAILDOMAIN );
				$email->setFrom( "$account@" . MAILDOMAIN );
				$email->setFromName( "$AppName" );
				$email->setSubject( $subject );
				$email->setText( $message );
			
				$sendgrid->smtp->send( $email );
				$success = True;
			}
			else if ( $message )
			{
				$email = new Email();
				$email->setFrom( "$AppName <$account@" . MAILDOMAIN . ">" );
				if ( SENDUSERMAIL )
				{
					$email->setRecipients( $email_address );
				}
				$email->setBCCs( "$account@" . MAILDOMAIN );
				$email->setSubject( $subject );
				$email->setMessage( $message );
			
				$success = $email->send( $debug );
			}
			return $success;
		}

	static function sendPasswordReset( $sid, $request, $debug )
	{
		$email = array_get( $request, "email" );
		
		$sql = "Users_Send_Resets_Replace( '$email' )";

		error_log( $sql );

		return is_array( DBi_callProcedure( DB, $sql, $debug ) );
	}

	static function resetPassword( $sid, $request, $debug )
	{
		$ret = false;
	
		$token     = array_get( $request, "token"     );
		$password1 = array_get( $request, "password1" );
		$password2 = array_get( $request, "password2" );

		if ( $password1 == $password2 )
		{
			$sql = "Users_Send_Resets_Reset_Password( '$token', '$password1' )";

			error_log( $sql );

			$ret = is_array( DBi_callProcedure( DB, $sql, $debug ) );
		}
		return $ret;
	}

	static function changePassword( $sid, $request, $debug )
	{
		$ret = false;
	
		$email     = array_get( $request, "email"     );
		$password0 = array_get( $request, "password0" );
		$password1 = array_get( $request, "password1" );
		$password2 = array_get( $request, "password2" );

		if ( $password1 == $password2 )
		{
			$sql = "Users_Change_Password( '$email', '$password0', '$password1' )";

			error_log( $sql );

			$ret = array_get( first( DBi_callProcedure( DB, $sql, $debug ) ), "success" );
		}
		return $ret;
	}

	static function UpdatePassword( $sid, $request, $debug )
	{
		$USER         = array_get( $request, "USER"         );
		$old_password = array_get( $request, "old_password" );
		$new_password = array_get( $request, "new_password" );

		$sql = "Users_Change_Password_with_USER( '$USER', '$old_password', '$new_password' )";

		error_log( $sql );

		return array_get( first( DBi_callProcedure( DB, $sql, $debug ) ), "success" );
	}

	static function scheduleTermination( $session, $request, $debug )
	{
		if ( $session instanceof SPSession )
		{
			$sid = $session->getSessionId();
		}
		else
		{
			$sid = $session;
		}

		$USER     = array_get( $request, "USER" );
		$password = array_get( $request, "password" );
		
		$sql = "Users_Termination_Schedule_Replace( '$sid', '$USER', '$password' )";
		return is_array( DBi_callProcedure( DB, $sql, $debug ) );
	}
	
//		static function first( $array )
//		{
//			if ( is_array( $array ) && array_key_exists( 0, $array ) )
//			{
//				return $array[0];
//			}	
//		}

	static function Users( $sid, $debug )
	{
		$sql = "Users_Retrieve_All( '$sid' )";

		return force_array( DBi_callProcedure( DB, $sql, $debug ) );
	}
}

?>