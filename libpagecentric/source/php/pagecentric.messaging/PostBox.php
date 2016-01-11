<?php

class PostBox
{
	static function SendSMS( $from, $to, $text, $test = false )
	{
		$success = false;

		if ( !$test && SENDUSERMAIL )
		{
			$to = self::Internationalise( $to );
		}
		else
		if ( !$test && SENDDEMOMAIL )
		{
			$to = self::Internationalise( DEMO_MOBILE );
			$text = "Demo: " . $text;
		}
		else
		{
			$to = self::Internationalise( TEST_MOBILE );
			$text = "Test: " . $text;
		}

		if ( USE_WHOLESALESMS )
		{
			$api = new TransmitSMSAPI( USE_WHOLESALESMS_KEY, USE_WHOLESALESMS_SECRET );
			
			$response = $api->sendSMS( $text, $to, $from );

			if ( "SUCCESS" == $response->error->code )
			{
				$success = true;
			}
			else
			{
				var_dump( $response );
			}
		}

		return $success;
	}

	static function SendEmail( $sender, $from, $to, $subject, $plain, $html, $bcc, $test = false )
	{
		$success = false;

		$to      = self::StandardiseEmailAddress( $to );
		$message = html_entity_decode( $plain, ENT_QUOTES );
	
		if ( defined( "USE_SENDGRID" ) )
		{
			try
			{
				$sendgrid = new SendGrid( USE_SENDGRID_USER, USE_SENDGRID_PW );
				$email    = new SendGrid\Email();

				if ( !$test && SENDUSERMAIL )
				{
					$address = new EmailAddress( $to );
					
					if ( !string_contains( $to, "@" ) || !$address->isValid( new NullPrinter() ) )
					{
						$to = self::DetermineFailOverEmailAddress( $to, $address );
					}

					$email->addTo( $to );

					if ( defined( "BCCUSERMAIL") && BCCUSERMAIL )
					{
						$email->setBCC( TECH_EMAIL );
					}
					
					if ( $bcc )
					{
						$email->setBCC( $bcc );
					}
				}
				else
				{
					echo "SENDUSEREMAIL == FALSE, sending to " . TECH_EMAIL . "\n";

					$email->addTo( TECH_EMAIL );
				}
				$email->setFrom( $from );
				$email->setFromName( $sender );
				$email->setSubject( $subject );
				$email->setText( $message );
				if ( $html ) $email->setHtml( $html );

				$sendgrid->send( $email );
				$success = True;
			}
			catch ( Exception $ex )
			{
				echo "Exception thrown from SendGrid: " . $ex->getMessage() . "\n";
			}
		}
		else
		{
			echo "SendGrid is not configured.";
		}
		return $success;
	}

	static function SendMessageTest( $appname, $from, $to, $subject, $message, $html, $out )
	{
		$out->println( "$appname" );
		$out->println( "$from"    );
		$out->println( "$to"      );
		$out->println( "$subject" );
		$out->println( "$message" );
		$out->println( "$html"    );
		
		return false;
	}

	static function DetermineFailoverEmailAddress( $to, $address )
	{
		if ( defined( "BOGUS_EMAIL" ) )
		{
			echo "Bogus email detected ($to), sending to " . BOGUS_EMAIL . " - $address->error\n";
			$to = BOGUS_EMAIL;
		}
		else
		{
			echo "Bogus email detected ($to), ignoring as BOGUS_EMAIL is not defined - $address->error\n";
			$to = "";
		}

		return $to;
	}

	static function Internationalise( $to )
	{
		if ( ! string_startsWith( $to, "+" ) )
		{
			$to = str_replace( ' ', '', $to );
		
			if ( (10 == strlen( $to )) && string_startsWith( $to, "0" ) )
			{
				$to = "+61" . substr( $to, 1 );
			}
		}
		return $to;
	}
	
	static function StandardiseEmailAddress( $email_address )
	{
		$standard = $email_address;
		$start = strpos( $email_address, "(" );

		if ( FALSE !== $start )
		{
			$end = strpos( $email_address, ")", $start );

			$standard  = substr( $email_address, 0, $start );
			$standard .= substr( $email_address, $end + 1 );
		}
		
		return $standard;
	}
	
}