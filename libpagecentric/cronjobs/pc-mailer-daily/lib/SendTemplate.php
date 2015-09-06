<?php

include_once( "SendMessages.php" );

class SendTemplate extends SendMessages
{
	function sendMessages( $out, $debug )
	{
		$kind   = "???";
		$unsent = force_array( DBi_callProcedure( DB, "<SOME TABLE>_Unsent", $debug ) );

		if ( 0 < count( $unsent ) )
		{
			foreach ( $unsent as $tuple )
			{
				$now                 = date( "Y-m-d H:i:s", time() );

				$TID                 = array_get( $tuple, "EVENT" );
				$email                = array_get( $tuple, "email"  );

				if ( $this->sendEmail( $TID, $email, $tuple, $out, $debug ) )
				{
					$out->println( "$now, '$kind', $registered, $email, ok" );
				}
				else
				{
					$out->println( "$now, '$kind', $registered, $email, error" );
				}
			}
		}
	}

	function sendEmail( $TID, $email, $tuple, $out, $debug )
	{
		$success = false;

		$name          = "Approved_Jobseeker";
		$subject       = file_get_contents( BASE . "/share/templates/shortlistme/$name.Subject.txt", false );

		$text          = addslashes( $this->getTemplate( $name, "txt", $tuple ) );
		$html          = addslashes( $this->getTemplate( $name, "htm", $tuple ) );

		if ( $this->sendMessage( "ShortlistMe", TEAM_EMAIL, $email, $subject, $text, $html, TECH_EMAIL ) )
		{
			DBi_callProcedure( DB, "<SOME TABLE>_Sent( '$TID' )", $debug );
			$success = true;
		}
		return $success;
	}

	function getTemplate( $name, $format, $tuple )
	{
		$content = file_get_contents( BASE . "/share/templates/shortlistme/$name.$format", false );

		$content = str_replace(   '%GIVEN_NAME%', array_get( $tuple, "given_name" ), $content );

		$content = str_replace(     '%BLOG_URL%',     BLOG_URL, $content );
		$content = str_replace( '%FACEBOOK_URL%', FACEBOOK_URL, $content );
		$content = str_replace(        '%PHONE%',        PHONE, $content );
		$content = str_replace(   '%TEAM_EMAIL%',   TEAM_EMAIL, $content );
		$content = str_replace(      '%ADDRESS%',      ADDRESS, $content );

		return $content;
	}
}

?>