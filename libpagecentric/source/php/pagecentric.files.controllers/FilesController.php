<?php
//	Copyright (c) 2012 Daniel Robert Bradley. All rights reserved.
?>
<?php

class FilesController extends Controller
{
	static function insertFile( $sid, $USER, $kind, $filename, $file_array, $debug )
	{
		$FILE = 0;
		{
			$original_filename = $file_array['name'];
			$tmpname           = $file_array['tmp_name'];
			$filesize          = $file_array['size'];
			$filetype          = $file_array['type'];
			$base64            = "";

			$fp = fopen( $tmpname, 'r' );
			{
				$filecontent = fread( $fp, filesize($tmpname));
				$base64      = base64_encode( $filecontent );
			}
			fclose( $fp ); 

			$pathinfo = pathinfo( $original_filename );
			$fileextension = $pathinfo['extension'];
					
			if ( !get_magic_quotes_gpc() )
			{
				$filename          = addslashes( $filename );
				$original_filename = addslashes( $original_filename );
			}
			
			$sql  = "Files_Replace( '$sid', '0', '$USER', '$kind', '$original_filename', '$filename', '$filetype', '$filesize', '$fileextension', '<base64 encoded data>' )";
			$debug->println( "<!-- $sql -->" );

			$sql  = "Files_Replace( '$sid', '0', '$USER', '$kind', '$original_filename', '$filename', '$filetype', '$filesize', '$fileextension', '$base64' )";
			$FILE = array_get( first( DBi_callProcedure( DB, $sql, new NullPrinter() ) ), "FILE" );
		}
		return $FILE;
	}
	
	static function retrieve( $sid, $token, $debug )
	{
		$sql = "Files_Retrieve_By_Token( '$sid', '$token' )";
		return first( DBi_callProcedure( DB, $sql, $debug ) );
	}
}

?>