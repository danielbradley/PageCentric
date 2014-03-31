<?php
//	Copyright (c) 2012 Daniel Robert Bradley. All rights reserved.
?>
<?php

class DownloadPage extends Page
{
	function __construct()
	{
		parent::__construct();
	
		$sid   = $this->getRequest( "sid" );
		$token = $this->getRequest( "token" );

		if ( $token )
		{
			$file = FilesController::retrieve( $sid, $token, $this->debug );
			if ( $file )
			{
				$name    = array_get( $file, "filename" );
				$type    = array_get( $file, "filetype" );
				$size    = array_get( $file, "filesize" );
				$base64  = array_get( $file, "base64" );
				$content = base64_decode( $base64 );

				header( "Content-Type: $type" );
				header( "Content-Length: $size" );
				header( "Content-Disposition: attachment, filename=\"$name\"" );

				echo $content;
				exit;
			}
			else
			{
				header( "Content-Type: text/plain" );
				echo "File not found";
			}
		}
		else
		{
			header( "Content-Type: text/plain" );
			echo "No file specified";
		}
	}
}

?>