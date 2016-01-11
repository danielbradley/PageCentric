<?php

class SendMessages
{
	function sendAllMessages( $msgtype, $sql, $TID_name, $out, $debug, $sendOne = false )
	{
		$unsent = force_array( DBi_callProcedure( DB, $sql, $debug ) );

		if ( 0 < count( $unsent ) )
		{
			foreach ( $unsent as $tuple )
			{
				$now    = date( "Y-m-d H:i:s", time()  );
				$TID    = array_get( $tuple, $TID_name );
				$email  = array_get( $tuple, "email"   );
				$vendor = array_get( $tuple, "vendor"  );

				if ( $TID )
				{
					if ( $this->createMessage( $msgtype, $TID, $email, $tuple, $out, $debug ) )
					{
						$out->println( "$now, '$msgtype', $email, $vendor, ok" );
					}
					else
					{
						$out->println( "$now, '$msgtype', $email, $vendor, error" );
					}
					if ( $sendOne ) break;
				}
				else
				{
					$out->println( "Error: tuple does not contain expected TID field: $TID_name" );
				}
			}
		}
	}

	function sendMessage( $appname, $from, $to, $subject, $message, $html, $bcc, $test = false )
	{
		return PostBox::SendEmail( $appname, $from, $to, $subject, $message, $html, $bcc, $test );
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

	function sendSMS( $from, $to, $text, $test = false )
	{
		return PostBox::SendSMS( $from, $to, $text, $test );
	}

	function getTemplate( $msgtype, $pattern, $format, $tuple, $out, $fallback = false )
	{
		// i.e. getTemplate( "PasswordReset", "x.y.z", "txt", [...], $out );
		//
		// Will check for:
		//
		//	1)	<Template Dir>/PasswordReset.x.y.z.txt
		//	1)	<Template Dir>/PasswordReset.x.y.txt
		//	1)	<Template Dir>/PasswordReset.x.txt
		//	1)	<Template Dir>/PasswordReset.txt
		//
		//	Where <Template Dir> is either:
		//
		//	if ( array_key_exists( "vendor", $tuple ) )
		//	{
		//		<Base>/share/template/<APP_NAME>/vendors/<vendor>/
		//	}
		//	else
		//	{
		//		<Base>/share/template/<APP_NAME>/
		//	}
	
		$content   = "";
		$vendor    = array_get( $tuple, "vendor"    );
		$type      = array_get( $tuple, "type"      );

		$filename  = self::FindTemplate( $vendor, $msgtype, $pattern, $format, $fallback );

		if ( file_exists( $filename ) )
		{
			$content      = file_get_contents( $filename );
			$content      = string_replace( $content, $tuple, ("txt" == $format) );
			$resource_dir = dirname( $filename );

			if ( array_key_exists( "logo", $tuple ) )
			{
				$logo_name  = array_get( $tuple, "logo" );
				$logo_path  = $resource_dir . "/" . $logo_name;
				
				if ( file_exists( $logo_path ) )
				{
					$image64 = "data:image/png;base64," . base64_encode( file_get_contents( $logo_path ) );
					$content = str_replace( $logo_name, $image64, $content );
				}
				else
				{
					$out->println( "Could not find: " . $logo_path );
				}
			}

			if ( file_exists( $resource_dir . "/style.css" ) )
			{
				$style   = file_get_contents( $resource_dir . "/style.css" );
				$content = str_replace( "<link rel='stylesheet' href='style.css'>", "<style>\n$style\n</style>\n", $content );
			}

			if ( $content )
			{
				$out->println( "Using: $filename" );
			}
			else
			{
				$out->println( "Using empty file: $filename" );
			}
		}
		else
		{
			$out->println( "Could not find: " . $filename );
		}
		
		return $content;
	}

	static function FindTemplate( $vendor, $msgtype, $pattern, $format, $fallback )
	{
		$default    = strToLower( APP_NAME );
		$vendor     = strToLower( $vendor );
		$vendorpath = $vendor ? "$default/vendors/$vendor" : $default;
		$filepath   = "";
		
		$suf = $pattern . ".unused";

		do
		{
			if ( ($suf = self::TruncateSuffix( $suf )) )
			{
				$filepath = BASE . "/share/templates/$vendorpath/$msgtype.$suf.$format";
			}
			else
			{
				$filepath = BASE . "/share/templates/$vendorpath/$msgtype.$format";
			}
			echo "Trying: $filepath" . "\n";
		}
		while ( $suf && !file_exists( $filepath ) );

		if ( $vendor && $fallback && !file_exists( $filepath ) )
		{
			$suf = $pattern . ".unused";

			do
			{
				$suf = self::TruncateSuffix( $suf );
				
				if ( ($suf = self::TruncateSuffix( $suf )) )
				{
					$filepath = BASE . "/share/templates/$default/$msgtype.$suf.$format";
				}
				else
				{
					$filepath = BASE . "/share/templates/$default/$msgtype.$format";
				}

				echo "Trying: $filepath" . "\n";
			}
			while ( $suf && !file_exists( $filepath ) );
		}

		if ( ! file_exists( $filepath ) )
		{
			echo "Could not find: $filepath\n";
		}
		else
		{
			echo "Found:  $filepath\n";
		}
		
		return $filepath;
	}

	static function TruncateSuffix( $pattern )
	{
		$ret  = null;
		$bits = explode( ".", $pattern );

		if ( count( $bits ) > 1 )
		{
			$bits = array_slice( $bits, 0, -1 );
			$ret = implode( ".", $bits );
		}
		else
		if ( count( $bits ) == 1 )
		{
			$ret = "";
		}
		return $ret;
	}
}
