<?php

include_once( "SendMessages.php" );

class SendSignupMessages extends SendMessages
{
	function sendMessages( $out, $debug )
	{
		$unsent = force_array( DBi_callProcedure( DB, "Users_Retrieve_Unsent", $debug ) );

		if ( 0 < count( $unsent ) )
		{
			foreach ( $unsent as $tuple )
			{
				$now     = date( "Y-m-d H:i:s", time() );

				$message           = "Signup";
				$email             = array_get( $tuple, "email"      );
				$email_provisional = array_get( $tuple, "email_provisional"      );
				$given             = array_get( $tuple, "given_name" );
				$created           = array_get( $tuple, "created"    );
				$type              = array_get( $tuple, "type"       );

				if ( $email_provisional )
				{
					$email   = $email_provisional;
					$message = "ConfirmEmail";
				}

				if ( $this->createAndSendActivationEmail( $message, $type, $email, $given, $out, $debug ) )
				{
					$out->println( "$now, 'activation', $created, $email, ok" );
				}
				else
				{
					$out->println( "$now, 'activation', $created, $email, error" );
				}
			}
		}
	}

	function createAndSendActivationEmail( $message, $type, $email_address, $first_name, $out, $debug )
	{
		$success = false;

		$https = defined( "FORCE_HTTPS" )  ? "https://" : "http://";

		$sql     = "Users_Activations_Create( '$email_address' )";
		$token   = array_get( first( DBi_callProcedure( DB, $sql, $debug ) ), "token" );
		$account = defined( "MAILBOX" ) ? MAILBOX : "contact";

		switch ( $message )
		{
		case "Signup":
			$url     = $https . $_SERVER["SERVER_NAME"] . "/?action=confirm&token=" . $token;
			$subject = "Your " . APP_NAME . " account is ready to be verified";
			$text    = $this->getTemplate( $message, $type, $first_name, $url );
			$html    = "";
			break;
		
		case "ConfirmEmail":
			$url     = $https . $_SERVER["SERVER_NAME"] . "/confirm_email/?action=confirm&token=" . $token;
			$subject = "Please confirm your changed email address";
			$text    = $this->getTemplate( $message, $type, $first_name, $url );
			$html    = "";
		}

		if ( $this->sendMessage( APP_NAME, TEAM_EMAIL, $email_address, $subject, $text, $html, "" ) )
		{
			DBi_callProcedure( DB, "Users_Update_Sent( '$email_address' )", $debug );
			$success = true;
		}
		return $success;
	}

	function getTemplate( $message, $type, $first_name, $url )
	{
		$content = file_get_contents( BASE . "/share/templates/" . APP_NAME . "/$message.$type.txt", false );
		$content = str_replace(  '%FIRST_NAME%',  $first_name, $content );
		$content = str_replace(         '%URL%',         $url, $content );

		return $content;
	}
}

?>