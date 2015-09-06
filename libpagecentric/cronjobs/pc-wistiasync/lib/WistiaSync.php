<?php

class WistiaSync
{
	protected $apiKey   = "";
	protected $response = "";
	
	public function __construct()
	{
		$this->apiKey = defined( "WISTIA_API_KEY" ) ? WISTIA_API_KEY : "";
	}

	public function perform( $out, $debug )
	{
		if ( $this->apiKey )
		{
			$URL = "https://api.wistia.com/v1/medias.json?api_password=" . $this->apiKey;
			$out->println( "Contacting: " . $URL );
	
			$json   = $this->send( $URL );

			$object = json_decode( $json );

			$this->PrintX( $object, $out );
		}
	}

	static function PrintX( $object, $out )
	{
		if ( is_array( $object ) )
		{
			foreach ( $object as $o )
			{
				self::PrintX( $o, $out );
			}
		}
		else
		{
			self::PrintObject( $object, $out );
		}
	}
	
	static function PrintObject( $object, $out )
	{
		$hashed_id     = $object->hashed_id;
		$duration      = $object->duration;
		$updated       = $object->updated;
		$image         = self::ExtractImage( $hashed_id, $object->assets );

		$out->printf( "Processing: $hashed_id: " );

		if ( ! DBi_callFunction( DB, "Articles_Info_Contains( '$hashed_id', '$updated' )", new NullPrinter() ) )
		{
			$out->println( "ADDING" );
		
			$filename      = $image["filename"];
			$filetype      = $image["filetype"];
			$filesize      = $image["filesize"];
			$fileextension = $image["fileextension"];
			$base64        = $image["base64"];

			$sql = "Articles_Info_Replace( '$hashed_id', '$updated', '$duration', '$filename', '$filetype', '$filesize', '$fileextension', '$base64' )";

			DBi_callProcedure( DB, $sql, new NullPrinter() );
		}
		else
		{
			$out->println( "SKIPPING" );
		}
	}

	static function ExtractImage( $hashed_id, $assets )
	{
		$ret = array();
	
		foreach ( $assets as $asset )
		{
			if ( "image/jpeg" == $asset->contentType )
			{
				$extension = self::ExtractExtension( $asset->contentType );

				$ret["url"]           = $asset->url;
				$ret["width"]         = $asset->width;
				$ret["height"]        = $asset->height;
				$ret["filename"]      = $hashed_id . "." . $extension;
				$ret["filetype"]      = $asset->contentType;
				$ret["filesize"]      = $asset->fileSize;
				$ret["fileextension"] = $extension;
				$ret["base64"]        = self::RetrieveURLAsBase64( $asset->url );
				break;
			}
		}
		
		return $ret;
	}

	static function RetrieveURLAsBase64( $url )
	{
		$encoded = "";
		$data    = self::Send( $url );
		$temp    = tmpfile();

		fwrite( $temp, $data );
		fseek ( $temp, 0     );

		while ( !feof( $temp ) )
		{
			$plain    = fread( $temp, 57 * 143 );
			$encoded .= base64_encode( $plain );
		}

		fclose( $temp );

		return $encoded;
	}
	
	static function ExtractExtension( $contentType )
	{
		//	For "image/jpeg"
		//	First call returns "image", second call returns "jpeg"
	
		strtok( $contentType, '/' );
		return        strtok( '/' );
	}

	static function Send($url)
	{
		$username = 'api';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
}