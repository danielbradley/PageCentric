#!/usr/bin/php
<?php

//require_once( '/local/software/Libraries/Braintree/braintree-php-2.22.1/lib/Braintree.php' );
require_once( '/local/software/Libraries/Braintree/braintree-php-2.37.0/lib/Braintree.php' );

if ( ! array_key_exists( 1, $argv ) )
{
	echo "Usage: paymaster <webapp conf>" . "\n";
	exit;
}

//include_once( "/local/settings/webapps/sm.braintree.php" );

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('j5sxjqd27rjw7ts6');
Braintree_Configuration::publicKey('qz3ctvx9dcf4f4vd');
Braintree_Configuration::privateKey('b8a62944ab55395773a92b934cc14a28');

include_once( $argv[1] );

define( "NOW", date( "Y-m-d H:i:s", time() ) );

$app_name = strtolower( APP_NAME );

include_once( BASE . "/dep/lib$app_name/lib/autoload.php" );
include_once( "lib/Phase0Plans.php"         );
include_once( "lib/Phase1Customers.php"     );
include_once( "lib/Phase2CreditCards.php"   );
include_once( "lib/Phase3Subscriptions.php" );
include_once( "lib/Phase4Purchases.php"     );
include_once( "lib/Phase5Transactions.php"  );
include_once( "lib/Phase7Remover.php"       );

Page::initialise();

function main( $args )
{
	$out   = new Printer();
	$debug = new NullPrinter();
	{
		$out->println( NOW );

		$phase0 = new Phase0Plans();
		$phase0->perform( $out, $debug );

//		$phase1 = new Phase1Customers();
//		$phase1->perform( $out, $debug );
//
//		$phase2 = new Phase2CreditCards();
//		$phase2->perform( $out, $debug );
//
//		$phase3 = new Phase3Subscriptions();
//		$phase3->perform( $out, $debug );
//
//		$phase4 = new Phase4Purchases();
//		$phase4->perform( $out, $debug );
//
//		$phase5 = new Phase5Transactions();
//		$phase5->perform( $out, $debug );
//
//		$phase7 = new Phase7Remover();
//		$phase7->perform( $out, $debug );
	}
}

main( $argv );
