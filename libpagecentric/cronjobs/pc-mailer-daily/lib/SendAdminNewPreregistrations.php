<?php

include_once( "SendMessages.php" );

class SendAdminNewPreregistrations extends SendMessages
{
	function sendMessages( $out, $debug )
	{
		$kind   = "Preregistrations";
		$unsent = force_array( DBi_callProcedure( DB, "Preregistrations_Todays", $debug ) );

		if ( 0 < count( $unsent ) )
		{
			$now = date( "Y-m-d H:i:s", time() );

			if ( $this->sendEmail( ADMIN_EMAIL, $unsent, $out, $debug ) )
			{
				$out->println( "$now, '$kind', ok" );
			}
			else
			{
				$out->println( "$now, '$kind', error" );
			}
		}
	}

	function sendEmail( $email, $tuples, $out, $debug )
	{
		$success = false;

		$name     = "Admin.Preregistrations";
		$app_name = APP_NAME;
		$subject  = file_get_contents( BASE . "/share/templates/$app_name/$name.Subject.txt", false );
		$text     = "";
		$html     = "";

		foreach ( $tuples as $tuple )
		{
			$registered = array_get( $tuple, "registered" );
			$name       = array_get( $tuple, "name"       );
			$gender     = array_get( $tuple, "gender"     );
			$birth_year = array_get( $tuple, "birth_year" );
			$profession = array_get( $tuple, "profession" );
		
			$text .= sprintf( "%20s, %20s, %20s, %20s, %50s\n", $registered, $name, $gender, $birth_year, $profession );
		}

		if ( $this->sendMessage( BRAND_NAME, TECH_EMAIL, $email, $subject, $text, $html, TECH_EMAIL ) )
		{
			$success = true;
		}
		return $success;
	}
}

?>