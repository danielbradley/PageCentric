<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class HTMLUtils
{
	static function extractAttribute( $attributes, $attributeName )
	{
		$value = "";
	
		$pos = strpos( $attributes, $attributeName );
		if ( $pos !== False )
		{
			$pos = strpos( $attributes, "'", $pos );
			{
				if ( $pos != False )
				{
					$tmp = substr( $attributes, $pos + 1 );
					$pos = strpos( $tmp, "'" );
					if ( $pos !== False )
					{
						$value = substr( $tmp, 0, $pos );
					}
				}
			}
		}
		return $value;
	}
	
	static function decideIEPlaceholder( $value, $placeholder )
	{
		$pl = null;

		$browser = $_SERVER['HTTP_USER_AGENT'];
		if ( False !== strpos( $browser, "MSIE 9" ) )
		{
			$pl = $value ? "" : $placeholder;
		}
		return $pl;
	}
}

?>