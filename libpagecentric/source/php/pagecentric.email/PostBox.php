<?php

class PostBox
{
	static function SendMessage( $sender, $from, $to, $subject, $plain, $html, $bcc )
	{
		$success = false;
	
		$message = html_entity_decode( $plain, ENT_QUOTES );
	
		if ( defined( "USE_SENDGRID" ) )
		{
			try
			{
				$sendgrid = new SendGrid( USE_SENDGRID_USER, USE_SENDGRID_PW );
				$email    = new SendGrid\Mail();

				if ( SENDUSERMAIL )
				{
					$email->addTo( $to );
					$email->setBCC( "clients@imperial-standard.com" );
					if ( $bcc )
					{
						$email->setBCC( $bcc );
					}
				}
				else
				{
					$email->addTo( "clients@imperial-standard.com" );
				}
				$email->setFrom( $from );
				$email->setFromName( $sender );
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
}