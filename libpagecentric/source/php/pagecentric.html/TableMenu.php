<?php
//	Copyright (c) 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

//include_once( $SOURCE . "/pagecentric.html/Menu.php" );

class TableMenu extends Menu
{
	function render( $out )
	{
		$out->inprint( "<table style='width:100%;table-layout:fixed;text-align:center;' id='$this->id' class='Menu $this->horizontal $this->vertical $this->left $this->right'>" );
		{
			$out->inprint( "<tr>" );
			{
				$col = 1;
			
				foreach ( $this->items as $id => $page )
				{
					$request_uri = REQUEST_URI;
					$bits = explode( '?', $page );
					$link = $bits[0];

					if ( "" == $link )
					{
						$out->println( "<td class='col$col'><span>$id</span></td>" );
					}
					else
					{
						$out->println( "<td class='col$col'><a data-toggle='modal' href='$page'>$id</a></td>" );
					}
					
					$col++;
				}
			}
			$out->outprint( "</tr>" );
		}
		$out->outprint( "</table>" );
	}
}

?>