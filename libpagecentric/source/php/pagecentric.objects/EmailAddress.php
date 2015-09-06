<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class EmailAddress
{
	function __construct( $email_address )
	{
		$this->email = $email_address;
		$this->error = "";
	}
	
	/*
	 * Based on the following article:
	 * http://articles.sitepoint.com/article/users-email-address-php
	 */
	function isValid( $debug )
	{
		$ret = False;
	
		if ( $this->email )
		{
			$list = explode( '@', $this->email );
			
			if ( "2" == count( $list ) )
			{
				if ( "" != $list[0] )
				{
					if ( array_key_exists( 1, $list ) )
					{
						$parts = explode( ".", $list[1] );
						$n     = count( $parts );
						
						if ( $n > 1 )
						{
							//if ( checkdnsrr( $list[1], "MX" ) )
							{
								$ret = True;
							}
							//else
							//{
							//	$this->error = "DNS lookup failed!";
							//}
						}
					}
					else
					{
						$this->error = "No domain specified in email address!";
					}
				}
				else
				{
					$this->error = "No account specified in email address!";
				}
			}
			else
			{
				$this->error = "Malformed email address!";
			}
		}
		
		return $ret;
	}
}

?>