<?php

include_once( "SendMessages.php" );

class SendPasswordResetMessages extends SendMessages
{
	function sendMessages( $out, $debug )
	{
		$unsent = force_array( DBi_callProcedure( DB, "Users_Send_Resets_Retrieve", $debug ) );

		if ( 0 < count( $unsent ) )
		{
			foreach ( $unsent as $tuple )
			{
				$now       = date( "Y-m-d H:i:s", time() );

				$email             = array_get( $tuple, "email"      );
				$email_provisional = array_get( $tuple, "email_provisional"      );
				$given             = array_get( $tuple, "given_name" );
				$token             = array_get( $tuple, "token"      );
				$timestamp         = array_get( $tuple, "timestamp"    );

				if ( $this->sendEmail( $email, $given, $token, $out, $debug ) )
				{
					$out->println( "$now, 'password reset', $timestamp, $email, ok" );
				}
				else
				{
					$out->println( "$now, 'password reset', $timestamp, $email, error" );
				}
			}
		}
	}

	function sendEmail( $email_address, $first_name, $token, $out, $debug )
	{
		$success = false;

		$https = defined( "FORCE_HTTPS" )  ? "https://" : "http://";

		$url     = $https . $_SERVER["SERVER_NAME"] . "/?show-modal=modal-reset&token=$token";
		$subject = "Follow the link below to change your password";
		$text    = $this->getTemplate( "txt", $first_name, $url, APPNAME, MAILDOMAIN );
		$html    = $this->getTemplate( "htm", $first_name, $url, APPNAME, MAILDOMAIN );

		if ( $this->sendMessage( APP_NAME, "hello@" . APP_NAME . ".com", $email_address, $subject, $text, $html, "" ) )
		{
			DBi_callProcedure( DB, "Users_Send_Resets_Sent( '$email_address' )", $debug );
			$success = true;
		}
		return $success;
	}

	function getTemplate( $format, $given, $url, $appname, $mail_domain )
	{
		$server = $_SERVER["SERVER_NAME"];

		$content = file_get_contents( BASE . "/share/templates/" . APP_NAME . "/PasswordReset.$format", false );
		$content = str_replace(  '%GIVEN_NAME%',       $given, $content );
		$content = str_replace(         '%URL%',         $url, $content );
		$content = str_replace(     '%APPNAME%',     $appname, $content );
		$content = str_replace( '%MAIL_DOMAIN%', $mail_domain, $content );
		$content = str_replace( '%SERVER_NAME%',      $server, $content );

		return $content;
	}
}

?>