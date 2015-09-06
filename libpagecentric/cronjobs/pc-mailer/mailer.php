#!/usr/bin/php
<?php

include_once( "/local/software/Libraries/SendGrid/sendgrid-php/SendGrid_loader.php" );

if ( ! array_key_exists( 1, $argv ) )
{
	echo "Usage: mailer <webapp conf>" . "\n";
	exit;
}

include_once( $argv[1] );

define( "NOW", date( "Y-m-d H:i:s", time() ) );

$app_name = strtolower( APP_NAME );

include_once( BASE . "/dep/lib$app_name/lib/lib$app_name.php" );
include_once( "content/SignupMessage.php" );
include_once( "lib/SendSignupMessages.php" );
include_once( "lib/SendEventRegistrations.php" );
include_once( "lib/SendPasswordResetMessages.php" );
include_once( "lib/SendProfile90Notifications.php" );


function main( $args )
{
	$out   = new Printer();
	$debug = new NullPrinter();
	{
		$out->println( NOW );

//		$s1 = new SendSignupMessages();
//		$s1->sendMessages( $out, $debug );

		$s2 = new SendPasswordResetMessages();
		$s2->sendMessages( $out, $debug );
	}
}

main( $argv );

?>
