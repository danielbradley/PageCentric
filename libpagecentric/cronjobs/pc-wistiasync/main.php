#!/usr/bin/php
<?php

if ( ! array_key_exists( 1, $argv ) )
{
	echo "Usage: main <webapp conf>" . "\n";
	exit;
}

include_once( $argv[1] );

set_include_path( BASE . "/dep/libpagecentric/source/php:" );

include_once( BASE . "/dep/libpagecentric/lib/libpagecentric.php" );

Page::initialise();

include_once( "lib/WistiaSync.php" );

function main( $args )
{
	define( "NOW", date( "Y-m-d H:i:s", time() ) );

	$out   = new Printer();
	$debug = new NullPrinter();
	{
		$out->println( NOW );

		$s1 = new WistiaSync();
		$s1->perform( $out, $debug );
	}
}

main( $argv );

?>
