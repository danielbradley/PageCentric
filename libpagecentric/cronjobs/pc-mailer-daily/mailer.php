#!/usr/bin/php
<?php

xif ( ! array_key_exists( 1, $argv ) )
{
	echo "Usage: mailer <webapp conf>" . "\n";
	exit;
}

include_once( $argv[1] );

set_include_path( BASE . "/dep/libpagecentric/source/php" );

include_once( BASE . "/dep/libpagecentric/lib/libpagecentric.php" );

Page::initialise();

include_once( "lib/SendAdminUpdate.php" );

define( "NOW", date( "Y-m-d H:i:s", time() ) );
$app_name = strtolower( APP_NAME );

function main( $args )
{
	$out   = new Printer();
	$debug = new NullPrinter();
	{
		$out->println( NOW );

		$s1 = new SendAdminUpdate();
		$s1->sendMessages( $out, $debug );
	}
}

main( $argv );

?>
