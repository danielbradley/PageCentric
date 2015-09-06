<?php
//	Copyright (c) 2015 Daniel Robert Bradley. All rights reserved.
//	This software is distributed under the terms of the GNU Lesser General Public License version 2.1
?>
<?php

class PersonName
{
	public $title       = "";
	public $given_name  = "";
	public $family_name = "";

	function __construct( $name )
	{
		$bit  = 0;
		$bits = explode( " ", $name );
		$n    = count( $bits );
		
		if ( $bit < $n )
		{
			switch ( $bits[$bit] )
			{
			case "Cr":
			case "Dr":
			case "Master":
			case "Miss":
			case "Mr":
			case "Mrs":
			case "Ms":
				$this->title = $bits[$bit];
				$bit++;
				break;
			}
		}

		if ( $bit < $n )
		{
			$gname = "";
			for ( $i=$bit; $i < ($n - 1); $i++ )
			{
				$gname .= (" " . $bits[$i]);
			}
			
			$this->given_name  = trim( $gname );
			
			$this->family_name = $bits[$n - 1];
		}

		//echo $self->given_name . " " . $self->family_name;
	}
}