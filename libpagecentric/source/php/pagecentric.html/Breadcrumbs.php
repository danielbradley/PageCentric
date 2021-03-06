<?php
//	Copyright (c) 2009, 2010 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class Breadcrumbs
{
	public $labels;
	public $arrow = "&gt;";

	function __construct( $labels )
	{
		$page = "";//PAGE;
	
		$this->labels = $labels;
		$this->pages  = array();
		$this->urls   = array();
		
		foreach( $this->labels as $label )
		{
			$this->pages[] = str_replace( " ", "_", strtolower( $label ) );
		}

		$max = count( $this->pages );
		for ( $i=0; $i < $max; $i++ )
		{
			if ( $i < ($max - 1) )
			{
				$url = $page . "/";
				for ( $j=0; $j <= $i; $j++ )
				{
					if ( 0 < $j )
					{
						$url .= $this->pages[$j] . "/";
					} else if ( "dashboard" == $this->pages[$j] ) {
						$url .= $this->pages[$j] . "/";
					} else {
						//$url .= $this->pages[$j] . "/";
					}
				}
				$this->urls[$i] = $url;
			}
		}
	}
	
	function setLabel( $index, $label )
	{
		$i   = $index - 1;
		$max = count( $this->labels );

		$this->labels[$max+$i] = $label;
	}

	function setLastLabel( $label )
	{
		$this->setLabel( 0, $label );
	}
	
	function setURL( $index, $url )
	{
		$i   = $index - 1;
		$max = count( $this->labels );

		$x = $max+$i;

		if ( array_key_exists( $x, $this->urls ) )
			$this->urls[$x] = $url;
	}

	function getURL( $index )
	{
		$i   = $index - 1;
		$max = count( $this->labels );

		$x   = $max+$i;

		$ret = PAGE;
		if ( array_key_exists( $x, $this->urls ) )
			$ret = $this->urls[$x];
		return $ret;
	}
	
	function getLastLabel()
	{
		$max = count( $this->labels );
		return $this->labels[$max-1];
	}

	function getLastURL()
	{
		$max = count( $this->labels );
		return $this->urls[$max-1];
	}

	function getPreviousURL()
	{
		$max = count( $this->labels );
		return $this->urls[$max-2];
	}
	
	function getCount()
	{
		return count( $this->labels );
	}

	function setArrow( $text )
	{
		$this->arrow = $text;
	}
	
	function getArrow()
	{
		return $this->arrow;
	}
	
	function truncate( $nr )
	{
		$this->labels = array_slice( $this->labels, $nr );
		$this->pages  = array_slice( $this->pages,  $nr );
		$this->urls   = array_slice( $this->urls,   $nr );
	}
	
	function render( $out )
	{
		$arrow = $this->getArrow();
		
		$out->inprint( "<ol>" );
		{
			$max = count( $this->pages );
			
			for ( $i=0; $i < $max; $i++ )
			{
				$label = $this->labels[$i];

				if ( $i < ($max - 1) )
				{
					$url = $this->urls[$i];
					
					if ( $label )
					{
						$out->println( "<li><a href='$url'>" . $label . "</a></li>" );
						$out->println( "<li class='arrow'>$arrow</li>" );
					}
				} else {
					$out->println( "<li><span>" . $label . "</span></li>" );
				}
			}
		}
		$out->outprint( "</ol>" );
	}
}

?>