<?php

class SendMessages
{
	function sendMessage( $appname, $from, $to, $subject, $message, $html, $bcc )
	{
		$success = false;
	
		$message = html_entity_decode( $message, ENT_QUOTES );
		$message = str_replace( "\'", "'", $message );
	
		if ( defined( "USE_SENDGRID" ) )
		{
			try
			{
				$sendgrid = new SendGrid( USE_SENDGRID_USER, USE_SENDGRID_PW );
				$email    = new SendGrid\Mail();

				if ( SENDUSERMAIL )
				{
					$email->addTo( $to );
					if ( $bcc )
					{
						$email->setBCC( $bcc );
					}
				}
				else
				{
					$email->addTo( $bcc );
				}
				$email->setFrom( $from );
				$email->setFromName( $appname );
				$email->setSubject( $subject );
				$email->setText( $message );
				if ( $html ) $email->setHtml( $html );

				$sendgrid->smtp->send( $email );
				$success = True;
			}
			catch ( Exception $ex )
			{
				echo "Exception thrown from SendGrid: " . $ex->getMessage() . "\n";
			}
		}
		return $success;
	}

	function sendMessageTest( $appname, $from, $to, $subject, $message, $html, $out )
	{
		$out->println( "$appname" );
		$out->println( "$from"    );
		$out->println( "$to"      );
		$out->println( "$subject" );
		$out->println( "$message" );
		$out->println( "$html"    );
		
		return false;
	}
}

?>