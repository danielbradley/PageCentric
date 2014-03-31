<?php
//	Copyright (c) 2014 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

if ( ! defined( "SCANDIR_SORT_DESCENDING" ) )
{
	define( "SCANDIR_SORT_DESCENDING", 1 );
}

class Files
{
	static function recurseFiles( $directory, $file_extension )
	{
		$files = array();
	
		if ( is_dir( $directory ) )
		{
			//echo "<!-- Found: $directory -->";
			Files::recurseFilesPrivate( $directory, $file_extension, $files );
		}
		return $files;
	}
	
	static function recurseFilesPrivate( $directory, $file_extension, &$files )
	{
		$tmp = scandir( $directory, SCANDIR_SORT_DESCENDING );
		
		foreach ( $tmp as $f )
		{
			if ( ! string_startsWith( $f, "." ) )
			{
				$filepath = "$directory/$f";
				if ( is_dir( $filepath ) )
				{
					Files::recurseFilesPrivate( $filepath, $file_extension, $files );
				}
				else
				if ( string_endsWith( $f, $file_extension ) )
				{
					$files[] = $filepath;
				}
			}
		}
	}
}
